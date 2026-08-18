# Quick Setup Guide - SuperAdmin Panel

## 🚀 Quick Start (5 Minutes)

### 1. Install Dependencies
```bash
composer install
npm install && npm run dev
```

### 2. Environment Setup
```bash
cp .env.example .env
php artisan key:generate
```

### 3. Database Configuration
Add to your `.env` file:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=landlord_db
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

### 4. Run Setup Command
```bash
php artisan superadmin:setup
```

### 5. Access the Panel
- **URL**: `http://yourdomain.com/superadmin/login`
- **Email**: `admin@facturarg.com`
- **Password**: `admin123`

## 🔐 Enable 2FA (Recommended)

1. **Login** to SuperAdmin panel
2. **Go to Profile** → Two-Factor Authentication
3. **Click Enable** → Setup 2FA
4. **Scan QR Code** with Google Authenticator
5. **Enter 6-digit code** to confirm
6. **Save recovery codes** securely

## 📋 Default Features

### ✅ What's Working
- **Authentication**: Login/logout with 2FA
- **Dashboard**: Statistics and recent activities
- **Tenant Management**: CRUD operations with status control
- **Training Videos**: Upload, manage, and organize videos
- **Profile Management**: Update info and security settings
- **Activity Monitoring**: Track all tenant activities
- **Export Functionality**: CSV exports for reports

### 🔧 Quick Commands
```bash
# View all routes
php artisan route:list --name=superadmin

# Clear caches (if issues)
php artisan cache:clear && php artisan config:clear

# Check database status
php artisan migrate:status --database=landlord

# View logs
tail -f storage/logs/laravel.log
```

## 🆘 Common Issues & Solutions

### Issue: "Route not defined"
**Solution**: Clear route cache
```bash
php artisan route:clear
```

### Issue: QR code not working
**Solution**: Check Google2FA installation
```bash
composer require pragmarx/google2fa
php artisan config:clear
```

### Issue: File upload fails
**Solution**: Check permissions and limits
```bash
chmod -R 775 storage bootstrap/cache
# Check php.ini: upload_max_filesize = 100M
```

### Issue: Database connection error
**Solution**: Verify database configuration
```bash
php artisan config:cache
php artisan migrate:status --database=landlord
```

## 📞 Need Help?

1. **Check the full README**: `SUPERADMIN_README.md`
2. **Review logs**: `storage/logs/laravel.log`
3. **Clear caches**: `php artisan cache:clear`
4. **Contact support**: [Your support email]

---

**🎯 You're all set!** The SuperAdmin panel is ready to use with full 2FA security. 