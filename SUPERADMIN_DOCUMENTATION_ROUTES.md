# Superadmin Documentation Management Routes

## Overview

The documentation management system has been enhanced with dedicated superadmin routes that are **exclusively accessible to superadmins** and **completely isolated from tenant access**. This ensures that documentation management is centralized and secure.

## Route Structure

### Base URL Pattern
All superadmin documentation routes follow this pattern:
```
/superadmin/documentation/{action}
```

### Route Names
All routes are prefixed with `superadmin.documentation.` for easy identification and security.

## Available Routes

### 1. **Index Route** - Main Documentation Dashboard
```php
Route::get('/', [DocumentationController::class, 'index'])->name('index');
```
- **URL**: `/superadmin/documentation`
- **Purpose**: Main dashboard for managing all documentation articles
- **Access**: Superadmin only
- **Features**: DataTables integration, bulk operations, search/filter

### 2. **Data Route** - AJAX Data for DataTables
```php
Route::get('/data', [DocumentationController::class, 'getDocumentationData'])->name('data');
```
- **URL**: `/superadmin/documentation/data`
- **Purpose**: Provides JSON data for DataTables
- **Access**: Superadmin only
- **Features**: Pagination, sorting, filtering

### 3. **Create Routes** - Add New Documentation
```php
Route::get('/create', [DocumentationController::class, 'create'])->name('create');
Route::post('/', [DocumentationController::class, 'store'])->name('store');
```
- **URLs**: `/superadmin/documentation/create` (GET), `/superadmin/documentation` (POST)
- **Purpose**: Create new documentation articles
- **Access**: Superadmin only
- **Features**: Form validation, slug generation, category management

### 4. **Show Route** - View Documentation Details
```php
Route::get('/{id}', [DocumentationController::class, 'show'])->name('show');
```
- **URL**: `/superadmin/documentation/{id}`
- **Purpose**: View detailed information about a specific article
- **Access**: Superadmin only

### 5. **Edit Routes** - Modify Documentation
```php
Route::get('/{id}/edit', [DocumentationController::class, 'edit'])->name('edit');
Route::put('/{id}', [DocumentationController::class, 'update'])->name('update');
```
- **URLs**: `/superadmin/documentation/{id}/edit` (GET), `/superadmin/documentation/{id}` (PUT)
- **Purpose**: Edit existing documentation articles
- **Access**: Superadmin only
- **Features**: Pre-populated forms, validation

### 6. **Status Toggle** - Activate/Deactivate Articles
```php
Route::post('/{id}/toggle-status', [DocumentationController::class, 'toggleStatus'])->name('toggle-status');
```
- **URL**: `/superadmin/documentation/{id}/toggle-status`
- **Purpose**: Toggle article active/inactive status
- **Access**: Superadmin only
- **Features**: AJAX response, immediate UI update

### 7. **Delete Route** - Remove Documentation
```php
Route::delete('/{id}', [DocumentationController::class, 'destroy'])->name('destroy');
```
- **URL**: `/superadmin/documentation/{id}`
- **Purpose**: Delete documentation articles
- **Access**: Superadmin only
- **Features**: Confirmation dialog, AJAX response

### 8. **Categories Route** - Get All Categories
```php
Route::get('/categories', [DocumentationController::class, 'categories'])->name('categories');
```
- **URL**: `/superadmin/documentation/categories`
- **Purpose**: Get all available documentation categories
- **Access**: Superadmin only
- **Features**: JSON response for dropdowns

### 9. **Search Route** - Search Documentation
```php
Route::get('/search', [DocumentationController::class, 'search'])->name('search');
```
- **URL**: `/superadmin/documentation/search?q={query}`
- **Purpose**: Search documentation articles
- **Access**: Superadmin only
- **Features**: Full-text search, JSON response

### 10. **Reorder Route** - Change Article Order
```php
Route::post('/reorder', [DocumentationController::class, 'reorder'])->name('reorder');
```
- **URL**: `/superadmin/documentation/reorder`
- **Purpose**: Reorder documentation articles
- **Access**: Superadmin only
- **Features**: Drag-and-drop support, bulk order update

### 11. **Export Route** - Export to Excel
```php
Route::get('/export', [DocumentationController::class, 'export'])->name('export');
```
- **URL**: `/superadmin/documentation/export`
- **Purpose**: Export all documentation to Excel file
- **Access**: Superadmin only
- **Features**: Excel formatting, all fields included

### 12. **Import Route** - Import from Excel
```php
Route::post('/import', [DocumentationController::class, 'import'])->name('import');
```
- **URL**: `/superadmin/documentation/import`
- **Purpose**: Import documentation from Excel file
- **Access**: Superadmin only
- **Features**: Validation, error handling, bulk import

### 13. **Statistics Route** - Documentation Analytics
```php
Route::get('/statistics', [DocumentationController::class, 'statistics'])->name('statistics');
```
- **URL**: `/superadmin/documentation/statistics`
- **Purpose**: Get documentation statistics
- **Access**: Superadmin only
- **Features**: JSON response with counts and metrics

### 14. **Bulk Actions Route** - Mass Operations
```php
Route::post('/bulk-action', [DocumentationController::class, 'bulkAction'])->name('bulk-action');
```
- **URL**: `/superadmin/documentation/bulk-action`
- **Purpose**: Perform bulk operations on multiple articles
- **Access**: Superadmin only
- **Features**: Activate, deactivate, delete multiple articles

## Security Features

### 1. **Authentication Middleware**
```php
Route::middleware(['auth:superadmin', '2fa:superadmin'])
```
- All routes require superadmin authentication
- Two-factor authentication enforced
- Session-based security

### 2. **Route Isolation**
- Routes are completely separate from tenant routes
- No cross-access between superadmin and tenant systems
- Dedicated superadmin guard

### 3. **Database Connection**
- Uses landlord database connection
- Tenant databases cannot access documentation
- Centralized data management

### 4. **Permission Validation**
- All operations validated at controller level
- Input sanitization and validation
- CSRF protection enabled

## Access Control

### Superadmin Access
- ✅ Full CRUD operations
- ✅ Bulk operations
- ✅ Import/Export functionality
- ✅ Statistics and analytics
- ✅ Category management
- ✅ Search and filtering

### Tenant Access
- ❌ No access to superadmin routes
- ❌ Cannot modify documentation
- ❌ Cannot access landlord database
- ✅ Read-only access to documentation center
- ✅ Access to AI chatbot

## Database Security

### Landlord Database
- All documentation stored in landlord database
- Tenant databases have no documentation tables
- Centralized backup and recovery
- Audit logging for all changes

### Connection Isolation
```php
protected $connection = 'landlord';
```
- Documentation model uses landlord connection
- Tenant models cannot access landlord database
- Complete data separation

## Usage Examples

### Accessing Routes in Views
```php
// Superadmin documentation index
<a href="{{ route('superadmin.documentation.index') }}">Manage Documentation</a>

// Create new article
<a href="{{ route('superadmin.documentation.create') }}">Create Article</a>

// Edit specific article
<a href="{{ route('superadmin.documentation.edit', $article->id) }}">Edit</a>
```

### AJAX Calls
```javascript
// Toggle status
$.post('{{ route("superadmin.documentation.toggle-status", $article->id) }}', {
    _token: '{{ csrf_token() }}'
});

// Delete article
$.ajax({
    url: '{{ route("superadmin.documentation.destroy", $article->id) }}',
    type: 'DELETE',
    data: { _token: '{{ csrf_token() }}' }
});
```

### DataTables Integration
```javascript
$('#documentation-table').DataTable({
    ajax: '{{ route("superadmin.documentation.data") }}',
    columns: [
        { data: 'title' },
        { data: 'category' },
        { data: 'status_badge' },
        { data: 'actions' }
    ]
});
```

## Error Handling

### Validation Errors
- Form validation with detailed error messages
- AJAX error responses for dynamic operations
- User-friendly error display

### Database Errors
- Graceful handling of database connection issues
- Fallback responses for failed operations
- Logging of all errors for debugging

### Security Errors
- 403 Forbidden for unauthorized access
- 404 Not Found for invalid resources
- 500 Internal Server Error for system issues

## Performance Optimization

### Caching
- Route caching for improved performance
- Database query optimization
- Asset caching for static resources

### Database Optimization
- Indexed columns for faster searches
- Efficient queries with proper joins
- Pagination for large datasets

## Monitoring and Logging

### Access Logs
- All superadmin actions logged
- IP address tracking
- Session monitoring

### Error Logs
- Detailed error logging
- Stack traces for debugging
- Performance metrics

### Audit Trail
- Documentation changes tracked
- User action history
- Data modification logs

## Best Practices

### Security
1. Always use HTTPS in production
2. Regular security audits
3. Keep superadmin credentials secure
4. Monitor access patterns

### Performance
1. Use pagination for large datasets
2. Implement caching where appropriate
3. Optimize database queries
4. Monitor response times

### Maintenance
1. Regular backups of landlord database
2. Update documentation regularly
3. Monitor system performance
4. Keep dependencies updated

## Troubleshooting

### Common Issues

**Route not found:**
- Check if superadmin routes are registered
- Verify route service provider configuration
- Clear route cache: `php artisan route:clear`

**Authentication failed:**
- Verify superadmin guard configuration
- Check session configuration
- Ensure 2FA is properly set up

**Database connection error:**
- Verify landlord database configuration
- Check database credentials
- Ensure landlord database exists

### Debug Commands
```bash
# List all superadmin routes
php artisan route:list --name=superadmin

# Clear route cache
php artisan route:clear

# Check superadmin authentication
php artisan tinker
>>> auth()->guard('superadmin')->check()

# Test landlord database connection
php artisan tinker
>>> DB::connection('landlord')->table('documentation')->count()
```

## Conclusion

The superadmin documentation management routes provide a secure, isolated, and comprehensive system for managing documentation across all tenants. The complete separation from tenant access ensures data integrity and security while providing powerful management capabilities for superadmins. 