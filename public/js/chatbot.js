/**
 * Chatbot JavaScript functionality with category support
 */

class Chatbot {
    constructor() {
        this.sessionId = null;
        this.isTyping = false;
        this.selectedCategory = null; // Add selected category property
        this.init();
    }

    init() {
        this.sessionId = this.generateSessionId();
        this.bindEvents();
        this.loadWelcomeMessage(); // Load welcome message and categories
    }

    generateSessionId() {
        return 'chat_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
    }

    // bindEvents() {
    //     const chatForm = document.getElementById('chatForm');
    //     const messageInput = document.getElementById('messageInput');
    //     const clearChatBtn = document.getElementById('clearChat');
    //
    //     if (chatForm) {
    //         chatForm.addEventListener('submit', (e) => this.handleSubmit(e));
    //     }
    //
    //     if (messageInput) {
    //         messageInput.addEventListener('keypress', (e) => {
    //             if (e.key === 'Enter' && !e.shiftKey) {
    //                 e.preventDefault();
    //                 this.handleSubmit(e);
    //             }
    //         });
    //     }
    //
    //     if (clearChatBtn) {
    //         clearChatBtn.addEventListener('click', () => this.clearChat());
    //     }
    //
    //     // Quick questions
    //     document.querySelectorAll('.quick-question').forEach(link => {
    //         link.addEventListener('click', (e) => {
    //             e.preventDefault();
    //             const question = link.dataset.question;
    //             messageInput.value = question;
    //             this.handleSubmit(e);
    //         });
    //     });
    //
    //     // Category buttons
    //     document.addEventListener('click', (e) => {
    //         if (e.target.classList.contains('category-btn')) {
    //             e.preventDefault();
    //             const category = e.target.dataset.category;
    //             this.selectCategory(category);
    //         }
    //     });
    // }
    //
    // async handleSubmit(e) {
    //     e.preventDefault();
    //
    //     const messageInput = document.getElementById('messageInput');
    //     const message = messageInput.value.trim();
    //
    //     if (!message || this.isTyping) return;
    //
    //     // Add user message
    //     this.addMessage(message, 'user');
    //     messageInput.value = '';
    //
    //     // Show typing indicator
    //     this.showTypingIndicator();
    //
    //     try {
    //         const response = await this.sendMessage(message);
    //         this.hideTypingIndicator();
    //         this.addMessage(response.response, 'bot', response.timestamp);
    //     } catch (error) {
    //         this.hideTypingIndicator();
    //         this.addMessage('Sorry, I encountered an error. Please try again.', 'bot');
    //         console.error('Chatbot error:', error);
    //     }
    // }
    //
    // async sendMessage(message) {
    //     const response = await fetch('/chatbot/send', {
    //         method: 'POST',
    //         headers: {
    //             'Content-Type': 'application/json',
    //             'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
    //         },
    //         body: JSON.stringify({
    //             message: message,
    //             session_id: this.sessionId,
    //             category: this.selectedCategory // Include selected category
    //         })
    //     });
    //
    //     if (!response.ok) {
    //         throw new Error('Network response was not ok');
    //     }
    //
    //     return await response.json();
    // }
    //
    // selectCategory(category) {
    //     this.selectedCategory = category;
    //     const message = `I want to learn about ${category}. What can you tell me about this module?`;
    //     const messageInput = document.getElementById('messageInput');
    //     messageInput.value = message;
    //
    //     // Hide category buttons
    //     const categoryButtons = document.getElementById('categoryButtons');
    //     if (categoryButtons) {
    //         categoryButtons.style.display = 'none';
    //     }
    //
    //     // Submit the message
    //     this.handleSubmit(new Event('submit'));
    // }

    // addMessage(message, sender, timestamp = null) {
    //     const chatMessages = document.getElementById('chatMessages');
    //     const time = timestamp || new Date().toLocaleTimeString();
    //
    //     const messageHtml = `
    //         <div class="chat-message ${sender}">
    //             <div class="message-bubble">
    //                 ${this.escapeHtml(message)}
    //                 <div class="message-time">${time}</div>
    //             </div>
    //         </div>
    //     `;
    //
    //     chatMessages.insertAdjacentHTML('beforeend', messageHtml);
    //     this.scrollToBottom();
    // }

    showTypingIndicator() {
        this.isTyping = true;
        const chatMessages = document.getElementById('chatMessages');
        const typingHtml = `
            <div class="chat-message bot" id="typing-indicator">
                <div class="message-bubble typing-indicator">
                    <div class="typing-dots">
                        <span></span>
                        <span></span>
                        <span></span>
                    </div>
                </div>
            </div>
        `;
        chatMessages.insertAdjacentHTML('beforeend', typingHtml);
        this.scrollToBottom();
    }

    hideTypingIndicator() {
        this.isTyping = false;
        const typingIndicator = document.getElementById('typing-indicator');
        if (typingIndicator) {
            typingIndicator.remove();
        }
    }

    async loadWelcomeMessage() {
        try {
            const response = await fetch('/chatbot/welcome');
            const data = await response.json();

            // Add welcome message
            this.addMessage(data.welcome_message, 'bot');

            // Create category buttons
            this.createCategoryButtons(data.categories);
        } catch (error) {
            console.error('Error loading welcome message:', error);
            this.addMessage('Hello! I\'m ARGi, your virtual assistant. How can I help you today?', 'bot');
        }
    }

    createCategoryButtons(categories) {
        const categoryButtons = document.getElementById('categoryButtons');
        if (!categoryButtons) return;

        categoryButtons.innerHTML = '';

        const categoryIcons = ['⿡', '⿢', '⿣', '⿤', '⿥', '⿦'];

        categories.slice(0, 6).forEach((category, index) => {
            const icon = categoryIcons[index] || '•';
            const button = document.createElement('button');
            button.className = 'btn btn-outline-primary btn-sm m-1 category-btn';
            button.dataset.category = category;
            button.innerHTML = `${icon} ${category}`;
            categoryButtons.appendChild(button);
        });

        categoryButtons.style.display = 'block';
    }

    async loadChatHistory() {
        try {
            const response = await fetch(`/chatbot/history?session_id=${this.sessionId}`);
            const messages = await response.json();

            const chatMessages = document.getElementById('chatMessages');
            chatMessages.innerHTML = '';

            messages.forEach(msg => {
                if (msg.message) {
                    this.addMessage(msg.message, 'user', new Date(msg.created_at).toLocaleTimeString());
                }
                if (msg.response) {
                    this.addMessage(msg.response, 'bot', new Date(msg.created_at).toLocaleTimeString());
                }
            });
        } catch (error) {
            console.error('Error loading chat history:', error);
        }
    }

    async clearChat() {
        if (!confirm('Are you sure you want to clear the chat history?')) return;

        try {
            await fetch('/chatbot/clear', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    session_id: this.sessionId
                })
            });

            const chatMessages = document.getElementById('chatMessages');
            chatMessages.innerHTML = '';
            this.sessionId = this.generateSessionId();
            this.selectedCategory = null; // Reset selected category
            this.loadWelcomeMessage(); // Reload welcome message and categories
        } catch (error) {
            console.error('Error clearing chat:', error);
        }
    }

    scrollToBottom() {
        const chatContainer = document.getElementById('chatContainer');
        if (chatContainer) {
            chatContainer.scrollTop = chatContainer.scrollHeight;
        }
    }

    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
}

// Initialize chatbot when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    if (document.getElementById('chatMessages')) {
        new Chatbot();
    }
});

// Documentation search functionality
class DocumentationSearch {
    constructor() {
        this.init();
    }

    init() {
        this.bindEvents();
    }

    bindEvents() {
        const searchInput = document.querySelector('input[name="search"]');
        const categoryFilter = document.getElementById('categoryFilter');

        if (searchInput) {
            searchInput.addEventListener('input', this.debounce(() => {
                this.performSearch();
            }, 300));
        }

        if (categoryFilter) {
            categoryFilter.addEventListener('change', () => {
                this.performSearch();
            });
        }
    }

    async performSearch() {
        const searchInput = document.querySelector('input[name="search"]');
        const categoryFilter = document.getElementById('categoryFilter');

        const searchTerm = searchInput ? searchInput.value : '';
        const category = categoryFilter ? categoryFilter.value : '';

        try {
            const response = await fetch(`/documentation/search?q=${encodeURIComponent(searchTerm)}&category=${encodeURIComponent(category)}`);
            const results = await response.json();
            this.updateResults(results);
        } catch (error) {
            console.error('Search error:', error);
        }
    }

    updateResults(results) {
        // Update the results display
        const resultsContainer = document.querySelector('.documentation-results');
        if (resultsContainer) {
            // Implementation depends on your HTML structure
            console.log('Search results:', results);
        }
    }

    debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }
}

// Initialize documentation search
document.addEventListener('DOMContentLoaded', function() {
    if (document.querySelector('input[name="search"]')) {
        new DocumentationSearch();
    }
});
