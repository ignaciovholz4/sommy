<?php

namespace App\Http\Controllers;

use App\Services\ChatbotService;
use App\Models\Documentation;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ChatbotController extends Controller
{
    protected $chatbotService;

    public function __construct(ChatbotService $chatbotService)
    {
        $this->chatbotService = $chatbotService;
    }

    public function index()
    {
        $sessionId = session('chat_session_id', Str::uuid());
        session(['chat_session_id' => $sessionId]);
        
        $chatHistory = $this->chatbotService->getChatHistory(auth()->id(), $sessionId);
        
        return view('chatbot.index', compact('chatHistory', 'sessionId'));
    }

    public function sendMessage(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:1000',
            'session_id' => 'required|string',
            'category' => 'nullable|string'
        ]);

        $response = $this->chatbotService->processMessage(
            $request->message,
            auth()->id(),
            $request->session_id,
            $request->category ?? null
        );


        return response()->json([
            'response' => $response,
            'timestamp' => now()->format('Y-m-d H:i:s')
        ]);
    }

    public function rateResponse(Request $request)
    {
        $request->validate([
            'message_id' => 'required|exists:chat_messages,id',
            'rating' => 'required|integer|between:1,5',
            'feedback' => 'nullable|string|max:500'
        ]);

        $this->chatbotService->rateResponse(
            $request->message_id,
            $request->rating,
            $request->feedback
        );

        return response()->json(['success' => true]);
    }

    public function getChatHistory(Request $request)
    {
        $sessionId = $request->get('session_id');
        $history = $this->chatbotService->getChatHistory(auth()->id(), $sessionId);
        
        return response()->json($history);
    }

    public function clearChat(Request $request)
    {
        $sessionId = $request->get('session_id');
        session(['chat_session_id' => Str::uuid()]);
        
        return response()->json(['success' => true]);
    }

    public function getWelcomeMessage()
    {
        $username = auth()->user()->name ?? 'User';
        $welcomeMessage = $this->chatbotService->getWelcomeMessage($username);
        $categories = $this->chatbotService->getCategories();
        
        return response()->json([
            'welcome_message' => $welcomeMessage,
            'categories' => $categories
        ]);
    }

    public function getCategories()
    {
        $categories = $this->chatbotService->getCategories();
        return response()->json($categories);
    }
} 