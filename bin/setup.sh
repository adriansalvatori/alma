#!/bin/bash

echo "=========================================="
echo "    WP / Bedrock Installation Setup"
echo "=========================================="

# Get default folder name
FOLDER_NAME=$(basename "$PWD")
DEFAULT_WP_HOME="http://${FOLDER_NAME}.test"

# Prompt for installation details
read -p "WP Home URL [${DEFAULT_WP_HOME}]: " user_wp_home
WP_HOME_URL=${user_wp_home:-$DEFAULT_WP_HOME}

# Prompt for database credentials
read -p "Database Name: " dbname
read -p "MySQL User: " dbuser
read -sp "MySQL Password: " dbpass
echo

# Copy .env.example to .env if it doesn't exist
if [ ! -f .env ]; then
  echo "Creating .env file from .env.example..."
  cp .env.example .env
fi

# Update .env
echo "Updating .env file with configuration..."
if [ "$(uname)" == "Darwin" ]; then
    sed -i '' "s|^WP_HOME=.*|WP_HOME='${WP_HOME_URL}'|" .env
    sed -i '' "s/^DB_NAME=.*/DB_NAME='${dbname}'/" .env
    sed -i '' "s/^DB_USER=.*/DB_USER='${dbuser}'/" .env
    sed -i '' "s/^DB_PASSWORD=.*/DB_PASSWORD='${dbpass}'/" .env
else
    sed -i "s|^WP_HOME=.*|WP_HOME='${WP_HOME_URL}'|" .env
    sed -i "s/^DB_NAME=.*/DB_NAME='${dbname}'/" .env
    sed -i "s/^DB_USER=.*/DB_USER='${dbuser}'/" .env
    sed -i "s/^DB_PASSWORD=.*/DB_PASSWORD='${dbpass}'/" .env
fi

# Check if vendor directory exists, run composer install if not
if [ ! -d "vendor" ]; then
    echo "Dependencies not found. Running composer install..."
    composer install
fi

# Run wp db create
echo "Creating the database..."
wp db create

# Extract and set WP_HOME from .env if needed
# (we already have it in WP_HOME_URL, but just in case we need to verify)

# Install WordPress
echo "Installing WordPress at ${WP_HOME_URL}..."
wp core install \
  --url="${WP_HOME_URL}" \
  --title="${FOLDER_NAME}" \
  --admin_user="admin" \
  --admin_password="password" \
  --admin_email="admin@example.com" \
  --skip-email

# Activate the Theme
echo "Activating 'alma' theme..."
wp theme activate alma

# Ask about plugins
echo "Fetching available plugins..."
PLUGINS=$(wp plugin list --status=inactive --field=name)

if [ -n "$PLUGINS" ]; then
    echo "=========================================="
    echo "Available plugins to activate:"
    echo "=========================================="
    for plugin in $PLUGINS; do
        read -p "Install/Activate '$plugin'? (y/N): " activate_plugin
        if [[ "$activate_plugin" =~ ^[Yy]$ ]]; then
            wp plugin activate "$plugin"
        fi
    done
fi

echo "=========================================="
echo "Additional Setup Tasks"
echo "=========================================="

# Generate and update salts
echo "Fetching fresh security salts..."
SALTS=$(curl -s https://api.wordpress.org/secret-key/1.1/salt/)
if [ -n "$SALTS" ]; then
    # We will use wp-cli to set salts to avoid complex sed commands with special characters
    wp config shuffle-salts
else
    echo "Failed to fetch salts, skipping..."
fi

# Set permalinks
echo "Setting permalink structure to /%postname%/"
wp rewrite structure '/%postname%/' --hard

# Delete default content
echo "Deleting default content..."
wp post delete 1 2 3 --force || true

# Build theme assets
echo "Installing NPM dependencies and building theme assets..."
(cd web/app/themes/alma && npm install && npm run build)

# Run Acorn optimize
echo "Clearing Acorn caches..."
wp acorn optimize:clear || true

echo "=========================================="
echo "Setup Complete!"
echo "You can log in at ${WP_HOME_URL}/wp/wp-admin"
echo "User: admin"
echo "Password: password"
echo "=========================================="
