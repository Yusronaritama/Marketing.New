<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>WhatsApp Chat Dashboard</title>
    @if (app()->environment('local', 'development'))
        <script src="https://cdn.tailwindcss.com"></script>
    @endif
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .chat-container {
            height: calc(100vh - 120px);
        }
        .messages-container {
            height: calc(100vh - 300px);
            overflow-y: auto;
        }
        .chat-list {
            height: calc(100vh - 200px);
            overflow-y: auto;
        }
        .message-incoming {
            background: #e5e5ea;
            color: #000;
        }
        .message-outgoing {
            background: #007bff;
            color: #fff;
        }
        .typing-indicator {
            display: inline-block;
            padding: 10px 15px;
            background: #e5e5ea;
            border-radius: 18px;
        }
        .typing-dot {
            display: inline-block;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #666;
            margin: 0 2px;
            animation: typing 1.4s infinite ease-in-out;
        }
        .typing-dot:nth-child(1) { animation-delay: -0.32s; }
        .typing-dot:nth-child(2) { animation-delay: -0.16s; }
        @keyframes typing {
            0%, 80%, 100% { transform: scale(0.8); opacity: 0.5; }
            40% { transform: scale(1); opacity: 1; }
        }
    </style>
</head>
<body class="bg-gray-100">
    <!-- Header -->
    <header class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-4">
                <div class="flex items-center">
                    <i class="fab fa-whatsapp text-green-500 text-2xl mr-3"></i>
                    <h1 class="text-xl font-semibold text-gray-900">WhatsApp Chat Dashboard</h1>
                </div>
                <div class="flex items-center space-x-4">
                    <div class="flex items-center space-x-2">
                        <div class="w-3 h-3 rounded-full bg-green-500 animate-pulse"></div>
                        <span class="text-sm text-gray-600" id="connection-status">Connected</span>
                    </div>
                    <button id="refresh-btn" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg text-sm">
                        <i class="fas fa-sync-alt mr-2"></i>Refresh
                    </button>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-lg shadow p-4">
                <div class="flex items-center">
                    <div class="p-2 rounded-full bg-blue-100 text-blue-500 mr-3">
                        <i class="fas fa-comments"></i>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Total Chats</p>
                        <p class="text-xl font-semibold" id="total-chats">0</p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow p-4">
                <div class="flex items-center">
                    <div class="p-2 rounded-full bg-green-100 text-green-500 mr-3">
                        <i class="fas fa-users"></i>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Group Chats</p>
                        <p class="text-xl font-semibold" id="group-chats">0</p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow p-4">
                <div class="flex items-center">
                    <div class="p-2 rounded-full bg-purple-100 text-purple-500 mr-3">
                        <i class="fas fa-user"></i>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Private Chats</p>
                        <p class="text-xl font-semibold" id="private-chats">0</p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow p-4">
                <div class="flex items-center">
                    <div class="p-2 rounded-full bg-orange-100 text-orange-500 mr-3">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Today's Messages</p>
                        <p class="text-xl font-semibold" id="today-messages">0</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Search and Actions -->
        <div class="bg-white rounded-lg shadow mb-6 p-4">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between space-y-4 md:space-y-0">
                <div class="flex-1 max-w-md">
                    <div class="relative">
                        <input 
                            type="text" 
                            id="search-input" 
                            placeholder="Search messages..."
                            class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        >
                        <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                    </div>
                </div>
                <div class="flex space-x-3">
                    <button id="mark-all-read" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg text-sm">
                        <i class="fas fa-check-double mr-2"></i>Mark All Read
                    </button>
                    <button id="export-chats" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg text-sm">
                        <i class="fas fa-download mr-2"></i>Export
                    </button>
                </div>
            </div>
        </div>

        <!-- Main Chat Interface -->
        <div class="bg-white rounded-lg shadow chat-container">
            <div class="flex h-full">
                <!-- Chat List Sidebar -->
                <div class="w-1/3 border-r border-gray-200">
                    <div class="p-4 border-b border-gray-200">
                        <h2 class="text-lg font-semibold">Conversations</h2>
                    </div>
                    <div class="chat-list p-2" id="chat-list">
                        <!-- Chat items will be loaded here -->
                        <div class="text-center py-8 text-gray-500">
                            <i class="fas fa-comments text-4xl mb-2"></i>
                            <p>Loading chats...</p>
                        </div>
                    </div>
                </div>

                <!-- Chat Messages Area -->
                <div class="flex-1 flex flex-col">
                    <!-- Chat Header -->
                    <div class="p-4 border-b border-gray-200 flex items-center justify-between">
                        <div class="flex items-center">
                            <div class="w-10 h-10 bg-green-500 rounded-full flex items-center justify-center text-white font-semibold mr-3" id="chat-avatar">
                                <i class="fas fa-user"></i>
                            </div>
                            <div>
                                <h3 class="font-semibold" id="chat-name">Select a chat</h3>
                                <p class="text-sm text-gray-500" id="chat-status">Click on a conversation to start chatting</p>
                            </div>
                        </div>
                        <div class="flex space-x-2">
                            <button id="chat-info-btn" class="p-2 text-gray-500 hover:text-gray-700" title="Chat Info">
                                <i class="fas fa-info-circle"></i>
                            </button>
                            <button id="archive-chat-btn" class="p-2 text-gray-500 hover:text-gray-700" title="Archive Chat">
                                <i class="fas fa-archive"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Messages Container -->
                    <div class="flex-1 messages-container p-4 bg-gray-50" id="messages-container">
                        <div class="text-center py-12 text-gray-500">
                            <i class="fas fa-comment-dots text-4xl mb-2"></i>
                            <p>Select a conversation to view messages</p>
                        </div>
                    </div>

                    <!-- Message Input -->
                    <div class="p-4 border-t border-gray-200 bg-white">
                        <div class="flex items-center space-x-3">
                            <button class="p-2 text-gray-500 hover:text-gray-700" id="attach-btn" title="Attach File">
                                <i class="fas fa-paperclip"></i>
                            </button>
                            <div class="flex-1">
                                <input 
                                    type="text" 
                                    id="message-input" 
                                    placeholder="Type a message..."
                                    class="w-full px-4 py-2 border border-gray-300 rounded-full focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    disabled
                                >
                            </div>
                            <button 
                                id="send-btn" 
                                class="bg-green-500 hover:bg-green-600 text-white p-2 rounded-full w-10 h-10 flex items-center justify-center"
                                disabled
                            >
                                <i class="fas fa-paper-plane"></i>
                            </button>
                        </div>
                        
                        <!-- File Upload (Hidden by default) -->
                        <div id="file-upload-section" class="mt-3 p-3 border border-gray-200 rounded-lg hidden">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm font-medium">Attach File</span>
                                <button id="close-attach" class="text-gray-500 hover:text-gray-700">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                            <input type="file" id="file-input" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100" />
                            <input type="text" id="file-caption" placeholder="Add a caption..." class="w-full mt-2 px-3 py-2 border border-gray-300 rounded-lg text-sm" />
                            <button id="send-file-btn" class="w-full mt-2 bg-blue-500 hover:bg-blue-600 text-white py-2 rounded-lg text-sm">
                                Send File
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modals -->
    <!-- Chat Info Modal -->
    <div id="chat-info-modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-md mx-4">
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold">Chat Information</h3>
                    <button id="close-chat-info" class="text-gray-500 hover:text-gray-700">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Chat ID</label>
                        <p class="text-sm text-gray-900" id="info-chat-id">-</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
                        <p class="text-sm text-gray-900" id="info-chat-type">-</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Last Activity</label>
                        <p class="text-sm text-gray-900" id="info-last-activity">-</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Message Count</label>
                        <p class="text-sm text-gray-900" id="info-message-count">-</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Search Results Modal -->
    <div id="search-results-modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-2xl mx-4 max-h-96 overflow-hidden">
            <div class="p-6 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold">Search Results</h3>
                    <button id="close-search-results" class="text-gray-500 hover:text-gray-700">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
            <div class="overflow-y-auto max-h-64 p-4" id="search-results-container">
                <!-- Search results will be loaded here -->
            </div>
        </div>
    </div>

    <script>
        // Global variables
        let currentChatId = null;
        let chats = [];
        let messages = [];

        // DOM Elements
        const chatList = document.getElementById('chat-list');
        const messagesContainer = document.getElementById('messages-container');
        const messageInput = document.getElementById('message-input');
        const sendBtn = document.getElementById('send-btn');
        const chatName = document.getElementById('chat-name');
        const chatStatus = document.getElementById('chat-status');
        const chatAvatar = document.getElementById('chat-avatar');
        const searchInput = document.getElementById('search-input');
        const refreshBtn = document.getElementById('refresh-btn');
        const attachBtn = document.getElementById('attach-btn');
        const fileUploadSection = document.getElementById('file-upload-section');
        const closeAttach = document.getElementById('close-attach');
        const fileInput = document.getElementById('file-input');
        const fileCaption = document.getElementById('file-caption');
        const sendFileBtn = document.getElementById('send-file-btn');
        const chatInfoModal = document.getElementById('chat-info-modal');
        const closeChatInfo = document.getElementById('close-chat-info');
        const chatInfoBtn = document.getElementById('chat-info-btn');
        const searchResultsModal = document.getElementById('search-results-modal');
        const closeSearchResults = document.getElementById('close-search-results');
        const searchResultsContainer = document.getElementById('search-results-container');
        const markAllReadBtn = document.getElementById('mark-all-read');
        const exportChatsBtn = document.getElementById('export-chats');
        const archiveChatBtn = document.getElementById('archive-chat-btn');

        // Initialize the dashboard
        document.addEventListener('DOMContentLoaded', function() {
            loadStats();
            loadChats();
            setupEventListeners();
            startAutoRefresh();
        });

        // Event Listeners
        function setupEventListeners() {
            // Message sending
            messageInput.addEventListener('input', toggleSendButton);
            messageInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    sendMessage();
                }
            });
            sendBtn.addEventListener('click', sendMessage);

            // File attachment
            attachBtn.addEventListener('click', toggleFileUpload);
            closeAttach.addEventListener('click', toggleFileUpload);
            sendFileBtn.addEventListener('click', sendFile);

            // Search
            searchInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    searchMessages();
                }
            });

            // Refresh
            refreshBtn.addEventListener('click', function() {
                loadStats();
                loadChats();
                if (currentChatId) {
                    loadChatHistory(currentChatId);
                }
            });

            // Modals
            chatInfoBtn.addEventListener('click', showChatInfo);
            closeChatInfo.addEventListener('click', function() {
                chatInfoModal.classList.add('hidden');
            });

            closeSearchResults.addEventListener('click', function() {
                searchResultsModal.classList.add('hidden');
            });

            // Actions
            markAllReadBtn.addEventListener('click', markAllAsRead);
            exportChatsBtn.addEventListener('click', exportChats);
            archiveChatBtn.addEventListener('click', archiveCurrentChat);

            // Close modals when clicking outside
            document.addEventListener('click', function(e) {
                if (e.target === chatInfoModal) {
                    chatInfoModal.classList.add('hidden');
                }
                if (e.target === searchResultsModal) {
                    searchResultsModal.classList.add('hidden');
                }
            });
        }

        // API Functions
        async function loadStats() {
            try {
                const response = await fetch('/admin/whatsapp/api/chats/stats');
                const data = await response.json();
                
                if (data.success) {
                    document.getElementById('total-chats').textContent = data.data.total_chats;
                    document.getElementById('group-chats').textContent = data.data.group_chats;
                    document.getElementById('private-chats').textContent = data.data.private_chats;
                    document.getElementById('today-messages').textContent = data.data.today_messages;
                }
            } catch (error) {
                console.error('Error loading stats:', error);
            }
        }

        async function loadChats() {
            try {
                chatList.innerHTML = '<div class="text-center py-8 text-gray-500"><i class="fas fa-spinner fa-spin text-2xl mb-2"></i><p>Loading chats...</p></div>';
                
                const response = await fetch('/admin/whatsapp/api/chats');
                const data = await response.json();
                
                if (data.success) {
                    chats = data.data.chats || [];
                    renderChatList();
                } else {
                    throw new Error(data.message);
                }
            } catch (error) {
                console.error('Error loading chats:', error);
                chatList.innerHTML = '<div class="text-center py-8 text-gray-500"><i class="fas fa-exclamation-triangle text-2xl mb-2"></i><p>Error loading chats</p></div>';
            }
        }

        async function loadChatHistory(chatId) {
            try {
                messagesContainer.innerHTML = '<div class="text-center py-8 text-gray-500"><i class="fas fa-spinner fa-spin text-2xl mb-2"></i><p>Loading messages...</p></div>';
                
                const response = await fetch(`/admin/whatsapp/api/chats/${chatId}/messages?limit=100`);
                const data = await response.json();
                
                if (data.success) {
                    messages = data.data.messages || [];
                    renderMessages();
                    scrollToBottom();
                }
            } catch (error) {
                console.error('Error loading chat history:', error);
                messagesContainer.innerHTML = '<div class="text-center py-8 text-gray-500"><i class="fas fa-exclamation-triangle text-2xl mb-2"></i><p>Error loading messages</p></div>';
            }
        }

        // Di file Blade, update function sendMessage
async function sendMessage() {
    const message = messageInput.value.trim();
    if (!message || !currentChatId) return;

    console.log('📤 Sending message:', { chatId: currentChatId, message: message });

    // Add message to UI immediately
    addMessageToUI({
        id: 'temp-' + Date.now(),
        message: message,
        sender: 'bot',
        is_from_me: true,
        timestamp: new Date().toISOString(),
        direction: 'outgoing'
    });

    messageInput.value = '';
    toggleSendButton();
    scrollToBottom();

    try {
        console.log('🔄 Making API request to send message...');
        
        // PERBAIKAN: Dapatkan CSRF token dengan cara yang lebih aman
        function getCsrfToken() {
            // Coba dari meta tag
            const metaTag = document.querySelector('meta[name="csrf-token"]');
            if (metaTag) {
                return metaTag.getAttribute('content');
            }
            
            // Coba dari input hidden (fallback)
            const inputTag = document.querySelector('input[name="_token"]');
            if (inputTag) {
                return inputTag.value;
            }
            
            // Jika tidak ditemukan, return string kosong
            console.warn('⚠️ CSRF token not found');
            return '';
        }
        
        const csrfToken = getCsrfToken();
        console.log('🔐 CSRF Token:', csrfToken ? 'Found' : 'Not found');

        const response = await fetch('/admin/whatsapp/api/chats/send-message', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                chat_id: currentChatId,
                message: message,
                type: 'text'
            })
        });

        console.log('📨 Response status:', response.status);
        
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }
        
        const data = await response.json();
        console.log('📨 Response data:', data);
        
        if (!data.success) {
            console.error('❌ Send message failed:', data);
            showNotification(data.message || 'Failed to send message', 'error');
        } else {
            console.log('✅ Message sent successfully');
            showNotification('Message sent successfully', 'success');
            
            // Refresh messages untuk menampilkan yang asli dari server
            setTimeout(() => {
                loadChatHistory(currentChatId);
            }, 1000);
        }
    } catch (error) {
        console.error('❌ Error sending message:', error);
        showNotification('Error sending message: ' + error.message, 'error');
    }
}

// Tambahkan function untuk test connection
async function testConnection() {
    try {
        console.log('🔗 Testing connection to Node.js server...');
        const response = await fetch('/admin/whatsapp/api/test-connection');
        const data = await response.json();
        console.log('🔗 Connection test result:', data);
        return data;
    } catch (error) {
        console.error('❌ Connection test failed:', error);
        return { success: false, message: error.message };
    }
}

// Panggil test connection saat load
document.addEventListener('DOMContentLoaded', function() {
    // Test connection setelah load
    setTimeout(() => {
        testConnection().then(result => {
            if (!result.success) {
                showNotification('⚠️ Cannot connect to WhatsApp server: ' + result.message, 'error');
            }
        });
    }, 2000);
});

        async function sendFile() {
            const file = fileInput.files[0];
            const caption = fileCaption.value.trim();

            if (!file || !currentChatId) return;

            const formData = new FormData();
            formData.append('chat_id', currentChatId);
            formData.append('file', file);
            if (caption) {
                formData.append('caption', caption);
            }

            try {
                const response = await fetch('/admin/whatsapp/api/chats/send-media', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: formData
                });

                const data = await response.json();
                
                if (data.success) {
                    showNotification('File sent successfully', 'success');
                    toggleFileUpload();
                    // Refresh messages to show the sent file
                    loadChatHistory(currentChatId);
                } else {
                    showNotification('Failed to send file', 'error');
                }
            } catch (error) {
                console.error('Error sending file:', error);
                showNotification('Error sending file', 'error');
            }
        }

        async function searchMessages() {
            const query = searchInput.value.trim();
            if (!query) return;

            try {
                const response = await fetch(`/admin/whatsapp/api/chats/search?query=${encodeURIComponent(query)}`);
                const data = await response.json();
                
                if (data.success) {
                    showSearchResults(data.data.messages, query);
                }
            } catch (error) {
                console.error('Error searching messages:', error);
                showNotification('Error searching messages', 'error');
            }
        }

        async function markAllAsRead() {
            try {
                // This would typically mark all chats as read
                // For now, we'll just show a notification
                showNotification('All chats marked as read', 'success');
                
                // Update UI to remove unread counts
                document.querySelectorAll('.unread-count').forEach(el => {
                    el.remove();
                });
                document.querySelectorAll('.font-semibold').forEach(el => {
                    el.classList.remove('font-semibold');
                    el.classList.add('font-normal');
                });
            } catch (error) {
                console.error('Error marking all as read:', error);
                showNotification('Error marking chats as read', 'error');
            }
        }

        async function exportChats() {
            try {
                // This would typically export chat data
                showNotification('Export feature coming soon', 'info');
            } catch (error) {
                console.error('Error exporting chats:', error);
                showNotification('Error exporting chats', 'error');
            }
        }

        async function archiveCurrentChat() {
            if (!currentChatId) return;

            try {
                const response = await fetch(`/admin/whatsapp/api/chats/${currentChatId}/archive`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                });

                const data = await response.json();
                
                if (data.success) {
                    showNotification('Chat archived successfully', 'success');
                    // Remove chat from list and clear current chat
                    loadChats();
                    clearCurrentChat();
                } else {
                    showNotification('Failed to archive chat', 'error');
                }
            } catch (error) {
                console.error('Error archiving chat:', error);
                showNotification('Error archiving chat', 'error');
            }
        }

        // UI Rendering Functions
        function renderChatList() {
            if (chats.length === 0) {
                chatList.innerHTML = '<div class="text-center py-8 text-gray-500"><i class="fas fa-comments text-2xl mb-2"></i><p>No chats available</p></div>';
                return;
            }

            chatList.innerHTML = chats.map(chat => `
                <div class="chat-item p-3 border-b border-gray-100 hover:bg-gray-50 cursor-pointer ${chat.id === currentChatId ? 'bg-blue-50' : ''}" 
                     onclick="selectChat('${chat.id}')">
                    <div class="flex items-center">
                        <div class="w-12 h-12 bg-green-500 rounded-full flex items-center justify-center text-white font-semibold mr-3">
                            ${chat.is_group ? '<i class="fas fa-users"></i>' : '<i class="fas fa-user"></i>'}
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex justify-between items-center">
                                <h4 class="font-semibold text-gray-900 truncate ${chat.unread_count > 0 ? 'font-semibold' : 'font-normal'}">
                                    ${chat.name || 'Unknown Chat'}
                                </h4>
                                <span class="text-xs text-gray-500">${formatTime(chat.last_message_time)}</span>
                            </div>
                            <p class="text-sm text-gray-600 truncate">${chat.last_message || 'No messages'}</p>
                        </div>
                        ${chat.unread_count > 0 ? `
                            <span class="unread-count bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center ml-2">
                                ${chat.unread_count}
                            </span>
                        ` : ''}
                    </div>
                </div>
            `).join('');
        }

        function renderMessages() {
            if (messages.length === 0) {
                messagesContainer.innerHTML = '<div class="text-center py-8 text-gray-500"><i class="fas fa-comment-dots text-2xl mb-2"></i><p>No messages yet</p><p class="text-sm">Start a conversation!</p></div>';
                return;
            }

            messagesContainer.innerHTML = messages.map(message => `
                <div class="message mb-4 ${message.is_from_me ? 'text-right' : 'text-left'}">
                    <div class="inline-block max-w-xs lg:max-w-md px-4 py-2 rounded-2xl ${message.is_from_me ? 'message-outgoing' : 'message-incoming'}">
                        <p class="text-sm">${message.message || '(Media message)'}</p>
                        <div class="text-xs opacity-70 mt-1 text-right">
                            ${formatTime(message.timestamp)}
                        </div>
                    </div>
                </div>
            `).join('');
        }

        function addMessageToUI(message) {
            const messageElement = document.createElement('div');
            messageElement.className = `message mb-4 ${message.is_from_me ? 'text-right' : 'text-left'}`;
            messageElement.innerHTML = `
                <div class="inline-block max-w-xs lg:max-w-md px-4 py-2 rounded-2xl ${message.is_from_me ? 'message-outgoing' : 'message-incoming'}">
                    <p class="text-sm">${message.message}</p>
                    <div class="text-xs opacity-70 mt-1 text-right">
                        ${formatTime(message.timestamp)}
                    </div>
                </div>
            `;
            messagesContainer.appendChild(messageElement);
        }

        function showSearchResults(results, query) {
            if (results.length === 0) {
                searchResultsContainer.innerHTML = '<div class="text-center py-8 text-gray-500"><p>No results found for "' + query + '"</p></div>';
            } else {
                searchResultsContainer.innerHTML = results.map(result => `
                    <div class="p-3 border-b border-gray-100 hover:bg-gray-50 cursor-pointer" onclick="goToMessage('${result.chat_id}', '${result.id}')">
                        <div class="flex justify-between items-start mb-1">
                            <span class="font-medium text-sm">${getChatName(result.chat_id)}</span>
                            <span class="text-xs text-gray-500">${formatTime(result.timestamp)}</span>
                        </div>
                        <p class="text-sm text-gray-600">${highlightText(result.message, query)}</p>
                    </div>
                `).join('');
            }
            searchResultsModal.classList.remove('hidden');
        }

        // Utility Functions
        function selectChat(chatId) {
            currentChatId = chatId;
            const chat = chats.find(c => c.id === chatId);
            
            if (chat) {
                chatName.textContent = chat.name || 'Unknown Chat';
                chatStatus.textContent = chat.is_group ? 'Group • ' + (chat.participants_count || 'Multiple') + ' participants' : 'Personal chat';
                chatAvatar.innerHTML = chat.is_group ? '<i class="fas fa-users"></i>' : '<i class="fas fa-user"></i>';
                
                // Enable message input
                messageInput.disabled = false;
                sendBtn.disabled = false;
                
                // Load chat history
                loadChatHistory(chatId);
                
                // Update chat list UI
                renderChatList();
                
                // Mark as read
                if (chat.unread_count > 0) {
                    markChatAsRead(chatId);
                }
            }
        }

        function clearCurrentChat() {
            currentChatId = null;
            chatName.textContent = 'Select a chat';
            chatStatus.textContent = 'Click on a conversation to start chatting';
            chatAvatar.innerHTML = '<i class="fas fa-user"></i>';
            messageInput.disabled = true;
            sendBtn.disabled = true;
            messagesContainer.innerHTML = '<div class="text-center py-12 text-gray-500"><i class="fas fa-comment-dots text-4xl mb-2"></i><p>Select a conversation to view messages</p></div>';
        }

        function toggleSendButton() {
            sendBtn.disabled = messageInput.value.trim() === '' || !currentChatId;
        }

        function toggleFileUpload() {
            fileUploadSection.classList.toggle('hidden');
        }

        function showChatInfo() {
            if (!currentChatId) return;
            
            const chat = chats.find(c => c.id === currentChatId);
            if (chat) {
                document.getElementById('info-chat-id').textContent = chat.id;
                document.getElementById('info-chat-type').textContent = chat.is_group ? 'Group Chat' : 'Personal Chat';
                document.getElementById('info-last-activity').textContent = formatTime(chat.last_message_time);
                document.getElementById('info-message-count').textContent = messages.length;
                chatInfoModal.classList.remove('hidden');
            }
        }

        function scrollToBottom() {
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
        }

        function formatTime(timestamp) {
            if (!timestamp) return '';
            const date = new Date(timestamp);
            return date.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
        }

        function formatDate(timestamp) {
            if (!timestamp) return '';
            const date = new Date(timestamp);
            return date.toLocaleDateString('id-ID', { 
                year: 'numeric', 
                month: 'short', 
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        }

        function highlightText(text, query) {
            if (!text || !query) return text;
            const regex = new RegExp(`(${query})`, 'gi');
            return text.replace(regex, '<mark class="bg-yellow-200">$1</mark>');
        }

        function getChatName(chatId) {
            const chat = chats.find(c => c.id === chatId);
            return chat ? chat.name : 'Unknown Chat';
        }

        function goToMessage(chatId, messageId) {
            selectChat(chatId);
            searchResultsModal.classList.add('hidden');
            // In a real implementation, you might want to scroll to the specific message
        }

        function markChatAsRead(chatId) {
            fetch(`/admin/whatsapp/api/chats/${chatId}/read`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            }).then(response => response.json())
              .then(data => {
                  if (data.success) {
                      // Update local chat data
                      const chat = chats.find(c => c.id === chatId);
                      if (chat) {
                          chat.unread_count = 0;
                          renderChatList();
                      }
                  }
              });
        }

        function showNotification(message, type = 'info') {
            // Create notification element
            const notification = document.createElement('div');
            notification.className = `fixed top-4 right-4 p-4 rounded-lg shadow-lg text-white max-w-sm z-50 ${
                type === 'success' ? 'bg-green-500' : 
                type === 'error' ? 'bg-red-500' : 
                'bg-blue-500'
            }`;
            notification.innerHTML = `
                <div class="flex items-center">
                    <i class="fas fa-${type === 'success' ? 'check' : type === 'error' ? 'exclamation-triangle' : 'info'} mr-2"></i>
                    <span>${message}</span>
                </div>
            `;
            
            document.body.appendChild(notification);
            
            // Remove after 3 seconds
            setTimeout(() => {
                notification.remove();
            }, 3000);
        }

        function startAutoRefresh() {
            // Refresh stats every 30 seconds
            setInterval(loadStats, 30000);
            
            // Refresh current chat every 10 seconds if active
            setInterval(() => {
                if (currentChatId) {
                    loadChatHistory(currentChatId);
                }
            }, 10000);
        }
    </script>
</body>
</html>