@extends('layouts.admin')

@section('title', 'Interactúa con ARGi')

@section('styles')
<style>
    /* Estética ARGi - Modern Chat Interface */
    :root {
        --argi-blue: #007bff;
        --argi-bg: #f4f6f9;
        --user-bubble: #007bff;
        --bot-bubble: #ffffff;
    }

    .chat-card {
        border-radius: 20px;
        border: none;
        box-shadow: 0 10px 30px rgba(0,0,0,0.08) !important;
        overflow: hidden;
    }

    #chatContainer {
        background-color: var(--argi-bg);
        background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23007bff' fill-opacity='0.03'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        scroll-behavior: smooth;
    }

    .chat-message {
        margin-bottom: 20px;
        display: flex;
        flex-direction: column;
    }

    .message-bubble {
        max-width: 80%;
        padding: 12px 18px;
        border-radius: 18px;
        position: relative;
        font-size: 0.95rem;
        line-height: 1.5;
        box-shadow: 0 2px 5px rgba(0,0,0,0.05);
    }

    .chat-message.user {
        align-items: flex-end;
    }

    .chat-message.user .message-bubble {
        background: var(--user-bubble);
        color: white;
        border-bottom-right-radius: 4px;
    }

    .chat-message.bot {
        align-items: flex-start;
    }

    .chat-message.bot .message-bubble {
        background: var(--bot-bubble);
        color: #333;
        border-bottom-left-radius: 4px;
        border: 1px solid #e9ecef;
    }

    .message-time {
        font-size: 0.7rem;
        margin-top: 5px;
        opacity: 0.7;
    }

    /* Typing Indicator */
    .typing-dots span {
        width: 8px;
        height: 8px;
        background: #999;
        border-radius: 50%;
        display: inline-block;
        animation: typing 1s infinite;
        margin: 0 2px;
    }
    @keyframes typing {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-5px); }
    }

    /* Quick Help Styling */
    .quick-question {
        display: block;
        padding: 10px 15px;
        margin-bottom: 8px;
        background: #fff;
        border: 1px solid #ddd;
        border-radius: 10px;
        color: #444;
        transition: all 0.2s;
        text-decoration: none !important;
    }
    .quick-question:hover {
        background: var(--argi-blue);
        color: white !important;
        transform: translateX(5px);
    }

    .category-btn {
        border-radius: 20px;
        padding: 5px 15px;
        font-weight: 600;
        transition: 0.3s;
    }
</style>
@endsection

@section('contenido')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2 align-items-center">
            <div class="col-sm-6">
                <h1 class="m-0 fw-bold"><i class="fas fa-robot text-primary me-2"></i>¡Hola! Soy ARGi</h1>
                <p class="text-muted small">Tu asistente inteligente en Facturarg</p>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('admin') }}">Inicio</a></li>
                    <li class="breadcrumb-item active">ARGi Chat</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-8">
                <div class="card chat-card">
                    <div class="card-header bg-white border-bottom py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <h3 class="card-title fw-bold text-dark">
                                <span class="badge badge-success badge-pill mr-2">Online</span>
                                Conversación con la IA
                            </h3>
                            <button type="button" class="btn btn-sm btn-outline-danger border-0" id="clearChat">
                                <i class="fas fa-trash-alt"></i> Limpiar historial
                            </button>
                        </div>
                    </div>
                    
                    <div class="card-body" style="height: 500px; overflow-y: auto;" id="chatContainer">
                        <div class="chat-messages" id="chatMessages">
                            </div>
                        
                        <div class="category-buttons text-center mt-3" id="categoryButtons" style="display: none;">
                            <p class="small text-muted mb-2">Selecciona un módulo para comenzar:</p>
                            </div>
                    </div>

                    <div class="card-footer bg-white border-top p-3">
                        <form id="chatForm">
                            <div class="input-group">
                                <input type="text" class="form-control border-0 bg-light" id="messageInput" 
                                       placeholder="Escribe tu mensaje aquí..." autocomplete="off" 
                                       style="border-radius: 12px; padding: 12px 20px;">
                                <div class="input-group-append ms-2">
                                    <button type="submit" class="btn btn-primary px-4" id="sendButton" style="border-radius: 12px;">
                                        <i class="fas fa-paper-plane"></i>
                                    </button>
                                    <button type="button" class="btn btn-primary d-none" id="loadingButton" disabled style="border-radius: 12px;">
                                        <i class="fas fa-spinner fa-spin"></i>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card chat-card">
                    <div class="card-header bg-white">
                        <h3 class="card-title fw-bold">Acceso Rápido</h3>
                    </div>
                    <div class="card-body">
                        <p class="text-muted small mb-3">Haz clic en una pregunta para enviarla directamente:</p>
                        <div class="quick-help">
                            <a href="#" class="quick-question" data-question="¿Cómo creo una nueva venta?">
                                <i class="fas fa-plus-circle me-2"></i> Crear nueva venta
                            </a>
                            <a href="#" class="quick-question" data-question="¿Cómo gestionar el inventario?">
                                <i class="fas fa-boxes me-2"></i> Gestión de Stock
                            </a>
                            <a href="#" class="quick-question" data-question="¿Cómo agregar clientes?">
                                <i class="fas fa-users me-2"></i> Nuevo Cliente
                            </a>
                            <a href="#" class="quick-question" data-question="¿Cómo generar informes?">
                                <i class="fas fa-chart-line me-2"></i> Reportes Mensuales
                            </a>
                        </div>
                        <hr>
                        <div class="text-center">
                            <a href="{{ route('documentation.index') }}" class="btn btn-sm btn-outline-primary w-100 py-2" style="border-radius: 10px;">
                                <i class="fas fa-book me-1"></i> Ver documentación completa
                            </a>
                        </div>
                    </div>
                </div>
                
                <div class="info-box bg-gradient-info shadow-sm" style="border-radius: 15px;">
                    <span class="info-box-icon"><i class="fas fa-lightbulb"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Tip Pro</span>
                        <span class="info-box-number" style="font-size: 0.85rem; font-weight: 400;">
                            Puedes pedirle a ARGi que te explique cómo configurar tu facturación de AFIP.
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

@endsection

@section('scripts')
<script>
$(document).ready(function() {
    const sessionId = '{{ $sessionId }}';
    const chatMessages = $('#chatMessages');
    const messageInput = $('#messageInput');
    const chatForm = $('#chatForm');
    const chatContainer = $('#chatContainer');
    const sendButton = $('#sendButton');
    const loadingButton = $('#loadingButton');
    
    // Indicador de escritura mejorado
    const typingIndicator = $(`
        <div class="chat-message bot" id="typing-indicator-wrapper">
            <div class="message-bubble bg-light">
                <div class="typing-dots"><span></span><span></span><span></span></div>
            </div>
        </div>
    `);

    let selectedCategory = null;

    loadWelcomeMessage();
    loadChatHistory();

    chatForm.on('submit', function(e) {
        e.preventDefault();
        const message = messageInput.val().trim();
        if (!message) return;

        // UI State
        sendButton.addClass('d-none');
        loadingButton.removeClass('d-none');
        addMessage(message, 'user');
        messageInput.val('');

        // Bot thinking...
        chatMessages.append(typingIndicator);
        scrollToBottom();

        $.ajax({
            url: '{{ route("chatbot.send") }}',
            method: 'POST',
            data: {
                message: message,
                session_id: sessionId,
                category: selectedCategory,
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                $('#typing-indicator-wrapper').remove();
                addMessage(response.response, 'bot', response.timestamp);
                
                sendButton.removeClass('d-none');
                loadingButton.addClass('d-none');
            },
            error: function() {
                $('#typing-indicator-wrapper').remove();
                addMessage('Lo siento, tuve un problema al procesar tu mensaje. ¿Podrías intentar de nuevo?', 'bot');
                sendButton.removeClass('d-none');
                loadingButton.addClass('d-none');
            }
        });
    });

    $('.quick-question').on('click', function(e) {
        e.preventDefault();
        messageInput.val($(this).data('question'));
        chatForm.submit();
    });

    $('#clearChat').on('click', function() {
        if (confirm('¿Estás seguro de que deseas borrar toda la conversación?')) {
            $.ajax({
                url: '{{ route("chatbot.clear") }}',
                method: 'POST',
                data: { session_id: sessionId, _token: '{{ csrf_token() }}' },
                success: function() {
                    chatMessages.empty();
                    $('#categoryButtons').hide();
                    selectedCategory = null;
                    loadWelcomeMessage();
                }
            });
        }
    });

    function addMessage(message, sender, timestamp = null) {
        const time = timestamp || new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
        const messageHtml = `
            <div class="chat-message ${sender}">
                <div class="message-bubble">
                    ${message}
                    <div class="message-time">${time}</div>
                </div>
            </div>
        `;
        chatMessages.append(messageHtml);
        scrollToBottom();
    }

    function loadWelcomeMessage() {
        $.ajax({
            url: '{{ route("chatbot.welcome") }}',
            method: 'GET',
            success: function(data) {
                addMessage(data.welcome_message, 'bot');
                if(data.categories) createCategoryButtons(data.categories);
            }
        });
    }

    function createCategoryButtons(categories) {
        const container = $('#categoryButtons');
        container.empty();
        categories.forEach(cat => {
            container.append(`
                <button class="btn btn-outline-primary btn-sm m-1 category-btn" data-category="${cat}">
                    ${cat}
                </button>
            `);
        });
        container.fadeIn();

        $('.category-btn').on('click', function() {
            selectedCategory = $(this).data('category');
            const msg = `Cuéntame sobre el módulo de ${selectedCategory}`;
            addMessage(msg, 'user');
            messageInput.val(msg);
            chatForm.submit();
            container.fadeOut();
        });
    }

    function loadChatHistory() {
        $.ajax({
            url: '{{ route("chatbot.history") }}',
            method: 'GET',
            data: { session_id: sessionId },
            success: function(messages) {
                if (messages.length > 0) {
                    chatMessages.empty();
                    messages.forEach(msg => {
                        if (msg.message) addMessage(msg.message, 'user', formatTime(msg.created_at));
                        if (msg.response) addMessage(msg.response, 'bot', formatTime(msg.created_at));
                    });
                    scrollToBottom();
                }
            }
        });
    }

    function formatTime(dateStr) {
        return new Date(dateStr).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    }

    function scrollToBottom() {
        chatContainer.animate({ scrollTop: chatContainer[0].scrollHeight }, 300);
    }
});
</script>
@endsection