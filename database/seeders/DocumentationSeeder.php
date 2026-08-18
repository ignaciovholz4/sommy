<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Documentation;
use Illuminate\Support\Str;

/**
 * DocumentationSeeder for Landlord Database
 * 
 * This seeder populates the documentation table in the landlord database.
 * Documentation should be retrieved from the landlord database to ensure
 * all tenants have access to the same documentation content.
 * 
 * Run this seeder with: php artisan db:seed --class=DocumentationSeeder
 */
class DocumentationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $documentation = [
            [
                'title' => 'Getting Started with the System',
                'content' => "Welcome to our business management system! This guide will help you get started with the basic features and navigation.

## First Steps

1. **Login to your account** using your credentials
2. **Complete your profile** with accurate information
3. **Explore the dashboard** to understand the main features
4. **Set up your preferences** in the configuration section

## Main Modules

- **Sales Management**: Create and manage sales transactions
- **Inventory Management**: Track products and stock levels
- **Customer Management**: Store and manage customer information
- **Reports & Analytics**: Generate business insights
- **User Management**: Manage system users and permissions

## Quick Tips

- Use the search function to quickly find what you need
- Check the notification center for important updates
- Use keyboard shortcuts for faster navigation
- Bookmark frequently used pages for quick access

For more detailed information about each module, please refer to the specific documentation sections.",
                'category' => 'Getting Started',
                'tags' => ['welcome', 'basics', 'navigation', 'setup'],
                'meta_description' => 'Complete guide to getting started with the business management system, including first steps and main features.',
                'order' => 1,
                'is_active' => true,
            ],
            [
                'title' => 'How to Create a New Sale',
                'content' => "Creating a new sale is one of the most common tasks in the system. Follow these steps to complete a sale transaction.

## Step-by-Step Process

### 1. Access the Sales Module
- Navigate to **Sales** in the main menu
- Click on **New Sale** or **Create Sale**

### 2. Select Customer
- Choose an existing customer from the dropdown
- Or create a new customer on the spot
- Verify customer information is correct

### 3. Add Products
- Search for products by name, code, or barcode
- Select the desired product from the results
- Enter quantity and verify price
- Add multiple products as needed

### 4. Review and Complete
- Check all items and quantities
- Verify total amount and taxes
- Apply any discounts if applicable
- Choose payment method
- Complete the sale

## Important Notes

- Always verify product availability before completing sale
- Double-check customer information
- Ensure accurate pricing and quantities
- Save the transaction receipt for records

## Troubleshooting

If you encounter issues:
- Check product stock levels
- Verify customer information
- Ensure proper permissions
- Contact support if problems persist",
                'category' => 'Sales Management',
                'tags' => ['sales', 'transactions', 'customers', 'products', 'checkout'],
                'meta_description' => 'Complete guide to creating new sales transactions, including customer selection, product addition, and payment processing.',
                'order' => 1,
                'is_active' => true,
            ],
            [
                'title' => 'Inventory Management Guide',
                'content' => "Effective inventory management is crucial for business success. Learn how to manage your product inventory efficiently.

## Adding New Products

### Product Information
- **Product Name**: Clear, descriptive name
- **SKU/Code**: Unique product identifier
- **Category**: Organize products by type
- **Description**: Detailed product information
- **Images**: Upload product photos

### Stock Management
- **Initial Stock**: Set starting quantity
- **Minimum Stock**: Set reorder threshold
- **Cost Price**: Product acquisition cost
- **Selling Price**: Retail price for customers

## Managing Stock Levels

### Stock Updates
- **Manual Adjustments**: Update quantities manually
- **Stock Transfers**: Move stock between locations
- **Stock Takes**: Periodic inventory counts
- **Automatic Alerts**: Low stock notifications

### Stock Reports
- Current stock levels
- Stock movement history
- Low stock alerts
- Stock valuation reports

## Best Practices

- Regular stock counts
- Monitor fast-moving items
- Set appropriate reorder points
- Keep accurate cost information
- Regular price reviews",
                'category' => 'Inventory Management',
                'tags' => ['inventory', 'stock', 'products', 'management', 'reports'],
                'meta_description' => 'Comprehensive guide to inventory management including adding products, stock control, and best practices.',
                'order' => 1,
                'is_active' => true,
            ],
            [
                'title' => 'Customer Management',
                'content' => "Managing customer relationships is essential for business growth. Learn how to effectively manage customer information and interactions.

## Adding New Customers

### Customer Information
- **Personal Details**: Name, contact information
- **Address**: Billing and shipping addresses
- **Contact Methods**: Phone, email, preferred contact
- **Customer Type**: Individual, business, VIP status
- **Notes**: Additional information and preferences

### Customer Categories
- **Regular Customers**: Standard service level
- **VIP Customers**: Premium service and benefits
- **Business Customers**: Corporate accounts
- **Wholesale Customers**: Bulk purchase discounts

## Customer Interactions

### Communication History
- Track all customer interactions
- Record phone calls and meetings
- Store email communications
- Note customer preferences

### Sales History
- Complete purchase history
- Payment records
- Return and refund history
- Customer satisfaction ratings

## Customer Service

### Support Tickets
- Create and track support requests
- Assign priority levels
- Monitor resolution times
- Follow up on resolved issues

### Customer Feedback
- Collect customer reviews
- Monitor satisfaction scores
- Address complaints promptly
- Implement improvements based on feedback",
                'category' => 'Customer Management',
                'tags' => ['customers', 'relationships', 'service', 'support', 'feedback'],
                'meta_description' => 'Guide to customer management including adding customers, tracking interactions, and providing excellent service.',
                'order' => 1,
                'is_active' => true,
            ],
            [
                'title' => 'Generating Reports',
                'content' => "Reports provide valuable insights into your business performance. Learn how to generate and interpret various reports.

## Sales Reports

### Daily Sales Report
- Total sales for the day
- Number of transactions
- Average transaction value
- Top-selling products

### Monthly Sales Report
- Monthly sales summary
- Sales growth trends
- Performance comparisons
- Revenue analysis

## Inventory Reports

### Stock Level Report
- Current stock quantities
- Low stock alerts
- Stock value calculations
- Product performance

### Stock Movement Report
- Stock in/out transactions
- Stock transfer history
- Stock adjustment records
- Movement patterns

## Financial Reports

### Profit and Loss
- Revenue and expenses
- Gross profit margins
- Net profit calculations
- Period comparisons

### Cash Flow Report
- Cash inflows and outflows
- Payment collection status
- Outstanding receivables
- Cash position analysis

## Custom Reports

### Report Builder
- Create custom reports
- Select specific data fields
- Apply filters and conditions
- Export in various formats

### Scheduled Reports
- Automate report generation
- Email delivery options
- Regular reporting cycles
- Report archiving",
                'category' => 'Reports & Analytics',
                'tags' => ['reports', 'analytics', 'sales', 'inventory', 'financial'],
                'meta_description' => 'Complete guide to generating and interpreting business reports including sales, inventory, and financial analytics.',
                'order' => 1,
                'is_active' => true,
            ],
            [
                'title' => 'User and Role Management',
                'content' => "Proper user and role management ensures system security and appropriate access levels. Learn how to manage users effectively.

## User Management

### Creating Users
- **User Information**: Name, email, contact details
- **Login Credentials**: Username and password
- **Role Assignment**: Assign appropriate roles
- **Department**: Organize users by department
- **Status**: Active or inactive status

### User Permissions
- **Module Access**: Control access to system modules
- **Function Permissions**: Specific function permissions
- **Data Access**: Control data visibility
- **Action Permissions**: Create, edit, delete permissions

## Role Management

### Predefined Roles
- **Administrator**: Full system access
- **Manager**: Management-level access
- **Sales Staff**: Sales module access
- **Inventory Staff**: Inventory module access
- **Cashier**: Point-of-sale access

### Custom Roles
- Create custom roles as needed
- Define specific permissions
- Assign multiple roles to users
- Role hierarchy management

## Security Features

### Password Policies
- Minimum password requirements
- Password expiration rules
- Account lockout policies
- Two-factor authentication

### Access Control
- IP address restrictions
- Session timeouts
- Login attempt limits
- Activity logging

## Best Practices

- Regular user access reviews
- Principle of least privilege
- Secure password policies
- Regular security audits
- User training on security",
                'category' => 'User Management',
                'tags' => ['users', 'roles', 'permissions', 'security', 'access'],
                'meta_description' => 'Guide to user and role management including creating users, assigning permissions, and security best practices.',
                'order' => 1,
                'is_active' => true,
            ],
            [
                'title' => 'System Configuration',
                'content' => "Configure your system settings to match your business requirements and preferences.

## General Settings

### Company Information
- **Company Name**: Your business name
- **Address**: Business address details
- **Contact Information**: Phone, email, website
- **Tax Information**: Tax ID and registration
- **Logo**: Upload company logo

### System Preferences
- **Language**: System language setting
- **Timezone**: Local timezone configuration
- **Currency**: Default currency setting
- **Date Format**: Date display format
- **Number Format**: Number formatting preferences

## Business Settings

### Sales Configuration
- **Tax Rates**: Configure tax percentages
- **Discount Policies**: Set discount rules
- **Payment Methods**: Available payment options
- **Invoice Settings**: Invoice format and numbering
- **Receipt Settings**: Receipt format options

### Inventory Settings
- **Stock Alerts**: Low stock notification levels
- **Auto-reorder**: Automatic reorder settings
- **Product Categories**: Manage product categories
- **Units of Measure**: Product measurement units
- **Barcode Settings**: Barcode configuration

## Security Settings

### Access Control
- **Session Timeout**: Automatic logout settings
- **Password Policy**: Password requirements
- **Login Restrictions**: IP and time restrictions
- **Audit Logging**: Activity tracking settings

### Backup Settings
- **Auto Backup**: Automatic backup schedule
- **Backup Location**: Backup storage location
- **Retention Policy**: Backup retention rules
- **Recovery Options**: Data recovery settings",
                'category' => 'System Configuration',
                'tags' => ['configuration', 'settings', 'preferences', 'business', 'security'],
                'meta_description' => 'Complete guide to system configuration including general settings, business preferences, and security options.',
                'order' => 1,
                'is_active' => true,
            ],
            [
                'title' => 'Common Troubleshooting',
                'content' => "Find solutions to common issues and problems you might encounter while using the system.

## Login Issues

### Can't Login
- **Check Credentials**: Verify username and password
- **Account Status**: Ensure account is active
- **Browser Issues**: Clear cache and cookies
- **Network Problems**: Check internet connection
- **System Status**: Verify system availability

### Password Problems
- **Reset Password**: Use password reset function
- **Password Requirements**: Check password policy
- **Account Lockout**: Wait for lockout period
- **Contact Admin**: Request admin assistance

## System Performance

### Slow Performance
- **Clear Cache**: Clear browser cache
- **Close Tabs**: Close unnecessary browser tabs
- **Check Network**: Verify internet speed
- **System Load**: Check system resources
- **Database Issues**: Contact technical support

### Error Messages
- **Note Error Code**: Record error details
- **Check Logs**: Review system logs
- **Restart Application**: Try restarting the system
- **Contact Support**: Report persistent errors

## Data Issues

### Missing Data
- **Check Filters**: Verify search filters
- **Date Range**: Check date range settings
- **Permissions**: Verify access permissions
- **Data Sync**: Check data synchronization
- **Backup Recovery**: Restore from backup if needed

### Data Accuracy
- **Verify Input**: Check data entry accuracy
- **Update Information**: Refresh data sources
- **Sync Issues**: Resolve synchronization problems
- **Data Validation**: Run data validation checks

## Getting Help

### Support Channels
- **Help Documentation**: Check this documentation
- **AI Assistant**: Use the built-in AI chatbot
- **Email Support**: Send detailed email
- **Phone Support**: Call support hotline
- **Live Chat**: Use online chat support

### Before Contacting Support
- **Document Issue**: Record problem details
- **Screenshots**: Take screenshots of errors
- **Steps to Reproduce**: Note exact steps
- **System Information**: Provide system details
- **Error Messages**: Copy exact error text",
                'category' => 'Troubleshooting',
                'tags' => ['troubleshooting', 'support', 'errors', 'problems', 'help'],
                'meta_description' => 'Troubleshooting guide for common system issues including login problems, performance issues, and data problems.',
                'order' => 1,
                'is_active' => true,
            ],
        ];

        foreach ($documentation as $doc) {
            $slug = Str::slug($doc['title']);
            $counter = 1;
            
            while (Documentation::where('slug', $slug)->exists()) {
                $slug = Str::slug($doc['title']) . '-' . $counter;
                $counter++;
            }

            Documentation::create([
                'title' => $doc['title'],
                'content' => $doc['content'],
                'category' => $doc['category'],
                'tags' => $doc['tags'],
                'meta_description' => $doc['meta_description'],
                'order' => $doc['order'],
                'is_active' => $doc['is_active'],
                'slug' => $slug,
            ]);
        }
    }
} 