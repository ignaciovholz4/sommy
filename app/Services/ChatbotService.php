<?php

namespace App\Services;

use App\Models\Documentation;
use App\Models\ChatMessage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;

class ChatbotService
{
    protected $apiKey;
    protected $apiUrl = 'https://api.openai.com/v1/chat/completions';

    public function __construct()
    {
        $this->apiKey = config('services.openai.api_key');
    }

    public function processMessage($message, $userId, $sessionId, $category = null)
    {
        // First, try to find relevant documentation
        $relevantDocs = $this->findRelevantDocumentation($message, $category);



        // Create context from documentation
        $context = $this->buildContext($relevantDocs);

        if ($relevantDocs->isEmpty()) {

           return $this->getFallbackResponse($message, $context);
         }
        // Generate AI response
        $response = $this->generateAIResponse($message, $context);

        // Save the conversation
        $this->saveMessage($userId, $message, $response, $sessionId, $relevantDocs->first());

        return $response;
    }

    protected function findRelevantDocumentation($message, $category = null)
    {
        $searchTerms = $this->extractSearchTerms($message);

        $query = Documentation::active();

        // If category is specified, prioritize docs from that category
        if ($category) {
            $query->where('category', $category);
        }

        if (count($searchTerms) > 0) {
            $query->where(function($q) use ($searchTerms) {
                $q->where(function($subQuery) use ($searchTerms) {
                    foreach ($searchTerms as $term) {
                        $subQuery->orWhere('title', 'like', "%{$term}%")
                                 ->orWhere('content', 'like', "%{$term}%")
                                 ->orWhere('tags', 'like', "%{$term}%");
                    }
                });
            });
        }

        // If no category specified, search all docs
        if (!$category) {
            $query->orderBy('order');
        }

        return $query->limit(1)->get();
    }

    protected function extractSearchTerms($message)
    {
        // Simple keyword extraction - can be enhanced with NLP
        $words = explode(' ', strtolower($message));
        $stopWords = ['the', 'a', 'an', 'and', 'or', 'but', 'in', 'on', 'at', 'to', 'for', 'of', 'with', 'by', 'is', 'are', 'was', 'were', 'be', 'been', 'have', 'has', 'had', 'do', 'does', 'did', 'will', 'would', 'could', 'should', 'may', 'might', 'can', 'this', 'that', 'these', 'those', 'i', 'you', 'he', 'she', 'it', 'we', 'they', 'me', 'him', 'her', 'us', 'them'];
        
        return array_filter($words, function($word) use ($stopWords) {
            return !in_array($word, $stopWords) && strlen($word) > 2;
        });
    }

    protected function buildContext($documentation)
    {
        if ($documentation->isEmpty()) {
            return "No specific documentation found for this query.";
        }

        $context = "Based on the available documentation:\n\n";
        
        foreach ($documentation as $doc) {
            $context .= "Title: {$doc->title}\n";
            $context .= "Category: {$doc->category}\n";
            $context .= "Content: {$doc->content}\n\n";
        }
        
        return $context;
    }

    protected function generateAIResponse($message, $context)
    {
        if (!$this->apiKey) {

            return $this->getFallbackResponse($message, $context);
        }

//        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post($this->apiUrl, [
                'model' => 'gpt-3.5-turbo',
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => "You are an expert customer support assistant for Facturarg, a comprehensive business management system. Your role is to:

1. **Provide accurate, helpful responses** based on the provided documentation context
2. **Give step-by-step instructions** when explaining processes
3. **Include troubleshooting tips** when relevant
4. **Suggest related features** that might be helpful
5. **Maintain a professional, friendly tone**
6. **Ask clarifying questions** when user requests are ambiguous
7. **Provide actionable next steps** in your responses

When responding:
- Use clear, simple language
- Break complex processes into numbered steps
- Include specific menu paths when possible
- Mention any prerequisites or requirements
- Suggest related documentation when helpful
- If you don't have specific information, guide users to the documentation center or support team

Always prioritize accuracy and helpfulness over brevity. Focus on providing practical, actionable advice that helps users accomplish their goals."
                    ],
                    [
                        'role' => 'user',
                        'content' => "Context: {$context}\n\nUser Question: {$message}"
                    ]
                ],
                'max_tokens' => 500,
                'temperature' => 0.7,
            ]);

            if ($response->successful()) {
                $data = $response->json();

                return $data['choices'][0]['message']['content'] ?? $this->getFallbackResponse($message, $context);
            }
//        } catch (\Exception $e) {
//            // Log error if needed
//        }

        return $this->getFallbackResponse($message, $context);
    }

    protected function getFallbackResponse($message, $context)
    {
       $lowerMessage = strtolower($message);
//
//        // Enhanced keyword-based responses with more specific guidance
    //    if (str_contains($lowerMessage, 'help') || str_contains($lowerMessage, 'support')) {
    //        return "I'm here to help! I can assist you with:\n\n• **Inventory Management**: Adding products, tracking stock, managing categories\n• **Sales & Billing**: Creating sales, generating invoices, processing payments\n• **Customer Management**: Adding customers, viewing history, managing relationships\n• **Reports & Analytics**: Generating reports, viewing business insights\n• **System Configuration**: Settings, user management, preferences\n\nWhat specific area would you like help with? You can also visit our documentation center for detailed guides.";
    //    }
//
//        if (str_contains($lowerMessage, 'sale') || str_contains($lowerMessage, 'venta')) {
//            return "**Sales Module Guide**:\n\n1. **Create a Sale**: Navigate to Sales → New Sale\n2. **Add Products**: Select products from inventory\n3. **Process Payment**: Choose payment method and complete transaction\n4. **Generate Invoice**: Automatically create and send invoices\n5. **View History**: Access Sales → Sales History for past transactions\n\nWould you like me to explain any of these steps in detail?";
//        }
//
//        if (str_contains($lowerMessage, 'inventory') || str_contains($lowerMessage, 'inventario')) {
//            return "**Inventory Management Guide**:\n\n1. **Add Products**: Go to Inventory → Products → Add New\n2. **Manage Stock**: Update quantities, set reorder levels\n3. **Categories**: Organize products by categories\n4. **Variants**: Create product variations (size, color, etc.)\n5. **Reports**: Generate inventory reports and analytics\n\nWhat specific inventory task do you need help with?";
//        }
//
//        if (str_contains($lowerMessage, 'customer') || str_contains($lowerMessage, 'cliente')) {
//            return "**Customer Management Guide**:\n\n1. **Add Customers**: Go to Customers → Add New Customer\n2. **Customer Information**: Store contact details, preferences\n3. **Purchase History**: View customer transaction history\n4. **Customer Groups**: Organize customers by categories\n5. **Credit Management**: Handle customer credit and payment terms\n\nHow can I help you with customer management?";
//        }
//
//        if (str_contains($lowerMessage, 'report') || str_contains($lowerMessage, 'reporte')) {
//            return "**Available Reports**:\n\n• **Sales Reports**: Daily, weekly, monthly sales summaries\n• **Inventory Reports**: Stock levels, movement, valuation\n• **Customer Reports**: Customer analysis, purchase patterns\n• **Financial Reports**: Revenue, expenses, profit margins\n• **Analytics**: Business insights and performance metrics\n\nWhich type of report would you like to generate?";
//        }
//
//        if (str_contains($lowerMessage, 'product') || str_contains($lowerMessage, 'producto')) {
//            return "**Product Management**:\n\n1. **Add New Product**: Inventory → Products → Add New\n2. **Product Information**: Name, description, pricing, images\n3. **Categories**: Organize products by type\n4. **Variants**: Create product variations\n5. **Stock Management**: Track quantities and reorder levels\n\nWhat specific product task do you need help with?";
//        }
//
//        if (str_contains($lowerMessage, 'invoice') || str_contains($lowerMessage, 'factura')) {
//            return "**Invoice Management**:\n\n1. **Create Invoice**: Sales → New Sale → Generate Invoice\n2. **Invoice Details**: Customer info, products, pricing, taxes\n3. **Send Invoice**: Email or print invoices to customers\n4. **Track Payments**: Monitor invoice status and payments\n5. **Invoice History**: View and manage past invoices\n\nWould you like help with creating or managing invoices?";
//        }
//
//        if (str_contains($lowerMessage, 'payment') || str_contains($lowerMessage, 'pago')) {
//            return "**Payment Processing**:\n\n1. **Payment Methods**: Cash, credit card, bank transfer, digital payments\n2. **Process Payment**: During sale creation or invoice payment\n3. **Payment Tracking**: Monitor payment status and history\n4. **Refunds**: Handle customer refunds and returns\n5. **Payment Reports**: Generate payment summaries and analytics\n\nWhat payment-related task do you need assistance with?";
//        }
        
        // Default response with more helpful guidance
        return "I’m sorry, I couldn’t find an exact answer to your question: '{$message}'. let me know the exact problem so I can give you the most relevant options ";
    }

    protected function saveMessage($userId, $message, $response, $sessionId, $documentation = null)
    {
        ChatMessage::create([
            'user_id' => $userId,
            'message' => $message,
            'response' => $response,
            'is_bot' => false,
            'session_id' => $sessionId,
            'documentation_id' => $documentation ? $documentation->id : null,
        ]);

        ChatMessage::create([
            'user_id' => $userId,
            'message' => '',
            'response' => $response,
            'is_bot' => true,
            'session_id' => $sessionId,
            'documentation_id' => $documentation ? $documentation->id : null,
        ]);
    }

    public function getChatHistory($userId, $sessionId = null)
    {
        $query = ChatMessage::where('user_id', $userId);
        
        if ($sessionId) {
            $query->where('session_id', $sessionId);
        }
        
        return $query->orderBy('created_at')->get();
    }

    public function rateResponse($messageId, $rating, $feedback = null)
    {
        $message = ChatMessage::find($messageId);
        if ($message && $message->is_bot) {
            $message->update([
                'rating' => $rating,
                'feedback' => $feedback
            ]);
        }
    }

    public function getWelcomeMessage($username)
    {
        $categories = Documentation::active()
            ->select('category')
            ->distinct()
            ->pluck('category')
            ->take(6);

        $welcomeMessage = "Hello, {$username} 👋\n\n";
        $welcomeMessage .= "I'm ARGi, your virtual assistant.\n";
        $welcomeMessage .= "I'm here to help you learn and master our system quickly and easily.\n\n";
        $welcomeMessage .= "Select the module you'd like to learn:\n\n";

        $categoryIcons = ['⿡', '⿢', '⿣', '⿤', '⿥', '⿦'];
        
        foreach ($categories as $index => $category) {
            $icon = $categoryIcons[$index] ?? '•';
            $welcomeMessage .= "{$icon} {$category}\n";
        }

        $welcomeMessage .= "\nBy tapping a category, you can ask specific questions about that module. What would you like to learn about?";

        return $welcomeMessage;
    }

    public function getCategories()
    {
        return Documentation::active()
            ->select('category')
            ->distinct()
            ->pluck('category');
    }
} 