# SuperAdmin Panel Setup Guide

This guide will help you set up the complete SuperAdmin panel for Facturarg with all the requested features.

## 🚀 Features Implemented

### ✅ SuperAdmin Panel
- **Complete SuperAdmin authentication system**
- **Two-Factor Authentication (2FA)** for enhanced security
- **Dashboard with real-time statistics**
- **Profile management with password change**

### ✅ Tenant Management
- **Complete tenant listing with DataTables**
- **Enable/Disable tenant status** (Active, Suspended, Pending)
- **Tenant activity monitoring**
- **Tenant details and activity history**
- **Export tenant activities to CSV**
- **Delete tenants with database cleanup**

### ✅ Training Video Management
- **Upload training videos** with thumbnails
- **Organize videos by modules** (Dashboard, Products, Sales, etc.)
- **Enable/Disable videos** for tenant access
- **Video duration tracking**
- **Sort and reorder videos**
- **Delete videos with file cleanup**

### ✅ Client Activity Monitoring
- **Comprehensive activity tracking** for all tenants
- **Activity types**: Login, Logout, Purchase, Sale, Product, User, System
- **IP address and user agent tracking**
- **Activity statistics and reporting**
- **Automatic cleanup of old activities**

### ✅ Security Features
- **Two-Factor Authentication** using authenticator apps
- **Session management** with 2FA verification
- **Secure password handling**
- **Activity logging** for all admin actions

## 📋 Prerequisites

1. **Laravel 10** with Spatie Multi-tenancy package
2. **MySQL/MariaDB** database
3. **PHP 8.1+** with required extensions
4. **Composer** for package management

## 🛠️ Installation Steps

### 1. Run the Setup Command

```bash
php artisan superadmin:setup
```

This command will:
- Run all landlord database migrations
- Create the initial SuperAdmin user
- Set up storage links
- Clear all caches

### 2. Configure Environment Variables

Add these to your `.env` file:

```env
# SuperAdmin Database Configuration
DB_CONNECTION=tenant
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=landlord_db
DB_USERNAME=your_username
DB_PASSWORD=your_password

# Landlord Database
LANDLORD_DB_DATABASE=landlord_db
LANDLORD_DB_USERNAME=your_username
LANDLORD_DB_PASSWORD=your_password

# Admin Database (for tenant operations)
DB_ADMIN_USER=admin_user
DB_ADMIN_PASSWORD=admin_password

# Tenant Database
TENANT_DB_USERNAME=tenant_user
TENANT_DB_PASSWORD=tenant_password
```

### 3. Create Required Directories

```bash
mkdir -p storage/app/public/training-videos
mkdir -p storage/app/public/training-thumbnails
chmod -R 775 storage/app/public
```

### 4. Set Up 2FA (Optional)

The SuperAdmin can enable 2FA from their profile page after login.

## 🔐 Default Credentials

After setup, you can access the SuperAdmin panel with:

- **URL**: `http://your-domain.com/superadmin/login`
- **Email**: `admin@facturarg.com`
- **Password**: `admin123`

⚠️ **Important**: Change the default password immediately after first login!

## 📁 File Structure

```
app/
├── Console/Commands/
│   └── SetupSuperAdmin.php
├── Http/Controllers/SuperAdmin/
│   ├── SuperAdminController.php
│   ├── TenantController.php
│   └── TrainingVideoController.php
├── Http/Middleware/
│   └── Ensure2FAVerified.php
├── Models/
│   ├── SuperAdmin.php
│   ├── TrainingVideo.php
│   ├── ClientActivity.php
│   └── Tenant.php (updated)
├── Services/
│   └── ClientActivityService.php
└── Providers/
    └── RouteServiceProvider.php (updated)

database/
├── migrations/landlord/
│   ├── 2025_01_01_000001_create_super_admins_table.php
│   ├── 2025_01_01_000002_create_training_videos_table.php
│   ├── 2025_01_01_000003_create_client_activities_table.php
│   └── 2025_01_01_000004_add_status_to_tenants_table.php
└── seeders/
    └── SuperAdminSeeder.php

resources/views/superadmin/
├── layouts/
│   └── app.blade.php
├── auth/
│   ├── login.blade.php
│   └── 2fa.blade.php
├── dashboard.blade.php
├── tenants/
│   └── index.blade.php
└── training-videos/
    └── index.blade.php

routes/
└── superadmin.php
```

## 🎯 Usage Guide

### Accessing the SuperAdmin Panel

1. Navigate to `/superadmin/login`
2. Enter credentials
3. If 2FA is enabled, enter the code from your authenticator app
4. Access the dashboard

### Managing Tenants

1. Go to **Tenant Management** in the sidebar
2. View all tenants with their status
3. Click **View** to see tenant details and activities
4. Use **Suspend/Activate** buttons to manage tenant access
5. Export activities for reporting

### Managing Training Videos

1. Go to **Training Videos** in the sidebar
2. Click **Upload Video** to add new training content
3. Select the appropriate module
4. Upload video file and thumbnail
5. Enable/disable videos as needed

### Monitoring Client Activities

- Activities are automatically logged for all tenant actions
- View recent activities on the dashboard
- Access detailed activity history per tenant
- Export activity data for analysis

## 🔧 Configuration

### Customizing Activity Tracking

Edit `app/Services/ClientActivityService.php` to add custom activity types:

```php
public static function logCustomActivity($tenantId, $customType, $description)
{
    return self::log($tenantId, $customType, $description);
}
```

### Adding New Training Video Modules

Update the modules array in `TrainingVideoController.php`:

```php
$modules = [
    'dashboard' => 'Dashboard',
    'products' => 'Product Management',
    'sales' => 'Sales Management',
    'your_module' => 'Your Module Name',
];
```

### Customizing 2FA

The 2FA system uses Laravel Fortify. Configure it in `config/fortify.php`:

```php
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

## 🚨 Security Considerations

1. **Change default credentials** immediately after setup
2. **Enable 2FA** for all SuperAdmin accounts
3. **Regular password updates** for SuperAdmin users
4. **Monitor activity logs** for suspicious activities
5. **Backup tenant databases** before deletion operations
6. **Secure file uploads** for training videos

## 🐛 Troubleshooting

### Common Issues

1. **Migration Errors**
   ```bash
   php artisan migrate:status --database=landlord
   php artisan migrate:rollback --database=landlord
   ```

2. **Storage Issues**
   ```bash
   php artisan storage:link
   chmod -R 775 storage/app/public
   ```

3. **Permission Issues**
   ```bash
   chown -R www-data:www-data storage/
   chmod -R 775 storage/
   ```

4. **Cache Issues**
   ```bash
   php artisan config:clear
   php artisan cache:clear
   php artisan route:clear
   php artisan view:clear
   ```

### Support

For issues or questions:
1. Check the Laravel logs: `storage/logs/laravel.log`
2. Verify database connections
3. Ensure all required packages are installed
4. Check file permissions

## 📈 Performance Optimization

1. **Database Indexing**: Activities table is indexed for better performance
2. **Activity Cleanup**: Old activities are automatically cleaned up
3. **Caching**: Use Redis for better session and cache performance
4. **File Storage**: Consider using cloud storage for training videos

## 🔄 Maintenance

### Regular Tasks

1. **Clean old activities** (older than 90 days)
2. **Backup tenant databases**
3. **Monitor storage usage** for training videos
4. **Update SuperAdmin passwords** regularly
5. **Review activity logs** for security

### Commands

```bash
# Clean old activities
php artisan tinker
>>> App\Services\ClientActivityService::cleanOldActivities(90);

# Backup tenant databases
php artisan tenants:artisan "backup:run" --tenant=all
```

---

🎉 **Your SuperAdmin panel is now ready!** 

Access it at `/superadmin/login` and start managing your multi-tenant SaaS application. 