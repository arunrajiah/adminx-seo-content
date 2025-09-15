# AdminX SEO & Content - Deployment Guide

## Table of Contents
1. [Development Environment Setup](#development-environment-setup)
2. [Local Development](#local-development)
3. [Testing Procedures](#testing-procedures)
4. [Build Process](#build-process)
5. [Packaging for Distribution](#packaging-for-distribution)
6. [WordPress.org Submission](#wordpressorg-submission)
7. [Production Deployment](#production-deployment)
8. [Version Management](#version-management)
9. [Continuous Integration](#continuous-integration)
10. [Monitoring & Maintenance](#monitoring--maintenance)

## Development Environment Setup

### Prerequisites
- **PHP**: 7.4 or higher (8.1+ recommended)
- **WordPress**: 5.0 or higher (latest version recommended)
- **MySQL**: 5.6 or higher (8.0+ recommended)
- **Node.js**: 16+ (for build tools)
- **Composer**: Latest version
- **Git**: Latest version

### Local Development Stack Options

#### Option 1: Local by Flywheel
```bash
# Download and install Local by Flywheel
# Create new WordPress site
# Set PHP version to 8.1
# Enable SSL and configure domain
```

#### Option 2: Docker Setup
```bash
# Clone the development repository
git clone https://github.com/arunrajiah/adminx-plugins.git
cd adminx-plugins

# Start Docker containers
docker-compose up -d

# Access WordPress at http://localhost:8080
```

#### Option 3: XAMPP/MAMP
```bash
# Install XAMPP or MAMP
# Create new WordPress installation
# Configure virtual host for development
# Set PHP version and memory limits
```

### Development Tools Setup

#### Code Editor Configuration
```json
// VS Code settings.json
{
    "php.validate.executablePath": "/usr/bin/php",
    "php.suggest.basic": false,
    "phpcs.enable": true,
    "phpcs.standard": "WordPress",
    "emmet.includeLanguages": {
        "php": "html"
    }
}
```

#### Composer Dependencies
```bash
# Install development dependencies
composer install --dev

# Install WordPress Coding Standards
composer global require wp-coding-standards/wpcs
phpcs --config-set installed_paths ~/.composer/vendor/wp-coding-standards/wpcs
```

## Local Development

### Project Structure
```
adminx-seo-content/
├── adminx-seo-content.php     # Main plugin file
├── includes/                  # Core PHP classes
│   ├── class-seo-health-checker.php
│   ├── class-internal-link-manager.php
│   ├── class-content-scheduler.php
│   └── class-auto-tagger.php
├── assets/                    # CSS/JS files
│   ├── admin.css
│   └── admin.js
├── templates/                 # Admin templates
├── docs/                      # Documentation
├── .github/workflows/         # CI/CD configuration
├── readme.txt                 # WordPress.org readme
├── composer.json              # PHP dependencies
└── package.json               # Node.js dependencies
```

### Development Workflow

#### 1. Feature Development
```bash
# Create feature branch
git checkout -b feature/new-seo-feature

# Make changes and test locally
# Follow WordPress coding standards
# Add unit tests for new functionality

# Commit changes
git add .
git commit -m "Add new SEO feature"

# Push to remote
git push origin feature/new-seo-feature
```

#### 2. Code Quality Checks
```bash
# Run PHP syntax check
find . -name "*.php" -print0 | xargs -0 -n1 php -l

# Run WordPress Coding Standards
phpcs --standard=WordPress ./adminx-seo-content

# Fix coding standards issues
phpcbf --standard=WordPress ./adminx-seo-content

# Run security scan
composer require --dev phpstan/phpstan
vendor/bin/phpstan analyse
```

#### 3. Database Development
```sql
-- Create development database tables
CREATE TABLE wp_adminx_seo_health (
    id mediumint(9) NOT NULL AUTO_INCREMENT,
    post_id bigint(20) NOT NULL,
    check_type varchar(50) NOT NULL,
    status varchar(20) NOT NULL,
    message text,
    checked_at datetime DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY post_id (post_id),
    KEY check_type (check_type)
);

CREATE TABLE wp_adminx_content_scheduler (
    id mediumint(9) NOT NULL AUTO_INCREMENT,
    post_id bigint(20) NOT NULL,
    action_type varchar(20) NOT NULL,
    scheduled_date datetime NOT NULL,
    status varchar(20) DEFAULT 'pending',
    created_at datetime DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY post_id (post_id),
    KEY scheduled_date (scheduled_date)
);
```

## Testing Procedures

### Unit Testing Setup
```bash
# Install PHPUnit
composer require --dev phpunit/phpunit

# Install WordPress test suite
bash bin/install-wp-tests.sh wordpress_test root '' localhost latest

# Run tests
vendor/bin/phpunit
```

### Test Categories

#### 1. Functionality Tests
```php
// Example test for SEO Health Checker
class SEOHealthCheckerTest extends WP_UnitTestCase {
    public function test_meta_title_check() {
        $post_id = $this->factory->post->create([
            'post_title' => 'Test Post Title'
        ]);
        
        $checker = new AdminX_SEO_Health_Checker();
        $result = $checker->check_single_post($post_id);
        
        $this->assertArrayHasKey('meta_title', $result);
        $this->assertEquals('good', $result['meta_title']['status']);
    }
}
```

#### 2. Integration Tests
- WordPress compatibility testing
- Plugin conflict testing
- Theme compatibility testing
- Database integrity testing

#### 3. Performance Tests
```bash
# Load testing with WP-CLI
wp eval "
for (\$i = 0; \$i < 1000; \$i++) {
    wp_insert_post([
        'post_title' => 'Test Post ' . \$i,
        'post_content' => str_repeat('Lorem ipsum ', 100),
        'post_status' => 'publish'
    ]);
}
"

# Test bulk operations performance
wp eval "
\$checker = new AdminX_SEO_Health_Checker();
\$start = microtime(true);
\$checker->run_scheduled_check();
\$end = microtime(true);
echo 'Execution time: ' . (\$end - \$start) . ' seconds';
"
```

#### 4. Security Tests
```bash
# Install security scanner
composer require --dev vimeo/psalm

# Run security analysis
vendor/bin/psalm --show-info=true

# Check for SQL injection vulnerabilities
grep -r "wpdb->query" --include="*.php" .
grep -r "\$wpdb->prepare" --include="*.php" .
```

### Browser Testing
- Chrome (latest)
- Firefox (latest)
- Safari (latest)
- Edge (latest)
- Mobile browsers (iOS Safari, Chrome Mobile)

## Build Process

### Asset Compilation
```bash
# Install Node.js dependencies
npm install

# Build production assets
npm run build

# Watch for development changes
npm run watch
```

### Package.json Configuration
```json
{
  "name": "adminx-seo-content",
  "version": "1.0.0",
  "scripts": {
    "build": "webpack --mode=production",
    "dev": "webpack --mode=development",
    "watch": "webpack --mode=development --watch"
  },
  "devDependencies": {
    "webpack": "^5.0.0",
    "css-loader": "^6.0.0",
    "mini-css-extract-plugin": "^2.0.0"
  }
}
```

### Version Bumping
```bash
# Update version in main plugin file
sed -i 's/Version: .*/Version: 1.0.1/' adminx-seo-content.php

# Update version constant
sed -i "s/define('ADMINX_SEO_CONTENT_VERSION', '.*');/define('ADMINX_SEO_CONTENT_VERSION', '1.0.1');/" adminx-seo-content.php

# Update readme.txt stable tag
sed -i 's/Stable tag: .*/Stable tag: 1.0.1/' readme.txt
```

## Packaging for Distribution

### Create Distribution Package
```bash
#!/bin/bash
# build-release.sh

VERSION="1.0.0"
PLUGIN_NAME="adminx-seo-content"

# Create build directory
mkdir -p build

# Copy plugin files
cp -r $PLUGIN_NAME build/

# Remove development files
rm -rf build/$PLUGIN_NAME/.git
rm -rf build/$PLUGIN_NAME/node_modules
rm -rf build/$PLUGIN_NAME/tests
rm -f build/$PLUGIN_NAME/.gitignore
rm -f build/$PLUGIN_NAME/composer.json
rm -f build/$PLUGIN_NAME/package.json
rm -f build/$PLUGIN_NAME/webpack.config.js

# Create zip file
cd build
zip -r $PLUGIN_NAME-$VERSION.zip $PLUGIN_NAME/
cd ..

echo "Release package created: build/$PLUGIN_NAME-$VERSION.zip"
```

### File Exclusions
```gitignore
# .distignore file for WordPress.org SVN
/.git
/.github
/node_modules
/tests
/build
.gitignore
.distignore
composer.json
package.json
webpack.config.js
phpunit.xml
.phpcs.xml
```

## WordPress.org Submission

### SVN Repository Setup
```bash
# Checkout SVN repository
svn checkout https://plugins.svn.wordpress.org/adminx-seo-content

# Directory structure
adminx-seo-content/
├── trunk/          # Development version
├── tags/           # Released versions
│   ├── 1.0.0/
│   └── 1.0.1/
└── assets/         # Plugin directory assets
    ├── banner-772x250.png
    ├── banner-1544x500.png
    ├── icon-128x128.png
    └── icon-256x256.png
```

### Submission Process
```bash
# Copy files to trunk
cp -r adminx-seo-content/* trunk/

# Add new files
svn add trunk/* --force

# Create release tag
svn copy trunk tags/1.0.0

# Commit changes
svn commit -m "Initial release v1.0.0"
```

### WordPress.org Assets
- **Banner**: 1544x500px and 772x250px
- **Icon**: 256x256px and 128x128px
- **Screenshots**: 1200x900px recommended
- **Plugin header image**: Optional 1200x630px

## Production Deployment

### Pre-Deployment Checklist
- [ ] All tests passing
- [ ] Code review completed
- [ ] Security audit passed
- [ ] Performance benchmarks met
- [ ] Documentation updated
- [ ] Version numbers updated
- [ ] Changelog updated

### Deployment Methods

#### Method 1: WordPress Admin Upload
1. Create plugin zip file
2. Upload via WordPress admin
3. Activate and test functionality
4. Monitor for errors

#### Method 2: FTP/SFTP Deployment
```bash
# Upload via SFTP
sftp user@yoursite.com
put -r adminx-seo-content /wp-content/plugins/
```

#### Method 3: Git Deployment
```bash
# Production server deployment
git clone https://github.com/arunrajiah/adminx-plugins.git
cd adminx-plugins
git checkout tags/v1.0.0
```

### Database Migration
```php
// Handle database updates
function adminx_seo_content_update_db_check() {
    $current_version = get_option('adminx_seo_content_version');
    
    if ($current_version !== ADMINX_SEO_CONTENT_VERSION) {
        adminx_seo_content_update_database();
        update_option('adminx_seo_content_version', ADMINX_SEO_CONTENT_VERSION);
    }
}
add_action('plugins_loaded', 'adminx_seo_content_update_db_check');
```

## Version Management

### Semantic Versioning
- **Major (X.0.0)**: Breaking changes
- **Minor (1.X.0)**: New features, backward compatible
- **Patch (1.0.X)**: Bug fixes, backward compatible

### Release Process
```bash
# Create release branch
git checkout -b release/1.0.1

# Update version numbers
# Update changelog
# Final testing

# Merge to main
git checkout main
git merge release/1.0.1

# Create tag
git tag -a v1.0.1 -m "Release version 1.0.1"
git push origin v1.0.1
```

### Changelog Management
```markdown
# Changelog

## [1.0.1] - 2025-09-20
### Fixed
- Fixed SEO health check timeout issue
- Improved internal link suggestion accuracy

### Changed
- Updated admin interface styling
- Optimized database queries

## [1.0.0] - 2025-09-15
### Added
- Initial release
- SEO Health Checker
- Internal Link Manager
- Content Scheduler
- Auto Tagging System
```

## Continuous Integration

### GitHub Actions Workflow
```yaml
# .github/workflows/ci.yml
name: WordPress Plugin CI

on:
  push:
    branches: [ "main", "develop" ]
  pull_request:
    branches: [ "main" ]

jobs:
  test:
    runs-on: ubuntu-latest
    
    strategy:
      matrix:
        php-version: [7.4, 8.0, 8.1, 8.2]
        wordpress-version: [5.0, 5.5, 6.0, latest]
    
    steps:
    - uses: actions/checkout@v3
    
    - name: Setup PHP
      uses: shivammathur/setup-php@v2
      with:
        php-version: ${{ matrix.php-version }}
        
    - name: Install dependencies
      run: composer install --no-dev --optimize-autoloader
      
    - name: Validate PHP syntax
      run: find . -name "*.php" -print0 | xargs -0 -n1 php -l
      
    - name: WordPress Coding Standards
      run: vendor/bin/phpcs --standard=WordPress ./adminx-seo-content
      
    - name: Security scan
      run: vendor/bin/psalm --show-info=false
      
    - name: Run tests
      run: vendor/bin/phpunit
```

### Automated Deployment
```yaml
# .github/workflows/deploy.yml
name: Deploy to WordPress.org

on:
  release:
    types: [published]

jobs:
  deploy:
    runs-on: ubuntu-latest
    steps:
    - uses: actions/checkout@v3
    
    - name: WordPress Plugin Deploy
      uses: 10up/action-wordpress-plugin-deploy@stable
      env:
        SVN_PASSWORD: ${{ secrets.SVN_PASSWORD }}
        SVN_USERNAME: ${{ secrets.SVN_USERNAME }}
```

## Monitoring & Maintenance

### Performance Monitoring
```php
// Add performance monitoring
function adminx_log_performance($operation, $execution_time) {
    if ($execution_time > 5) { // Log slow operations
        error_log("AdminX SEO: Slow operation - $operation took {$execution_time}s");
    }
}
```

### Error Tracking
```php
// Error logging
function adminx_log_error($message, $context = []) {
    error_log('AdminX SEO Error: ' . $message . ' Context: ' . json_encode($context));
}
```

### Health Checks
```bash
# Server health check script
#!/bin/bash
# health-check.sh

# Check WordPress installation
wp core verify-checksums

# Check plugin status
wp plugin status adminx-seo-content

# Check database integrity
wp db check

# Check for errors
tail -n 100 /var/log/apache2/error.log | grep "AdminX"
```

### Maintenance Schedule
- **Daily**: Monitor error logs
- **Weekly**: Performance review
- **Monthly**: Security updates
- **Quarterly**: Feature updates
- **Annually**: Major version releases

### Support Channels
- **WordPress.org Forum**: Community support
- **GitHub Issues**: Bug reports and feature requests
- **Documentation**: Keep user guides updated
- **Email Support**: Premium support option

---

**Deployment Team:** AdminX Development  
**Last Updated:** September 15, 2025  
**Next Review:** October 15, 2025