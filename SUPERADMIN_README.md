# SuperAdmin Panel - Facturarg Multi-Tenant SaaS

## 📋 Table of Contents

- [Overview](#overview)
- [Features](#features)
- [Prerequisites](#prerequisites)
- [Installation](#installation)
- [Configuration](#configuration)
- [Usage Guide](#usage-guide)
- [Two-Factor Authentication (2FA)](#two-factor-authentication-2fa)
- [API Endpoints](#api-endpoints)
- [Security Considerations](#security-considerations)
- [Troubleshooting](#troubleshooting)
- [Maintenance](#maintenance)

## 🎯 Overview

The SuperAdmin panel is a comprehensive management interface for the Facturarg multi-tenant SaaS application. It provides complete control over tenants, training videos, client activities, and system security with enterprise-grade two-factor authentication.

## ✨ Features

### 🔐 Authentication & Security
- **Two-Factor Authentication (2FA)** with Google Authenticator support
- **Recovery Codes** for emergency access
- **Session Management** with proper security controls
- **Rate Limiting** on authentication attempts

### 👥 Tenant Management
- **Tenant CRUD Operations** (Create, Read, Update, Delete)
- **Status Management** (Active, Suspended, Pending)
- **Activity Monitoring** with detailed logs
- **Export Functionality** for activity reports
- **Database Management** (create/drop tenant databases)

### 📹 Training Video Management
- **Video Upload** with file validation
- **Thumbnail Management** for video previews
- **Module Organization** (Dashboard, Products, Sales, etc.)
- **Status Control** (Enable/Disable videos)
- **Sort Order Management** for video sequencing

### 📊 Dashboard & Analytics
- **Real-time Statistics** (tenants, videos, activities)
- **Recent Activity Feed** with detailed information
- **Quick Action Buttons** for common tasks
- **System Status Monitoring**

## 🛠️ Prerequisites

### System Requirements
- **PHP**: 8.0 or higher
- **Laravel**: 10.x
- **MySQL/MariaDB**: 5.7 or higher
- **Composer**: Latest version
- **Node.js**: 14+ (for asset compilation)

### Required Extensions
- PHP Extensions: `bcmath`, `ctype`, `fileinfo`, `json`, `mbstring`, `openssl`, `pdo`, `tokenizer`, `xml`
- GD Extension: For image processing
- File Upload: Configured for large video files

### Dependencies
```bash
# Core Laravel packages
laravel/framework: ^10.0
spatie/laravel-multitenancy: ^3.0

# Authentication & Security
laravel/fortify: ^1.27
pragmarx/google2fa: ^8.0

# Frontend & UI
bootstrap: ^5.1
datatables: ^1.11
sweetalert2: ^11.0
```

## 🚀 Installation

### 1. Clone and Setup
```bash
# Clone the repository
git clone <repository-url>
cd FACTURAG_V1_MULTITENANT

# Install PHP dependencies
composer install

# Install Node.js dependencies (if using frontend assets)
npm install
npm run dev
```

### 2. Environment Configuration
```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

### 3. Database Setup
```bash
# Configure database connections in .env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=landlord_db
DB_USERNAME=your_username
DB_PASSWORD=your_password

# Run migrations
php artisan migrate --database=landlord
```

### 4. SuperAdmin Setup
```bash
# Run the SuperAdmin setup command
php artisan superadmin:setup
```

### 5. Storage Configuration
```bash
# Create storage links
php artisan storage:link

# Set proper permissions
chmod -R 775 storage bootstrap/cache
```

## ⚙️ Configuration

### Database Configuration
```php
// config/database.php
'connections' => [
    'landlord' => [
        'driver' => 'mysql',
        'host' => env('DB_HOST', '127.0.0.1'),
        'port' => env('DB_PORT', '3306'),
        'database' => env('DB_DATABASE', 'landlord_db'),
        'username' => env('DB_USERNAME', 'forge'),
        'password' => env('DB_PASSWORD', ''),
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
    ],
    
    'tenant' => [
        'driver' => 'mysql',
        'host' => env('DB_HOST', '127.0.0.1'),
        'port' => env('DB_PORT', '3306'),
        'database' => null, // Will be set dynamically
        'username' => env('DB_USERNAME', 'forge'),
        'password' => env('DB_PASSWORD', ''),
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
    ],
]
```

### Fortify Configuration
```php
// config/fortify.php
'guard' => 'superadmin',
'passwords' => 'superadmins',
'home' => '/superadmin/dashboard',
'prefix' => '',
'views' => false,

'features' => [
    Features::registration(),
    Features::resetPasswords(),
    Features::emailVerification(),
    Features::updateProfileInformation(),
    Features::updatePasswords(),
    Features::twoFactorAuthentication([
        'confirm' => true,
        'confirmPassword' => true,
    ]),
],
```

### Authentication Configuration
```php
// config/auth.php
'guards' => [
    'superadmin' => [
        'driver' => 'session',
        'provider' => 'superadmins',
    ],
],

'providers' => [
    'superadmins' => [
        'driver' => 'eloquent',
        'model' => App\Models\SuperAdmin::class,
    ],
],
```

## 📖 Usage Guide

### 🔑 Initial Access
1. **Access URL**: `https://yourdomain.com/superadmin/login`
2. **Default Credentials**:
   - Email: `admin@facturarg.com`
   - Password: `admin123`
3. **Security**: Change password immediately after first login

### 🏠 Dashboard Overview
The dashboard provides:
- **Statistics Cards**: Total tenants, active tenants, suspended tenants, training videos
- **Recent Activities**: Latest tenant activities with timestamps
- **Quick Actions**: Direct links to common tasks
- **System Status**: Database connections, 2FA status, last login

### 👥 Managing Tenants

#### View All Tenants
1. Navigate to **Tenant Management** in sidebar
2. View list with: ID, Name, Domain, Email, Status, Creation Date, Last Activity
3. Use **DataTables** for sorting, searching, and pagination

#### Tenant Actions
- **View**: Detailed tenant information and activity statistics
- **Edit**: Modify tenant details (name, email, domain, status)
- **Suspend/Activate**: Change tenant status with reason tracking
- **Delete**: Remove tenant and associated database
- **Activities**: View detailed activity logs
- **Export**: Download activity reports as CSV

#### Tenant Status Management
```php
// Available statuses
'active'     // Tenant can access the system
'suspended'  // Tenant access is blocked
'pending'    // Tenant awaiting activation
```

### 📹 Managing Training Videos

#### Upload New Video
1. Navigate to **Training Videos** → **Create**
2. Fill in details:
   - **Title**: Video title
   - **Description**: Optional description
   - **Module**: Select appropriate module
   - **Video File**: Upload MP4, AVI, MOV, WMV, FLV (max 100MB)
   - **Thumbnail**: Optional image (max 2MB)
   - **Duration**: Video length in seconds
   - **Sort Order**: Display order

#### Video Management
- **View**: Video details with preview and thumbnail
- **Edit**: Modify video information and files
- **Enable/Disable**: Control video availability
- **Delete**: Remove video and associated files
- **Reorder**: Change display order

#### Available Modules
- Dashboard
- Product Management
- Sales Management
- Purchase Management
- Inventory Management
- Reports & Analytics
- Settings & Configuration
- E-Commerce
- Subscription Management
- AFIP Integration

### 👤 Profile Management
1. Navigate to **Profile** in top navigation
2. **Update Information**:
   - Name and email
   - Change password (requires current password)
3. **Two-Factor Authentication**:
   - Enable/Disable 2FA
   - View recovery codes
   - Security tips

## 🔐 Two-Factor Authentication (2FA)

### Enabling 2FA
1. **Access Profile**: Go to Profile → Two-Factor Authentication
2. **Click Enable**: Start 2FA setup process
3. **Scan QR Code**: Use Google Authenticator app
4. **Verify Setup**: Enter 6-digit code from app
5. **Save Recovery Codes**: Store backup codes securely

### Supported Authenticator Apps
- **Google Authenticator** (iOS/Android)
- **Authy** (Multi-platform)
- **Microsoft Authenticator** (iOS/Android)
- **Any TOTP-compatible app**

### Recovery Process
1. **Lost Device**: Use recovery codes to access account
2. **Enter Recovery Code**: One-time use backup codes
3. **Regenerate 2FA**: Set up new 2FA after recovery

### Security Features
- **Time-based Codes**: 30-second rotating codes
- **Rate Limiting**: Protection against brute force
- **Session Management**: Proper verification tracking
- **Recovery Codes**: 8 backup codes for emergencies

## 🔌 API Endpoints

### Authentication Endpoints
```
POST   /superadmin/login              # Login
POST   /superadmin/logout             # Logout
GET    /superadmin/2fa/challenge      # 2FA challenge page
POST   /superadmin/2fa/verify         # Verify 2FA code
POST   /superadmin/2fa/recovery       # Use recovery code
```

### Tenant Management Endpoints
```
GET    /superadmin/tenants            # List tenants
GET    /superadmin/tenants/data       # DataTables data
GET    /superadmin/tenants/{id}       # View tenant
GET    /superadmin/tenants/{id}/edit  # Edit form
PUT    /superadmin/tenants/{id}       # Update tenant
POST   /superadmin/tenants/{id}/suspend   # Suspend tenant
POST   /superadmin/tenants/{id}/activate  # Activate tenant
DELETE /superadmin/tenants/{id}       # Delete tenant
GET    /superadmin/tenants/{id}/activities    # View activities
GET    /superadmin/tenants/{id}/export-activities  # Export activities
```

### Training Video Endpoints
```
GET    /superadmin/training-videos            # List videos
GET    /superadmin/training-videos/data       # DataTables data
GET    /superadmin/training-videos/create     # Create form
POST   /superadmin/training-videos            # Store video
GET    /superadmin/training-videos/{id}       # View video
GET    /superadmin/training-videos/{id}/edit  # Edit form
PUT    /superadmin/training-videos/{id}       # Update video
POST   /superadmin/training-videos/{id}/toggle-status  # Toggle status
DELETE /superadmin/training-videos/{id}       # Delete video
POST   /superadmin/training-videos/reorder    # Reorder videos
GET    /superadmin/training-videos/module/{module}  # Videos by module
```

## 🔒 Security Considerations

### Password Security
- **Minimum Length**: 8 characters
- **Complexity**: Mix of letters, numbers, symbols
- **Regular Updates**: Change passwords periodically
- **No Reuse**: Avoid reusing passwords

### 2FA Security
- **Enable 2FA**: Required for all SuperAdmin accounts
- **Secure Storage**: Store recovery codes safely
- **Device Security**: Protect devices with 2FA apps
- **Backup Access**: Keep recovery codes accessible

### Access Control
- **IP Restrictions**: Consider IP whitelisting
- **Session Timeout**: Configure appropriate timeouts
- **Activity Monitoring**: Regular review of access logs
- **Principle of Least Privilege**: Minimal required access

### Data Protection
- **Encryption**: All sensitive data encrypted
- **Backup**: Regular database backups
- **Audit Logs**: Comprehensive activity tracking
- **GDPR Compliance**: Data protection regulations

## 🐛 Troubleshooting

### Common Issues

#### 1. "Route not defined" Error
```bash
# Clear route cache
php artisan route:clear
php artisan config:clear
```

#### 2. Database Connection Issues
```bash
# Check database configuration
php artisan config:cache
php artisan migrate:status --database=landlord
```

#### 3. 2FA Not Working
```bash
# Check Fortify configuration
php artisan config:clear
php artisan route:clear

# Verify Google2FA installation
composer show pragmarx/google2fa
```

#### 4. File Upload Issues
```bash
# Check storage permissions
chmod -R 775 storage bootstrap/cache
php artisan storage:link

# Verify upload limits in php.ini
upload_max_filesize = 100M
post_max_size = 100M
```

#### 5. QR Code Not Generating
```bash
# Check GD extension
php -m | grep gd

# Verify Google2FA configuration
composer dump-autoload
```

### Debug Mode
```bash
# Enable debug mode for troubleshooting
APP_DEBUG=true
APP_ENV=local

# Check logs
tail -f storage/logs/laravel.log
```

### Performance Issues
```bash
# Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Optimize for production
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## 🛠️ Maintenance

### Regular Tasks

#### Daily
- **Monitor Logs**: Check for errors and security issues
- **Backup Verification**: Ensure backups are successful
- **Activity Review**: Monitor tenant activities

#### Weekly
- **Security Updates**: Update dependencies and packages
- **Performance Review**: Check system performance
- **User Management**: Review and update user access

#### Monthly
- **Database Maintenance**: Optimize and clean databases
- **Security Audit**: Review access logs and permissions
- **Backup Testing**: Test backup restoration procedures

### Backup Procedures
```bash
# Database backup
mysqldump -u username -p landlord_db > backup_$(date +%Y%m%d).sql

# File backup
tar -czf storage_backup_$(date +%Y%m%d).tar.gz storage/

# Configuration backup
cp .env .env.backup_$(date +%Y%m%d)
```

### Update Procedures
```bash
# Update dependencies
composer update
npm update

# Run migrations
php artisan migrate --database=landlord

# Clear caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Optimize for production
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Monitoring
- **Error Logs**: Monitor Laravel logs for errors
- **Performance**: Track response times and resource usage
- **Security**: Monitor failed login attempts and suspicious activities
- **Backup Status**: Verify backup completion and integrity

## 📞 Support

### Documentation
- **Laravel Documentation**: https://laravel.com/docs
- **Fortify Documentation**: https://laravel.com/docs/fortify
- **Google2FA Documentation**: https://github.com/antonioribeiro/google2fa

### Contact Information
- **Technical Support**: [Your support email]
- **Security Issues**: [Your security email]
- **Documentation**: [Your documentation URL]

### Version Information
- **SuperAdmin Panel**: v1.0.0
- **Laravel**: 10.x
- **Fortify**: 1.27
- **Google2FA**: 8.0

---

**⚠️ Important**: This SuperAdmin panel provides full system access. Ensure proper security measures are in place and regularly review access permissions. 