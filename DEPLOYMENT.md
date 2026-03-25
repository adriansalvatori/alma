# Deployment Guide

## Deployment Options

### Option 1: ZIP Deployment

```bash
npm run deploy:zip
# Upload alma-deploy.zip to server
# Extract and configure
```

### Option 2: Git Deployment

```bash
git push production main
# SSH into server
# Run composer install && npm run build
```

### Option 3: CI/CD Pipeline

Use GitHub Actions or similar.

## Pre-Deployment Checklist

### Code Quality
- [ ] All tests passing: `npm run test`
- [ ] Code linted: `npm run lint`
- [ ] No console errors
- [ ] Performance tested

### Security
- [ ] Environment variables set
- [ ] Debug mode disabled
- [ ] File permissions correct
- [ ] SSL certificate installed

### Database
- [ ] Backup created
- [ ] Migrations run (if any)
- [ ] Search-replace URLs done

### Assets
- [ ] Production build created
- [ ] Assets uploaded to CDN (if used)
- [ ] Cache cleared

## Server Requirements

### PHP
- Version: 8.2+
- Extensions: mbstring, curl, gd, mysql, intl
- Memory: 256MB minimum

### Database
- MySQL 8.0+ or MariaDB 10.4+
- Proper user permissions

### Web Server
- Nginx or Apache
- SSL/TLS enabled
- Gzip compression

### Node
- Version: 20+
- npm or yarn

## Environment Configuration

### Production .env
```env
WP_ENV=production
WP_HOME=https://example.com
WP_SITEURL=${WP_HOME}/wp
DB_NAME=production_db
DB_USER=production_user
DB_PASSWORD=secure_password
DB_HOST=localhost
DISABLE_WP_CRON=true
WP_DEBUG=false
SCRIPT_DEBUG=false
CONCATENATE_SCRIPTS=true
```

### Security Keys
Generate at: https://roots.io/salts.html

## Deployment Steps

### 1. Upload Code
```bash
rsync -avz --exclude='node_modules' --exclude='vendor' \
  ./ user@server:/var/www/html/
```

### 2. Install Dependencies
```bash
cd /var/www/html
composer install --no-dev --optimize-autoloader
npm install --prefix web/app/themes/alma
npm run build --prefix web/app/themes/alma
```

### 3. Set Permissions
```bash
chown -R www-data:www-data /var/www/html
find /var/www/html -type d -exec chmod 755 {} \;
find /var/www/html -type f -exec chmod 644 {} \;
chmod -R 775 /var/www/html/web/app/uploads
```

### 4. Update Database
```bash
wp db export backup.sql
wp search-replace 'http://local.test' 'https://example.com' --skip-columns=guid
wp cache flush
```

### 5. Optimize
```bash
wp acorn optimize
wp cache flush
```

## Rollback Strategy

### Quick Rollback
```bash
# Keep previous version
cp -r /var/www/html /var/www/html.backup

# If issues, restore:
rm -rf /var/www/html
mv /var/www/html.backup /var/www/html
```

### Database Rollback
```bash
wp db import backup.sql
```

## Post-Deployment Verification

### Functional Tests
- [ ] Homepage loads
- [ ] Login works
- [ ] Forms submit
- [ ] E-commerce checkout
- [ ] Admin access

### Performance Tests
- [ ] Page load < 3s
- [ ] Time to First Byte < 600ms
- [ ] No N+1 queries

### Monitoring
- [ ] Error logging enabled
- [ ] Uptime monitoring active
- [ ] Analytics tracking

## CDN Configuration

If using CDN (Cloudflare, etc.):

### Cache Rules
- Cache static assets: 1 year
- Cache HTML: 1 hour
- Bypass cache for logged-in users

### Cache Invalidation
```bash
# Cloudflare CLI
curl -X POST "https://api.cloudflare.com/client/v4/zones/ZONE_ID/purge_cache" \
  -H "Authorization: Bearer TOKEN" \
  -H "Content-Type: application/json" \
  --data '{"purge_everything":true}'
```

## Automated Deployment (GitHub Actions)

```yaml
name: Deploy to Production

on:
  push:
    branches: [main]

jobs:
  deploy:
    runs-on: ubuntu-latest
    
    steps:
      - uses: actions/checkout@v3
      
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.2'
      
      - name: Setup Node
        uses: actions/setup-node@v3
        with:
          node-version: '20'
      
      - name: Install dependencies
        run: |
          composer install --no-dev --optimize-autoloader
          npm install --prefix web/app/themes/alma
      
      - name: Build assets
        run: npm run build --prefix web/app/themes/alma
      
      - name: Deploy via SSH
        uses: easingthemes/ssh-deploy@v3
        with:
          SSH_PRIVATE_KEY: ${{ secrets.SSH_KEY }}
          REMOTE_HOST: ${{ secrets.HOST }}
          REMOTE_USER: ${{ secrets.USER }}
          SOURCE: "./"
          TARGET: "/var/www/html"
          EXCLUDE: "node_modules, vendor, .git"
      
      - name: Run post-deploy
        run: |
          ssh ${{ secrets.USER }}@${{ secrets.HOST }} "
            cd /var/www/html &&
            composer install --no-dev &&
            wp cache flush &&
            wp acorn optimize
          "
```

## Monitoring & Alerts

### Error Tracking
- Sentry or Bugsnag
- Log aggregation (Papertrail, Loggly)

### Performance Monitoring
- New Relic or DataDog
- Google PageSpeed

### Uptime Monitoring
- UptimeRobot or Pingdom

## Maintenance Mode

During deployment:
```bash
# Enable maintenance
wp maintenance-mode activate

# Deploy...

# Disable maintenance
wp maintenance-mode deactivate
```

Or use plugin like WP Maintenance Mode.

## Scaling Considerations

### Horizontal Scaling
- Use object cache (Redis)
- Database replication
- Load balancer
- CDN for assets

### Vertical Scaling
- Increase PHP memory
- Optimize database
- Use OPcache

### Caching Strategy
1. Object cache: Redis
2. Page cache: Varnish/Cloudflare
3. Browser cache: Long expiry
4. Fragment cache: Transients

## Troubleshooting

### Common Issues

**500 Error**
- Check error logs
- Verify file permissions
- Test PHP syntax

**Database Connection Failed**
- Check credentials
- Verify MySQL running
- Test connection manually

**Assets Not Loading**
- Rebuild assets
- Check manifest.json
- Clear browser cache

**White Screen**
- Enable WP_DEBUG
- Check memory limit
- Disable plugins one by one
