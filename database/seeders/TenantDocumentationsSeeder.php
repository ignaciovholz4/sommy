<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Documentation;
use Illuminate\Support\Str;

class TenantDocumentationsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $documentation = [
            [
                'title' => 'Welcome to Your Tenant System',
                'content' => "Welcome to your dedicated business management system! This is your private tenant environment where you can manage all aspects of your business.

## Getting Started

1. **Complete your profile setup** in the configuration section
2. **Set up your company information** including logo and contact details
3. **Configure your business settings** according to your needs
4. **Invite team members** and assign appropriate roles

## Your Private Environment

- **Isolated Data**: Your data is completely separate from other tenants
- **Custom Configuration**: Set up the system according to your business needs
- **User Management**: Manage your team members and their permissions
- **Backup & Security**: Your data is automatically backed up and secured

## Quick Navigation

- **Dashboard**: Overview of your business metrics
- **Sales**: Manage transactions and customer interactions
- **Inventory**: Track products and stock levels
- **Reports**: Generate business insights and analytics
- **Settings**: Configure your system preferences

## Support

- Use the **AI Help Bot** for instant assistance
- Browse the **Documentation Center** for detailed guides
- Contact support for complex issues

Remember: This is your dedicated space - customize it to fit your business needs!",
                'category' => 'Getting Started',
                'tags' => ['welcome', 'tenant', 'setup', 'basics'],
                'meta_description' => 'Welcome guide for new tenants, explaining the private environment and basic setup process.',
                'order' => 1,
                'is_active' => true,
            ],
            [
                'title' => 'Managing Your Sales Process',
                'content' => "Learn how to efficiently manage your sales process in your tenant environment.

## Sales Workflow

### 1. Point of Sale (POS)
- **Quick Sales**: Process transactions rapidly at the counter
- **Customer Search**: Find existing customers or create new ones
- **Product Selection**: Search by name, code, or scan barcode
- **Payment Processing**: Accept multiple payment methods

### 2. Sales Management
- **View All Sales**: Complete history of transactions
- **Filter & Search**: Find specific sales by date, customer, or product
- **Edit Sales**: Modify details if needed (with proper permissions)
- **Generate Receipts**: Print or email receipts to customers

### 3. Customer Management
- **Customer Database**: Store customer information and preferences
- **Purchase History**: Track customer buying patterns
- **Loyalty Programs**: Implement customer retention strategies

## Best Practices

- Always verify product availability before completing sales
- Maintain accurate customer information
- Process returns and exchanges promptly
- Generate daily sales reports for analysis

## Troubleshooting

**Common Issues:**
- Product not found: Check inventory and product codes
- Payment declined: Verify payment method and amounts
- Customer not found: Create new customer profile

**Solutions:**
- Use the search function effectively
- Double-check all information before confirming
- Contact support for technical issues",
                'category' => 'Sales Management',
                'tags' => ['sales', 'pos', 'transactions', 'customers', 'payments'],
                'meta_description' => 'Complete guide to managing sales processes, including POS operations, customer management, and best practices.',
                'order' => 1,
                'is_active' => true,
            ],
            [
                'title' => 'Inventory Management for Your Business',
                'content' => "Effective inventory management is essential for business success. Learn how to manage your product inventory efficiently.

## Product Management

### Adding New Products
1. Navigate to **Inventory > Products**
2. Click **Add New Product**
3. Fill in product details:
   - Name and description
   - Category and subcategory
   - Pricing information
   - Stock levels
   - Product images

### Stock Management
- **Stock Levels**: Monitor current inventory levels
- **Low Stock Alerts**: Get notified when products are running low
- **Stock Adjustments**: Correct inventory discrepancies
- **Stock Transfers**: Move products between locations

### Categories and Organization
- **Product Categories**: Organize products logically
- **Subcategories**: Create detailed product hierarchies
- **Tags and Attributes**: Add searchable product features
- **Bulk Operations**: Manage multiple products at once

## Inventory Best Practices

1. **Regular Audits**: Conduct periodic inventory counts
2. **Automated Alerts**: Set up low stock notifications
3. **Supplier Management**: Track supplier information and lead times
4. **Cost Tracking**: Monitor product costs and margins

## Reports and Analytics

- **Stock Reports**: View current inventory levels
- **Movement Reports**: Track product movement over time
- **Value Reports**: Calculate inventory value
- **Trend Analysis**: Identify popular and slow-moving products

## Troubleshooting

**Common Issues:**
- Stock discrepancies: Conduct regular audits
- Missing products: Check product status and visibility
- Duplicate products: Use search to avoid duplicates

**Solutions:**
- Use barcode scanning for accuracy
- Implement proper receiving procedures
- Train staff on inventory procedures",
                'category' => 'Inventory Management',
                'tags' => ['inventory', 'products', 'stock', 'categories', 'suppliers'],
                'meta_description' => 'Comprehensive guide to inventory management, including product setup, stock control, and best practices.',
                'order' => 1,
                'is_active' => true,
            ],
            [
                'title' => 'Customer Relationship Management',
                'content' => "Build strong relationships with your customers through effective CRM practices in your tenant system.

## Customer Database Management

### Adding New Customers
1. Go to **Sales > Customers**
2. Click **Add New Customer**
3. Enter customer information:
   - Personal details (name, email, phone)
   - Address information
   - Preferences and notes
   - Marketing consent

### Customer Profiles
- **Complete History**: View all customer interactions
- **Purchase Patterns**: Analyze buying behavior
- **Preferences**: Store customer likes and dislikes
- **Communication History**: Track all customer communications

## Customer Service

### Handling Inquiries
- **Quick Response**: Respond to customer questions promptly
- **Issue Resolution**: Track and resolve customer problems
- **Follow-up**: Ensure customer satisfaction
- **Feedback Collection**: Gather customer opinions

### Loyalty Programs
- **Rewards System**: Implement customer loyalty programs
- **Special Offers**: Create targeted promotions
- **Birthday Reminders**: Send personalized greetings
- **VIP Treatment**: Recognize high-value customers

## Communication Tools

- **Email Marketing**: Send newsletters and promotions
- **SMS Notifications**: Alert customers about orders and offers
- **Social Media Integration**: Connect with customers online
- **Customer Portal**: Allow customers to view their history

## Best Practices

1. **Data Accuracy**: Keep customer information up-to-date
2. **Privacy Compliance**: Follow data protection regulations
3. **Personalization**: Use customer data for personalized service
4. **Regular Communication**: Stay in touch with customers

## Analytics and Reporting

- **Customer Lifetime Value**: Calculate customer profitability
- **Retention Rates**: Monitor customer loyalty
- **Satisfaction Scores**: Track customer satisfaction
- **Growth Metrics**: Measure customer base expansion",
                'category' => 'Customer Management',
                'tags' => ['customers', 'crm', 'loyalty', 'communication', 'service'],
                'meta_description' => 'Guide to customer relationship management, including database management, service practices, and loyalty programs.',
                'order' => 1,
                'is_active' => true,
            ],
            [
                'title' => 'Financial Management and Reporting',
                'content' => "Manage your business finances effectively with comprehensive reporting and financial tools.

## Financial Overview

### Daily Operations
- **Cash Management**: Track daily cash flow
- **Bank Reconciliation**: Match transactions with bank statements
- **Expense Tracking**: Monitor business expenses
- **Revenue Analysis**: Analyze income sources

### Financial Reports

#### Sales Reports
- **Daily Sales**: Track daily revenue
- **Product Performance**: Identify best-selling items
- **Customer Analysis**: Understand customer spending patterns
- **Seasonal Trends**: Identify peak business periods

#### Profitability Analysis
- **Gross Profit**: Calculate product profitability
- **Net Profit**: Determine overall business profitability
- **Margin Analysis**: Monitor profit margins
- **Cost Analysis**: Track operational costs

## Cash Flow Management

### Receivables
- **Outstanding Invoices**: Track unpaid customer invoices
- **Payment Terms**: Monitor payment deadlines
- **Collection Process**: Follow up on overdue payments
- **Credit Management**: Assess customer creditworthiness

### Payables
- **Supplier Payments**: Track payments to suppliers
- **Payment Terms**: Monitor payment deadlines
- **Expense Categories**: Categorize business expenses
- **Budget Management**: Control spending within budgets

## Tax Management

- **Sales Tax**: Calculate and remit sales taxes
- **Income Tax**: Prepare for tax filing
- **Tax Reports**: Generate tax-related reports
- **Compliance**: Ensure tax law compliance

## Financial Best Practices

1. **Regular Reconciliation**: Reconcile accounts monthly
2. **Backup Documentation**: Keep all financial records
3. **Budget Planning**: Create and follow budgets
4. **Professional Advice**: Consult with accountants when needed

## Reporting Tools

- **Real-time Dashboards**: Monitor key metrics
- **Custom Reports**: Create specific financial reports
- **Export Options**: Download reports in various formats
- **Scheduled Reports**: Automate regular reporting

## Troubleshooting

**Common Issues:**
- Discrepancies in reports: Check data accuracy
- Missing transactions: Verify all entries are recorded
- Tax calculation errors: Review tax settings

**Solutions:**
- Regular data validation
- Proper training for staff
- Regular system maintenance",
                'category' => 'Financial Management',
                'tags' => ['finance', 'reports', 'cash-flow', 'taxes', 'profitability'],
                'meta_description' => 'Comprehensive guide to financial management, including reporting, cash flow, and best practices.',
                'order' => 1,
                'is_active' => true,
            ],
            [
                'title' => 'User Management and Security',
                'content' => "Manage your team members and ensure system security in your tenant environment.

## User Management

### Adding New Users
1. Navigate to **Admin > Users**
2. Click **Add New User**
3. Enter user information:
   - Name and email
   - Role assignment
   - Department or team
   - Contact information

### Role-Based Access Control
- **Admin Role**: Full system access and configuration
- **Manager Role**: Department oversight and reporting
- **Sales Role**: Sales and customer management
- **Inventory Role**: Product and stock management
- **Cashier Role**: Basic sales operations

### User Permissions
- **Module Access**: Control which modules users can access
- **Function Permissions**: Set specific function permissions
- **Data Access**: Limit access to sensitive information
- **Report Access**: Control reporting capabilities

## Security Best Practices

### Password Management
- **Strong Passwords**: Enforce password complexity
- **Regular Updates**: Require periodic password changes
- **Two-Factor Authentication**: Enable 2FA for sensitive accounts
- **Session Management**: Monitor active sessions

### Data Protection
- **Access Logs**: Track all system access
- **Data Encryption**: Protect sensitive information
- **Backup Security**: Secure backup procedures
- **Audit Trails**: Maintain activity logs

## System Configuration

### General Settings
- **Company Information**: Set up business details
- **System Preferences**: Configure default settings
- **Notification Settings**: Set up alerts and notifications
- **Integration Settings**: Configure third-party integrations

### Security Settings
- **Session Timeout**: Set automatic logout times
- **IP Restrictions**: Limit access to specific IP addresses
- **Failed Login Attempts**: Set lockout policies
- **Data Retention**: Configure data retention policies

## Training and Support

### User Training
- **New User Onboarding**: Comprehensive training for new staff
- **Role-Specific Training**: Tailored training for different roles
- **Ongoing Education**: Regular training updates
- **Documentation Access**: Provide access to help resources

### Support Resources
- **AI Help Bot**: Instant assistance for common questions
- **Documentation Center**: Comprehensive guides and tutorials
- **Video Tutorials**: Step-by-step video guides
- **Support Team**: Direct access to technical support

## Monitoring and Maintenance

### System Monitoring
- **Performance Tracking**: Monitor system performance
- **Usage Analytics**: Track feature usage
- **Error Logging**: Monitor system errors
- **Capacity Planning**: Plan for system growth

### Regular Maintenance
- **Data Cleanup**: Regular data maintenance
- **System Updates**: Keep system current
- **Security Audits**: Regular security reviews
- **Backup Verification**: Ensure backup integrity",
                'category' => 'User Management',
                'tags' => ['users', 'security', 'roles', 'permissions', 'training'],
                'meta_description' => 'Guide to user management and security, including role-based access, security practices, and system configuration.',
                'order' => 1,
                'is_active' => true,
            ],
            [
                'title' => 'Troubleshooting Common Issues',
                'content' => "Quick solutions for common problems you might encounter while using the system.

## Login and Access Issues

### Can't Log In
**Problem**: Unable to access the system
**Solutions**:
1. Check username and password
2. Verify account is active
3. Clear browser cache and cookies
4. Try different browser
5. Contact administrator if locked out

### Forgotten Password
**Problem**: Can't remember password
**Solutions**:
1. Use password reset function
2. Check email for reset link
3. Contact administrator for manual reset
4. Update password after reset

## Sales and Transaction Issues

### Product Not Found
**Problem**: Can't find product in system
**Solutions**:
1. Check product spelling and codes
2. Verify product is active in inventory
3. Check category filters
4. Search by different criteria
5. Contact inventory manager

### Payment Processing Errors
**Problem**: Payment methods not working
**Solutions**:
1. Verify payment method settings
2. Check network connection
3. Verify transaction amounts
4. Try alternative payment method
5. Contact payment processor support

## Inventory Problems

### Stock Discrepancies
**Problem**: Inventory counts don't match
**Solutions**:
1. Conduct physical inventory count
2. Check for pending transactions
3. Review recent stock adjustments
4. Verify receiving procedures
5. Update inventory records

### Product Information Errors
**Problem**: Incorrect product details
**Solutions**:
1. Verify product information
2. Update product details
3. Check category assignments
4. Review pricing information
5. Contact product manager

## System Performance

### Slow System Response
**Problem**: System is running slowly
**Solutions**:
1. Check internet connection
2. Clear browser cache
3. Close unnecessary browser tabs
4. Restart browser
5. Contact technical support

### Data Not Loading
**Problem**: Reports or data not displaying
**Solutions**:
1. Refresh the page
2. Check date ranges
3. Verify filter settings
4. Clear browser cache
5. Try different browser

## Customer Service Issues

### Customer Data Problems
**Problem**: Customer information incorrect
**Solutions**:
1. Verify customer details
2. Update customer information
3. Check for duplicate records
4. Merge duplicate accounts
5. Contact customer service

### Communication Problems
**Problem**: Can't send emails or notifications
**Solutions**:
1. Check email settings
2. Verify customer email addresses
3. Check spam filters
4. Test email functionality
5. Contact technical support

## Getting Help

### When to Contact Support
- System errors or crashes
- Data loss or corruption
- Security concerns
- Performance issues
- Feature requests

### How to Contact Support
1. **AI Help Bot**: Use for quick questions
2. **Documentation**: Check help articles
3. **Email Support**: Send detailed issue description
4. **Phone Support**: For urgent issues
5. **Live Chat**: For immediate assistance

### Information to Provide
- Detailed problem description
- Steps to reproduce the issue
- Error messages or screenshots
- System information
- User account details

## Prevention Tips

### Regular Maintenance
- Keep system updated
- Regular data backups
- Monitor system performance
- Train staff regularly
- Review security settings

### Best Practices
- Follow proper procedures
- Double-check important data
- Keep documentation current
- Report issues promptly
- Maintain good communication",
                'category' => 'Troubleshooting',
                'tags' => ['help', 'support', 'problems', 'solutions', 'maintenance'],
                'meta_description' => 'Comprehensive troubleshooting guide for common system issues, including solutions and prevention tips.',
                'order' => 1,
                'is_active' => true,
            ],
            [
                'title' => 'AI Help Bot User Guide',
                'content' => "Learn how to effectively use the AI Help Bot to get instant assistance with your system questions.

## What is the AI Help Bot?

The AI Help Bot is your intelligent assistant that provides instant help and guidance for using the system. It can answer questions, provide step-by-step instructions, and help you troubleshoot issues.

## How to Use the AI Help Bot

### Accessing the Bot
1. Navigate to **AI Help & Docs** in the main menu
2. Click on **AI Help Bot**
3. The chat interface will open in a new window

### Starting a Conversation
1. Type your question in the message box
2. Press Enter or click the send button
3. The bot will respond with helpful information
4. Continue the conversation as needed

### Types of Questions You Can Ask

#### General Questions
- \"How do I create a new sale?\"
- \"What are the main features of the system?\"
- \"How do I add a new customer?\"

#### Specific Procedures
- \"How do I process a return?\"
- \"How do I generate a sales report?\"
- \"How do I update product information?\"

#### Troubleshooting
- \"I can't find a product in the system\"
- \"My payment method isn't working\"
- \"I'm getting an error message\"

#### System Information
- \"What are the system requirements?\"
- \"How do I change my password?\"
- \"How do I set up notifications?\"

## Best Practices for Using the Bot

### Be Specific
- Ask specific questions rather than general ones
- Include relevant details in your questions
- Mention the specific module or feature you're working with

### Provide Context
- Explain what you're trying to accomplish
- Mention any error messages you're seeing
- Describe the steps you've already tried

### Follow Up
- Ask for clarification if the answer isn't clear
- Request additional details if needed
- Ask for step-by-step instructions

## Bot Features

### Quick Questions
The bot provides quick access to common questions:
- **Getting Started**: Basic setup and navigation
- **Sales Help**: Sales-related questions and procedures
- **Inventory Questions**: Product and stock management
- **Customer Support**: Customer management issues
- **Technical Issues**: System problems and solutions

### Chat History
- View your previous conversations
- Access past solutions and instructions
- Continue previous discussions

### Rating System
- Rate the helpfulness of bot responses
- Provide feedback to improve the service
- Help others by rating good answers

## When to Use the Bot vs. Other Support

### Use the AI Help Bot for:
- Quick questions and clarifications
- Step-by-step instructions
- Basic troubleshooting
- System navigation help
- Feature explanations

### Contact Human Support for:
- Complex technical issues
- Data recovery problems
- Security concerns
- Feature requests
- Account management issues

## Tips for Better Results

### Clear Communication
- Use simple, clear language
- Avoid technical jargon unless necessary
- Be patient and wait for responses

### Systematic Approach
- Ask one question at a time
- Follow the bot's suggestions
- Try the solutions before asking for more help

### Learning from Responses
- Pay attention to the bot's explanations
- Save useful information for future reference
- Use the knowledge gained to help others

## Privacy and Security

### Data Protection
- Your conversations are private
- Sensitive information is protected
- Chat history is stored securely

### Safe Usage
- Don't share sensitive account information
- Use the bot for legitimate questions only
- Report any inappropriate responses

## Getting the Most Out of the Bot

### Regular Use
- Use the bot regularly to learn new features
- Ask questions as they arise
- Build your knowledge base over time

### Feedback
- Rate responses to help improve the service
- Provide constructive feedback
- Suggest new topics for the knowledge base

### Continuous Learning
- The bot learns from interactions
- New features are added regularly
- Stay updated with system changes

Remember: The AI Help Bot is designed to make your experience easier and more efficient. Don't hesitate to use it whenever you need assistance!",
                'category' => 'AI Help Bot',
                'tags' => ['ai', 'chatbot', 'help', 'support', 'assistance'],
                'meta_description' => 'Complete guide to using the AI Help Bot, including best practices, features, and tips for getting the most out of the service.',
                'order' => 1,
                'is_active' => true,
            ]
        ];

        foreach ($documentation as $doc) {
            $doc['slug'] = Str::slug($doc['title']);
            Documentation::create($doc);
        }
    }
} 