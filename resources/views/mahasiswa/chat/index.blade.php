@extends('layouts.mahasiswa.app')

{{-- Load Library Tambahan --}}
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

@push('styles')
<style>
    /* Scrollbar Kustom untuk Chat */
    .chat-scroll::-webkit-scrollbar {
        width: 6px;
    }
    .chat-scroll::-webkit-scrollbar-track {
        background: rgba(255, 255, 255, 0.05);
        border-radius: 10px;
    }
    .chat-scroll::-webkit-scrollbar-thumb {
        background: rgba(255, 255, 255, 0.2);
        border-radius: 10px;
    }
    .chat-scroll::-webkit-scrollbar-thumb:hover {
        background: rgba(255, 255, 255, 0.4);
    }
</style>
@endpush

@section('content')
<div class="h-[calc(100vh-140px)] flex flex-col relative z-10 px-4 sm:px-6 lg:px-8">

    <div class="flex-1 container mx-auto max-w-5xl h-full flex flex-col">
        
        {{-- HEADER JUDUL --}}
        <div class="mb-4 text-center">
            <h1 class="text-2xl font-bold text-white drop-shadow-md">Pusat Bantuan 💬</h1>
            <p class="text-blue-200 text-sm">Hubungi admin untuk kendala pendaftaran</p>
        </div>

        {{-- CHAT MAIN CONTAINER (GLASS) --}}
        <div class="flex-1 flex flex-col bg-white/10 backdrop-blur-xl border border-white/20 rounded-2xl shadow-2xl overflow-hidden relative">
            
            {{-- 1. CHAT HEADER --}}
            <div class="px-6 py-4 border-b border-white/10 bg-white/5 flex items-center justify-between z-20">
                <div class="flex items-center space-x-4">
                    <div class="relative">
                        <div class="w-12 h-12 rounded-full bg-gradient-to-tr from-blue-400 to-indigo-500 flex items-center justify-center shadow-lg">
                            <i class="fas fa-headset text-white text-xl"></i>
                        </div>
                        <div class="absolute bottom-0 right-0 w-3.5 h-3.5 bg-green-400 border-2 border-blue-900 rounded-full animate-pulse"></div>
                    </div>
                    <div>
                        <h2 class="font-bold text-white text-lg">Admin Penmabar</h2>
                        <div class="flex items-center text-xs text-blue-200">
                            <span class="mr-1">●</span> Online • Siap Membantu
                        </div>
                    </div>
                </div>
            </div>

            {{-- 2. CHAT MESSAGES AREA --}}
            <div id="chat-messages" class="flex-1 overflow-y-auto p-6 space-y-6 chat-scroll bg-black/10">
                {{-- Pesan akan dimuat di sini via JS --}}
                <div class="h-full flex flex-col items-center justify-center text-white/30">
                    <i class="fas fa-circle-notch fa-spin text-3xl mb-3"></i>
                    <p>Memuat percakapan...</p>
                </div>
            </div>

            {{-- 3. CHAT INPUT AREA --}}
            <div class="p-4 border-t border-white/10 bg-white/5 backdrop-blur-md">
                <form id="chat-form" class="relative flex items-center gap-3">
                    @csrf
                    <input type="hidden" id="chat_id" value="{{ $chatId }}">
                    
                    {{-- Input Field --}}
                    <div class="flex-1 relative">
                        <input type="text" id="chat_text" name="chat_text"
                            class="w-full bg-black/20 border border-white/10 text-white placeholder-white/40 rounded-full py-3.5 pl-5 pr-12 focus:outline-none focus:ring-2 focus:ring-blue-400/50 focus:bg-black/30 transition-all"
                            placeholder="Ketik pesan Anda di sini..."
                            autocomplete="off"
                            maxlength="255" required>
                        
                        {{-- Icon Keyboard (Hiasan) --}}
                        <div class="absolute right-4 top-1/2 transform -translate-y-1/2 text-white/30">
                            <i class="far fa-keyboard"></i>
                        </div>
                    </div>

                    {{-- Send Button --}}
                    <button type="submit"
                        class="w-12 h-12 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white rounded-full shadow-lg shadow-blue-500/30 flex items-center justify-center transform hover:scale-110 transition-all duration-200 group">
                        <i class="fas fa-paper-plane group-hover:translate-x-0.5 group-hover:-translate-y-0.5 transition-transform"></i>
                    </button>
                </form>
                
                {{-- Footer Text --}}
                <div class="text-center mt-2">
                    <p class="text-[10px] text-white/30">
                        <i class="fas fa-lock mr-1"></i> Percakapan ini terenkripsi dan aman.
                    </p>
                </div>
            </div>

        </div>
        
        {{-- Info Alert (Floating) --}}
        <div class="mt-4 bg-blue-900/60 backdrop-blur-md border border-blue-500/30 rounded-xl p-4 flex items-start gap-3 shadow-lg">
            <div class="bg-blue-500/20 p-2 rounded-full text-blue-300">
                <i class="fas fa-info"></i>
            </div>
            <div>
                <h3 class="font-bold text-white text-sm">Jam Operasional</h3>
                <p class="text-blue-100 text-xs mt-0.5 leading-relaxed">
                    Admin akan membalas pesan pada jam kerja (08:00 - 16:00 WIB). Mohon menunggu balasan dengan sabar.
                </p>
            </div>
        </div>

    </div>
</div>

{{-- LOGIKA JAVASCRIPT (JQUERY) --}}
{{-- Saya update template HTML di dalam fungsi displayMessages agar sesuai tema Glass --}}
<script>
    $(document).ready(function() {
        const chatId = $('#chat_id').val();
        let isFetching = false;

        // Format Waktu (HH:MM)
        function formatTime(dateString) {
            const date = new Date(dateString);
            return date.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
        }

        // Scroll ke Bawah
        function scrollToBottom() {
            const chatContainer = $('#chat-messages');
            chatContainer.animate({ scrollTop: chatContainer[0].scrollHeight }, 300);
        }

        // Fetch Pesan
        function fetchMessages() {
            if (isFetching) return;
            isFetching = true;
            
            $.ajax({
                url: '{{ route("mahasiswa.chat.fetch") }}',
                type: 'GET',
                data: { chat_id: chatId },
                success: function(response) {
                    displayMessages(response);
                    isFetching = false;
                },
                error: function(xhr) {
                    console.error('Error:', xhr);
                    isFetching = false;
                }
            });
        }

        // Render HTML Pesan (Glassmorphism Style)
        function displayMessages(messages) {
            const chatContainer = $('#chat-messages');
            
            // State Kosong
            if (messages.length === 0) {
                chatContainer.html(`
                    <div class="h-full flex flex-col items-center justify-center text-white/40">
                        <div class="w-20 h-20 bg-white/5 rounded-full flex items-center justify-center mb-4 backdrop-blur-sm border border-white/10">
                            <i class="fas fa-comments text-4xl"></i>
                        </div>
                        <p class="font-medium">Belum ada percakapan.</p>
                        <p class="text-sm">Sapa admin untuk memulai!</p>
                    </div>
                `);
                return;
            }

            let html = '';
            let lastSender = ''; // Untuk grouping tampilan (opsional)

            messages.forEach(message => {
                const isAdmin = message.role === 'admin';
                const time = formatTime(message.created_at);
                
                // HTML TEMPLATE BARU
                if (isAdmin) {
                    // --- BUBBLE ADMIN (KIRI - KACA) ---
                    html += `
                        <div class="flex justify-start mb-2 animate-fade-in-up">
                            <div class="flex flex-row items-end gap-3 max-w-[85%] md:max-w-[70%]">
                                <div class="flex-shrink-0 w-8 h-8 rounded-full bg-blue-500/20 border border-blue-400/30 flex items-center justify-center text-blue-200 text-xs font-bold shadow-sm">
                                    A
                                </div>
                                <div class="bg-white/10 backdrop-blur-md border border-white/10 text-white rounded-2xl rounded-bl-none px-5 py-3 shadow-md">
                                    <div class="text-sm leading-relaxed">${message.chat_text}</div>
                                    <div class="text-[10px] text-white/40 text-right mt-1">${time}</div>
                                </div>
                            </div>
                        </div>
                    `;
                } else {
                    // --- BUBBLE USER (KANAN - GRADIENT) ---
                    html += `
                        <div class="flex justify-end mb-2 animate-fade-in-up">
                            <div class="flex flex-row-reverse items-end gap-3 max-w-[85%] md:max-w-[70%]">
                                <div class="flex-shrink-0 w-8 h-8 rounded-full bg-white/20 border border-white/30 flex items-center justify-center text-white text-xs font-bold shadow-sm">
                                    Y
                                </div>
                                <div class="bg-gradient-to-br from-blue-600 to-indigo-600 text-white border border-blue-500/30 rounded-2xl rounded-br-none px-5 py-3 shadow-lg shadow-blue-600/20">
                                    <div class="text-sm leading-relaxed">${message.chat_text}</div>
                                    <div class="text-[10px] text-blue-200 text-right mt-1 flex items-center justify-end gap-1">
                                        ${time} <i class="fas fa-check-double"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                }
            });

            // Hanya update HTML jika ada perubahan (logic sederhana)
            // Disini kita replace saja biar aman sinkronisasi datanya
            const currentHeight = chatContainer[0].scrollHeight;
            const currentScroll = chatContainer.scrollTop();
            
            chatContainer.html(html);

            // Auto scroll jika user ada di bawah, atau pesan baru muncul
            // (Simplifikasi: scroll ke bawah setiap update agar chat realtime terasa)
            // Anda bisa buat logic lebih pintar jika mau.
            // chatContainer.scrollTop(chatContainer[0].scrollHeight);
        }

        // Submit Form
        $('#chat-form').on('submit', function(e) {
            e.preventDefault();
            const input = $('#chat_text');
            const chatText = input.val().trim();
            
            if (!chatText) return;

            // Optimistic UI: Langsung tampilkan pesan user (biar kerasa cepet)
            // Nanti fetchMessages akan menimpa dengan data server yang valid
            const fakeTime = new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
            const tempBubble = `
                <div class="flex justify-end mb-2 opacity-50">
                    <div class="flex flex-row-reverse items-end gap-3 max-w-[85%]">
                        <div class="w-8 h-8 rounded-full bg-white/10"></div>
                        <div class="bg-gradient-to-br from-blue-600 to-indigo-600 text-white rounded-2xl rounded-br-none px-5 py-3">
                            <div class="text-sm">${chatText}</div>
                            <div class="text-[10px] text-right mt-1">Mengirim...</div>
                        </div>
                    </div>
                </div>
            `;
            $('#chat-messages').append(tempBubble);
            scrollToBottom();
            input.val('');

            // Kirim ke Server
            $.ajax({
                url: '{{ route("mahasiswa.chat.store") }}',
                type: 'POST',
                data: {
                    chat_text: chatText,
                    chat_id: chatId,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        fetchMessages(); // Refresh data asli dari server
                        setTimeout(scrollToBottom, 200); // Scroll ke bawah setelah load
                    }
                },
                error: function(xhr) {
                    alert('Gagal mengirim pesan.');
                }
            });
        });

        // Enter to Send
        $('#chat_text').on('keypress', function(e) {
            if (e.which === 13 && !e.shiftKey) {
                e.preventDefault();
                $('#chat-form').submit();
            }
        });

        // Loop Fetching
        setInterval(fetchMessages, 3000);
        fetchMessages(); // First load
        
        // Delay scroll pertama kali
        setTimeout(scrollToBottom, 500);
    });
</script>

<style>
    /* Animasi Bubble Muncul */
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .animate-fade-in-up {
        animation: fadeInUp 0.3s ease-out forwards;
    }
</style>
@endsection