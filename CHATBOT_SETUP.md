# AI Help Bot & Documentation Center Setup Guide

This guide will help you set up the AI-powered customer support chatbot and documentation center for your Laravel business management system.

## Features

### 🤖 AI Help Bot
- **Intelligent Responses**: Powered by OpenAI GPT-3.5-turbo (optional)
- **Documentation Integration**: Pulls responses from landlord database knowledge base
- **Fallback Responses**: Works even without API key
- **Chat History**: Persistent conversation tracking
- **Quick Questions**: Pre-defined common questions
- **Rating System**: Users can rate bot responses

### 📚 Documentation Center
- **Centralized Storage**: All documentation stored in landlord database
- **Multi-tenant Access**: All tenants access the same documentation
- **Searchable Content**: Full-text search across all documentation
- **Category Organization**: Organized by business modules
- **Rich Content**: Support for formatted text and structure
- **Admin Management**: Easy content creation and editing
- **SEO Friendly**: Meta descriptions and slugs
- **Related Articles**: Smart content recommendations

## Installation Steps

### 1. Run Migrations
```bash
php artisan migrate
```

### 2. Seed Initial Documentation
```bash
# Seed documentation in landlord database
php artisan db:seed --class=DocumentationSeeder
```

**Note**: Documentation is now stored in the landlord database to ensure all tenants have access to the same documentation content.

### 3. Configure OpenAI (Optional)
Add your OpenAI API key to your `.env` file:
```env
OPENAI_API_KEY=your_openai_api_key_here
```

If you don't have an OpenAI API key, the chatbot will still work with fallback responses.

### 4. Clear Cache
```bash
php artisan cache:clear
php artisan config:clear
```

## Usage

### Accessing the Chatbot
1. Navigate to **AI Help & Docs** → **AI Help Bot** in the sidebar
2. Start chatting with the AI assistant
3. Use quick questions for common inquiries
4. Rate responses to help improve the system

### Accessing Documentation
1. Navigate to **AI Help & Docs** → **Documentation Center**
2. Browse by category or search for specific topics
3. Click on articles to read detailed guides
4. Use the search function to find specific information

### Managing Documentation (Admin)
1. Navigate to **Manage Docs** → **All Articles**
2. Create new articles with the **Create Article** button
3. Edit existing articles as needed
4. Organize content with categories and tags

## Configuration

### Chatbot Settings
The chatbot can be configured in `app/Services/ChatbotService.php`:
- **API Model**: Change from `gpt-3.5-turbo` to other models
- **Response Length**: Adjust `max_tokens` parameter
- **Temperature**: Control response creativity (0.0-1.0)
- **Fallback Responses**: Customize keyword-based responses

### Documentation Categories
Default categories include:
- Getting Started
- Sales Management
- Inventory Management
- Customer Management
- Reports & Analytics
- User Management
- System Configuration
- Troubleshooting

You can add more categories in the admin interface.

## Customization

### Adding Custom Fallback Responses
Edit the `getFallbackResponse` method in `ChatbotService.php`:

```php
protected function getFallbackResponse($message, $context)
{
    $lowerMessage = strtolower($message);
    
    // Add your custom responses here
    if (str_contains($lowerMessage, 'your_keyword')) {
        return "Your custom response here.";
    }
    
    // Default response
    return "I understand you're asking about: '{$message}'. While I don't have specific information about this right now, I recommend checking our documentation center or contacting our support team for detailed assistance.";
}
```

### Styling Customization
The chatbot and documentation center use Bootstrap 4 classes. You can customize the appearance by:
1. Modifying the CSS in the view files
2. Adding custom styles to your main CSS file
3. Overriding Bootstrap classes

### Adding New Documentation
1. Use the admin interface to create new articles
2. Use proper categories and tags for organization
3. Include meta descriptions for better search results
4. Use markdown-style formatting in content

## API Integration

### OpenAI Integration
The system integrates with OpenAI's GPT-3.5-turbo model. To use this:

1. Get an OpenAI API key from [OpenAI Platform](https://platform.openai.com/)
2. Add the key to your `.env` file
3. The chatbot will automatically use AI responses when available

### Alternative AI Providers
To use other AI providers, modify the `generateAIResponse` method in `ChatbotService.php`:

```php
protected function generateAIResponse($message, $context)
{
    // Replace OpenAI implementation with your preferred provider
    // Example: Google AI, Azure OpenAI, Anthropic Claude, etc.
}
```

## Security Considerations

### API Key Security
- Never commit API keys to version control
- Use environment variables for sensitive data
- Rotate API keys regularly
- Monitor API usage and costs

### User Data Protection
- Chat messages are stored in the database
- Implement data retention policies
- Consider GDPR compliance for user data
- Regular data cleanup for old conversations

## Troubleshooting

### Common Issues

**Chatbot not responding:**
- Check if OpenAI API key is configured
- Verify internet connection
- Check Laravel logs for errors
- Ensure migrations are run

**Documentation not loading:**
- Verify database connection
- Check if seeder was run
- Clear application cache
- Check file permissions

**Search not working:**
- Verify database indexes
- Check search query syntax
- Clear application cache
- Check JavaScript console for errors

### Debug Mode
Enable debug mode in `.env`:
```env
APP_DEBUG=true
```

Check Laravel logs in `storage/logs/laravel.log` for detailed error information.

## Performance Optimization

### Database Optimization
- Add indexes to frequently searched columns
- Regular database maintenance
- Optimize queries for large datasets

### Caching
- Cache documentation content
- Cache search results
- Use Redis for session storage

### API Rate Limiting
- Implement rate limiting for chatbot requests
- Monitor API usage
- Set up alerts for high usage

## Support

For additional support:
1. Check the documentation center
2. Use the AI chatbot for help
3. Review Laravel logs for errors
4. Contact your development team

## License

This chatbot and documentation system is part of your business management application. Ensure compliance with your application's license terms. 