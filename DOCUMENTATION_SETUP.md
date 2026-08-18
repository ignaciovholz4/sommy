# Documentation System Setup - Landlord Database

## Overview

The documentation system has been configured to retrieve documentation from the **landlord database** instead of individual tenant databases. This ensures that all tenants have access to the same documentation content and maintains consistency across the multitenancy system.

## Architecture

### Database Structure
- **Landlord Database** (`landlord_db`): Contains global documentation content
- **Tenant Databases**: Individual tenant data (documentation is NOT stored here)
- **Documentation Model**: Configured to use landlord database connection

### Key Changes Made

1. **Documentation Model** (`app/Models/Documentation.php`)
   - Added `protected $connection = 'landlord';` to use landlord database
   - All documentation queries now go to the landlord database

2. **Database Seeder** (`database/seeders/DatabaseSeeder.php`)
   - Updated to include `DocumentationSeeder` for landlord database
   - Removed tenant-specific documentation seeding

3. **Documentation Seeder** (`database/seeders/DocumentationSeeder.php`)
   - Added documentation clarifying its purpose for landlord database
   - Contains comprehensive documentation content

## Setup Instructions

### 1. Run Landlord Database Migration
```bash
# Ensure landlord database exists and is configured
php artisan migrate --database=landlord
```

### 2. Seed Documentation in Landlord Database
```bash
# Run the documentation seeder for landlord database
php artisan db:seed --class=DocumentationSeeder
```

### 3. Verify Configuration
```bash
# Clear cache to ensure changes take effect
php artisan cache:clear
php artisan config:clear
```

## Database Configuration

### Landlord Database Connection
The landlord database is configured in `config/database.php`:

```php
'landlord' => [
    'driver' => 'mysql',
    'host' => env('DB_HOST', '127.0.0.1'),
    'port' => env('DB_PORT', '3306'),
    'database' => 'landlord_db',
    'username' => env('DB_USERNAME', 'laravel_user'),
    'password' => env('DB_PASSWORD', 'your_strong_password'),
    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
    'prefix' => '',
    'prefix_indexes' => true,
    'strict' => true,
    'engine' => null,
],
```

### Environment Variables
Ensure these are set in your `.env` file:
```env
DB_HOST=127.0.0.1
DB_PORT=3306
DB_USERNAME=laravel_user
DB_PASSWORD=your_strong_password
```

## Usage

### Superadmin Access
Superadmins can access documentation management through the left sidebar:

1. **Manage Documentation** section (superadmin only):
   - **All Articles**: View and manage all documentation
   - **Create New Article**: Add new documentation content
   - **View Documentation**: Access the public documentation center
   - **AI Help Bot**: Test and manage the AI chatbot

### User Access
Regular users can access documentation through:

1. **Help & Support** section:
   - **AI Help Bot**: Get AI-powered assistance
   - **Documentation Center**: Browse and search documentation

### Managing Documentation
1. Navigate to **Manage Documentation** → **All Articles**
2. Create, edit, or delete documentation articles
3. All changes are stored in the landlord database
4. Changes are immediately available to all tenants

## Benefits

### Centralized Management
- Single source of truth for documentation
- Consistent content across all tenants
- Easier maintenance and updates

### Performance
- Reduced database redundancy
- Efficient caching across tenants
- Optimized queries

### Scalability
- Documentation scales with tenant growth
- No need to replicate documentation per tenant
- Simplified backup and recovery

## Troubleshooting

### Common Issues

**Documentation not loading:**
```bash
# Check landlord database connection
php artisan tinker
>>> DB::connection('landlord')->table('documentation')->count()
```

**Seeder not working:**
```bash
# Ensure landlord database exists
mysql -u username -p -e "CREATE DATABASE IF NOT EXISTS landlord_db;"

# Run seeder with specific connection
php artisan db:seed --class=DocumentationSeeder --database=landlord
```

**Model connection issues:**
```bash
# Clear model cache
php artisan config:clear
php artisan cache:clear
```

### Verification Commands

```bash
# Check if documentation exists in landlord database
php artisan tinker
>>> use App\Models\Documentation;
>>> Documentation::count()

# Check documentation categories
>>> Documentation::distinct()->pluck('category')

# Test search functionality
>>> Documentation::search('sales')->get()
```

## Migration from Tenant-Based Documentation

If you previously had documentation in tenant databases:

1. **Backup existing data** (if needed)
2. **Run landlord seeder** to populate central documentation
3. **Remove tenant documentation tables** (optional)
4. **Update any custom code** that referenced tenant documentation

## Security Considerations

- **Access Control**: Ensure only authorized users can manage documentation
- **Backup Strategy**: Include landlord database in backup procedures
- **Audit Logging**: Monitor documentation changes
- **Data Protection**: Follow GDPR and privacy regulations

## Support

For issues with the documentation system:
1. Check landlord database connectivity
2. Verify seeder execution
3. Review application logs
4. Contact development team

## Related Files

- `app/Models/Documentation.php` - Documentation model
- `app/Http/Controllers/DocumentationController.php` - Documentation controller
- `app/Services/ChatbotService.php` - AI chatbot service
- `database/seeders/DocumentationSeeder.php` - Documentation seeder
- `config/database.php` - Database configuration
- `config/multitenancy.php` - Multitenancy configuration 