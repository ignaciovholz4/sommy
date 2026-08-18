# Testing Commands for Chatbot & Documentation System

## 1. Database & Migration Tests

### Check if tables exist in landlord database:
```bash
php artisan tinker --execute="echo 'Documentation table: ' . (Schema::hasTable('documentation') ? 'EXISTS' : 'MISSING');"
php artisan tinker --execute="echo 'Chat messages table: ' . (Schema::hasTable('chat_messages') ? 'EXISTS' : 'MISSING');"
```

### Check documentation count in landlord database:
```bash
php artisan tinker --execute="echo 'Documentation count: ' . App\Models\Documentation::count();"
```

### Check tenant database tables:
```bash
php artisan tenants:artisan "tinker --execute='echo \"Documentation table: \" . (Schema::hasTable(\"documentation\") ? \"EXISTS\" : \"MISSING\");'"
php artisan tenants:artisan "tinker --execute='echo \"Chat messages table: \" . (Schema::hasTable(\"chat_messages\") ? \"EXISTS\" : \"MISSING\");'"
```

### Check documentation count in tenant databases:
```bash
php artisan tenants:artisan "tinker --execute='echo \"Documentation count: \" . App\Models\Documentation::count();'"
```

## 2. Configuration Tests

### Test OpenAI API key configuration:
```bash
php artisan tinker --execute="echo 'OpenAI API Key: ' . (config('services.openai.api_key') ? 'CONFIGURED' : 'NOT CONFIGURED');"
```

### Test if API key is valid (without making actual API call):
```bash
php artisan tinker --execute="echo 'API Key length: ' . strlen(config('services.openai.api_key'));"
```

## 3. Service Tests

### Test ChatbotService instantiation:
```bash
php artisan tinker --execute="try { \$service = new App\Services\ChatbotService(); echo 'ChatbotService: OK'; } catch(Exception \$e) { echo 'ChatbotService: ERROR - ' . \$e->getMessage(); }"
```

### Test documentation search:
```bash
php artisan tinker --execute="\$docs = App\Models\Documentation::where('title', 'like', '%inventory%')->get(); echo 'Inventory docs found: ' . \$docs->count();"
```

## 4. Route Tests

### List all routes to check if chatbot routes exist:
```bash
php artisan route:list | grep -i chat
php artisan route:list | grep -i doc
```

### Test specific routes:
```bash
php artisan route:list --name=chatbot
php artisan route:list --name=documentation
```

## 5. Model Tests

### Test Documentation model:
```bash
php artisan tinker --execute="echo 'Documentation model: ' . (class_exists('App\Models\Documentation') ? 'EXISTS' : 'MISSING');"
```

### Test ChatMessage model:
```bash
php artisan tinker --execute="echo 'ChatMessage model: ' . (class_exists('App\Models\ChatMessage') ? 'EXISTS' : 'MISSING');"
```

### Test model relationships:
```bash
php artisan tinker --execute="try { \$doc = App\Models\Documentation::first(); echo 'Documentation model works: ' . (\$doc ? 'YES' : 'NO DATA'); } catch(Exception \$e) { echo 'Documentation model error: ' . \$e->getMessage(); }"
```

## 6. View Tests

### Check if view files exist:
```bash
ls resources/views/chatbot/
ls resources/views/documentation/
```

### Test view compilation:
```bash
php artisan view:clear
php artisan view:cache
```

## 7. JavaScript & CSS Tests

### Check if chatbot JS file exists:
```bash
ls public/js/chatbot.js
```

### Check if chatbot CSS file exists:
```bash
ls public/css/chatbot.css
```

## 8. Complete System Test

### Create a comprehensive test script:
```bash
cat > test_system.php << 'EOF'
<?php
require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== SYSTEM TEST ===\n";

// Test 1: Configuration
echo "1. OpenAI API Key: " . (config('services.openai.api_key') ? 'CONFIGURED' : 'NOT CONFIGURED') . "\n";

// Test 2: Models
echo "2. Documentation Model: " . (class_exists('App\Models\Documentation') ? 'EXISTS' : 'MISSING') . "\n";
echo "3. ChatMessage Model: " . (class_exists('App\Models\ChatMessage') ? 'EXISTS' : 'MISSING') . "\n";

// Test 3: Database
echo "4. Documentation count: " . App\Models\Documentation::count() . "\n";

// Test 4: Service
try {
    $service = new App\Services\ChatbotService();
    echo "5. ChatbotService: OK\n";
} catch(Exception $e) {
    echo "5. ChatbotService: ERROR - " . $e->getMessage() . "\n";
}

// Test 5: Documentation search
$docs = App\Models\Documentation::where('title', 'like', '%inventory%')->get();
echo "6. Inventory docs found: " . $docs->count() . "\n";

echo "=== TEST COMPLETED ===\n";
EOF

php test_system.php
```

## 9. Web Interface Tests

### Test chatbot page (replace with your domain):
```bash
curl -I http://your-domain.com/chatbot
curl -I http://your-domain.com/admin/chatbot
```

### Test documentation pages:
```bash
curl -I http://your-domain.com/documentation
curl -I http://your-domain.com/admin/documentation
```

## 10. Tenant-Specific Tests

### Test tenant database seeding:
```bash
php artisan tenants:artisan "db:seed --class=TenantDocumentationSeeder"
```

### Test tenant documentation:
```bash
php artisan tenants:artisan "tinker --execute='echo \"Tenant docs: \" . App\Models\Documentation::count();'"
```

## 11. Performance Tests

### Test response time:
```bash
time php artisan tinker --execute="\$service = new App\Services\ChatbotService(); \$response = \$service->processMessage('test message', 1, 'test-session'); echo 'Response time test completed';"
```

## 12. Error Log Tests

### Check Laravel logs for errors:
```bash
tail -n 50 storage/logs/laravel.log
```

### Check for specific chatbot errors:
```bash
grep -i "chatbot\|openai\|documentation" storage/logs/laravel.log
```

## Quick Test Sequence

Run these commands in order for a quick system check:

```bash
# 1. Check configuration
php artisan tinker --execute="echo 'OpenAI API: ' . (config('services.openai.api_key') ? 'OK' : 'FAIL');"

# 2. Check database
php artisan tinker --execute="echo 'Docs count: ' . App\Models\Documentation::count();"

# 3. Check tenant database
php artisan tenants:artisan "tinker --execute='echo \"Tenant docs: \" . App\Models\Documentation::count();'"

# 4. Check routes
php artisan route:list | grep -E "(chatbot|documentation)"

# 5. Check files
ls public/js/chatbot.js && ls public/css/chatbot.css && echo "Files: OK" || echo "Files: MISSING"
```

## Troubleshooting Commands

### If something fails, run these diagnostic commands:

```bash
# Clear all caches
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear

# Regenerate autoloader
composer dump-autoload

# Check file permissions
ls -la storage/logs/
ls -la bootstrap/cache/

# Check environment
php artisan env
``` 