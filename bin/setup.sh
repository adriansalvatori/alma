#!/bin/bash

echo "=========================================="
echo "    WP / Bedrock Installation Setup"
echo "=========================================="

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
echo "Updating .env file with database credentials..."
if [ "$(uname)" == "Darwin" ]; then
    sed -i '' "s/^DB_NAME=.*/DB_NAME='${dbname}'/" .env
    sed -i '' "s/^DB_USER=.*/DB_USER='${dbuser}'/" .env
    sed -i '' "s/^DB_PASSWORD=.*/DB_PASSWORD='${dbpass}'/" .env
else
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

# Get WP_HOME from .env for the WP installation URL
WP_HOME_URL=$(grep '^WP_HOME=' .env | cut -d '=' -f2 | tr -d "'\"")
if [ -z "$WP_HOME_URL" ]; then
    WP_HOME_URL="http://v7.alma.test"
fi

# Install WordPress
echo "Installing WordPress at ${WP_HOME_URL}..."
wp core install \
  --url="${WP_HOME_URL}" \
  --title="v7.alma" \
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
npm install --prefix web/app/themes/alma
npm run build --prefix web/app/themes/alma

# Run Acorn optimize
echo "Clearing Acorn caches..."
wp acorn optimize:clear || true

echo "=========================================="
echo "Setup Complete!"
echo "You can log in at ${WP_HOME_URL}/wp/wp-admin"
echo "User: admin"
echo "Password: password"
echo "=========================================="
