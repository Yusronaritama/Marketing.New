@extends('layouts.admin.app')
@section('title', 'Chat')
@section('content')

<div class="h-[calc(100vh-100px)] p-4">
    <div class="flex h-full bg-white rounded-2xl shadow-theme-sm border border-gray-200 overflow-hidden">
        
        {{-- SIDEBAR LIST USER --}}
        <aside class="chat-sidebar border-r border-gray-200 flex flex-col bg-white">
            <div class="p-4 border-b border-gray-100 bg-gray-50/50">
                <div class="flex justify-between items-center mb-3">
                    <h2 class="text-lg font-bold text-gray-800">Pesan</h2>
                    <div class="flex gap-2">
                        <span class="text-xs bg-brand-100 text-brand-600 px-2 py-1 rounded-full font-semibold">
                            {{ count($mahasiswa) }} Kontak
                        </span>
                    </div>
                </div>
                <div class="relative">
                    <input type="text" id="search-user" placeholder="Cari kontak..." class="w-full pl-9 pr-4 py-2 bg-gray-100 border-none rounded-lg text-sm focus:ring-1 focus:ring-brand-500 transition-all placeholder-gray-500">
                    <i class="fas fa-search absolute left-3 top-2.5 text-gray-400 text-xs"></i>
                </div>
            </div>
            
            <div id="user-list" class="flex-1 overflow-y-auto custom-scrollbar">
                @forelse($mahasiswa as $mhs)
                    @php
                        // Buat Chat ID Unik berdasarkan kombinasi ID (kecil_besar)
                        $chatIdMhs = 'chat_' . min(auth()->id(), $mhs->id) . '_' . max(auth()->id(), $mhs->id);
                        $isActive = $selectedChatId == $chatIdMhs;
                        $hasUnread = $mhs->unread_count > 0;
                    @endphp
                    
                    <div class="user-item group relative p-3 cursor-pointer transition-all hover:bg-gray-50 border-b border-gray-50 {{ $isActive ? 'bg-blue-50 active-chat' : '' }}"
                         data-user-id="{{ $mhs->id }}"
                         data-user-name="{{ $mhs->name }}"
                         data-chat-id-ref="{{ $chatIdMhs }}"> {{-- Simpan referensi chat ID di sini --}}
                        
                        {{-- Garis Indikator Aktif --}}
                        <div class="indicator absolute left-0 top-0 bottom-0 w-1 bg-brand-500 {{ $isActive ? 'block' : 'hidden' }}"></div>

                        <div class="flex gap-3 items-center">
                            <div class="relative flex-shrink-0">
                                <div class="w-12 h-12 rounded-full bg-gray-200 flex items-center justify-center text-gray-500 font-bold text-lg overflow-hidden border border-gray-100 uppercase">
                                    {{ substr($mhs->name, 0, 2) }}
                                </div>
                                {{-- Status Online Dot (Opsional) --}}
                                {{-- <div class="absolute bottom-0 right-0 w-3 h-3 bg-green-500 border-2 border-white rounded-full"></div> --}}
                            </div>
                            
                            <div class="flex-1 min-w-0">
                                <div class="flex justify-between items-baseline mb-0.5">
                                    <h3 class="text-sm font-semibold text-gray-900 truncate user-name">
                                        {{ $mhs->name }}
                                    </h3>
                                    @if($mhs->last_time)
                                        <span class="text-[11px] {{ $hasUnread ? 'text-green-600 font-bold' : 'text-gray-400' }}">
                                            {{ \Carbon\Carbon::parse($mhs->last_time)->isToday() ? \Carbon\Carbon::parse($mhs->last_time)->format('H:i') : \Carbon\Carbon::parse($mhs->last_time)->format('d/m') }}
                                        </span>
                                    @endif
                                </div>
                                
                                <div class="flex justify-between items-center">
                                    <p class="text-xs truncate max-w-[85%] {{ $hasUnread ? 'font-bold text-gray-800' : 'text-gray-500' }}">
                                        {{ $mhs->last_message ?: 'Klik untuk chat' }}
                                    </p>
                                    
                                    @if($hasUnread)
                                        <span class="unread-badge flex items-center justify-center min-w-[18px] h-[18px] px-1 bg-green-500 text-white text-[10px] font-bold rounded-full">
                                            {{ $mhs->unread_count }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="flex flex-col items-center justify-center h-40 text-gray-400 mt-10">
                        <p class="text-sm">Tidak ada kontak.</p>
                    </div>
                @endforelse
            </div>
        </aside>

        {{-- MAIN CHAT AREA --}}
        <main class="flex-1 flex flex-col bg-[#efeae2] relative min-w-0">
            <div id="empty-state" class="{{ $selectedChatId ? 'hidden' : 'flex' }} absolute inset-0 flex-col items-center justify-center bg-gray-50 z-20 border-b-4 border-brand-500">
                <div class="w-64 h-64 opacity-50 mb-6">
                    <svg class="w-full h-full text-gray-300" fill="currentColor" viewBox="0 0 24 24"><path d="M20 2H4c-1.1 0-2 .9-2 2v18l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm0 14H6l-2 2V4h16v12z"/></svg>
                </div>
                <h3 class="text-2xl font-light text-gray-600 mb-2">Admin Chat</h3>
                <p class="text-gray-500 text-sm">Pilih kontak di sebelah kiri untuk memulai.</p>
            </div>

            <header id="chat-header" class="px-4 py-2.5 bg-gray-100 border-b border-gray-200 flex items-center justify-between z-10 h-[60px]">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-gray-300 flex items-center justify-center text-gray-600 overflow-hidden font-bold">
                        <span id="header-avatar-text"><i class="fas fa-user"></i></span>
                    </div>
                    <div class="cursor-pointer">
                        <h2 id="current-chat-user" class="text-base font-semibold text-gray-800 leading-tight">
                            {{-- Nama User Dimuat JS --}}
                        </h2>
                        <p id="header-status" class="text-xs text-gray-500 truncate hidden">Online</p>
                    </div>
                </div>
                <div class="flex gap-4 text-gray-500">
                    <button class="hover:text-gray-700" title="Refresh Chat" onclick="if(currentChatId) loadChats()"><i class="fas fa-sync-alt"></i></button>
                </div>
            </header>

            <div id="chat-box" class="flex-1 overflow-y-auto p-4 space-y-2 custom-scrollbar bg-opacity-10" style="background-image: url('https://user-images.githubusercontent.com/15075759/28719144-86dc0f70-73b1-11e7-911d-60d70fcded21.png');">
                {{-- Pesan di-load via JS --}}
            </div>

            <div class="px-4 py-3 bg-gray-100 flex items-end gap-2 h-auto min-h-[62px]">
                <form id="chat-form" class="flex-1 flex items-end gap-2">
                    @csrf
                    <input type="hidden" name="chat_id" id="chat-id" value="{{ $selectedChatId }}">
                    
                    <div class="flex-1 bg-white rounded-lg flex items-center border border-white focus-within:border-white focus-within:ring-0 py-2 px-4 shadow-sm">
                        <textarea name="chat_text" 
                                  id="chat-input"
                                  rows="1"
                                  placeholder="Ketik pesan..." 
                                  class="w-full bg-transparent border-none p-0 focus:ring-0 text-gray-700 resize-none max-h-32 custom-scrollbar text-sm leading-relaxed"
                                  {{ !$selectedChatId ? 'disabled' : '' }}></textarea>
                    </div>

                    <button type="submit" 
                            id="btn-submit"
                            class="p-3 text-gray-500 hover:text-brand-600 transition mb-1 disabled:opacity-50 disabled:cursor-not-allowed"
                            {{ !$selectedChatId ? 'disabled' : '' }}>
                        <i class="fas fa-paper-plane text-xl"></i>
                    </button>
                </form>
            </div>
        </main>
    </div>
</div>

{{-- JAVASCRIPT LOGIC --}}
<script>
    let currentChatId = '{{ $selectedChatId }}';
    
    // === 1. FUNGSI UTAMA: LOAD CHAT (Global scope agar bisa dipanggil) ===
    function loadChats(scrollToBottom = false) {
        if (!currentChatId) return;

        const box = document.getElementById('chat-box');
        
        fetch(`/admin/chat/fetch?chat_id=${encodeURIComponent(currentChatId)}`, {
            headers: { 'Accept': 'application/json' }
        })
        .then(res => res.json())
        .then(data => {
            if (data.length === 0) {
                box.innerHTML = '<div class="h-full flex flex-col items-center justify-center text-gray-500 opacity-80"><span class="bg-yellow-100 text-yellow-800 text-xs px-3 py-1 rounded shadow-sm">Mulai percakapan baru!</span></div>';
                return;
            }
            
            let html = '';
            let lastDate = null;

            data.forEach(chat => {
                const isMe = chat.email === '{{ auth()->user()->email }}';
                const date = new Date(chat.created_at);
                const dateStr = date.toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });

                // Divider Tanggal
                if (lastDate !== dateStr) {
                    html += `<div class="flex justify-center my-3"><span class="bg-blue-50 text-gray-600 text-[11px] font-medium px-3 py-1 rounded-lg shadow-sm border border-blue-100 uppercase tracking-wide">${dateStr}</span></div>`;
                    lastDate = dateStr;
                }

                const justify = isMe ? 'justify-end' : 'justify-start';
                const bubbleStyle = isMe 
                    ? 'bg-[#d9fdd3] text-gray-800 rounded-lg shadow-sm border border-green-100' 
                    : 'bg-white text-gray-800 rounded-lg shadow-sm border border-gray-100';
                
                // Cek status read (jika ada kolom is_read di db)
                const readStatus = chat.is_read 
                    ? '<i class="fas fa-check-double text-[10px] text-blue-500"></i>' 
                    : '<i class="fas fa-check text-[10px] text-gray-400"></i>';

                html += `
                    <div class="flex w-full ${justify} group mb-1">
                        <div class="max-w-[70%] lg:max-w-[60%] min-w-[100px]">
                            <div class="${bubbleStyle} px-3 py-1.5 relative text-sm">
                                <p class="leading-relaxed whitespace-pre-wrap break-words pb-3">${chat.chat_text}</p>
                                <div class="absolute bottom-1 right-2 flex items-center gap-1">
                                    <span class="text-[10px] text-gray-500">
                                        ${date.toLocaleTimeString('id-ID', {hour: '2-digit', minute:'2-digit'})}
                                    </span>
                                    ${isMe ? readStatus : ''} 
                                </div>
                            </div>
                        </div>
                    </div>`;
            });
            
            // Logic Scroll: Scroll ke bawah jika user sudah di bawah, atau jika dipaksa (saat klik user)
            const isScrolledToBottom = box.scrollHeight - box.clientHeight <= box.scrollTop + 150;
            box.innerHTML = html;
            
            if(isScrolledToBottom || scrollToBottom) { 
                box.scrollTop = box.scrollHeight;
            }
        })
        .catch(err => console.error("Gagal memuat chat:", err));
    }

    // === 2. EVENT LISTENER SAAT DOKUMEN SIAP ===
    document.addEventListener('DOMContentLoaded', function() {
        
        // A. Handle Klik User di Sidebar
        const userList = document.getElementById('user-list');
        
        userList.addEventListener('click', function(e) {
            // Cari elemen .user-item terdekat dari yang diklik
            const item = e.target.closest('.user-item');
            if (!item) return; // Jika yang diklik bukan user item, abaikan

            const userId = item.getAttribute('data-user-id');
            const userName = item.getAttribute('data-user-name');

            // 1. Update Tampilan Aktif di Sidebar
            document.querySelectorAll('.user-item').forEach(i => {
                i.classList.remove('bg-blue-50', 'active-chat');
                i.querySelector('.indicator').classList.add('hidden');
            });
            item.classList.add('bg-blue-50', 'active-chat');
            item.querySelector('.indicator').classList.remove('hidden');

            // 2. Hilangkan Badge Unread (Visual)
            const badge = item.querySelector('.unread-badge');
            if(badge) badge.remove();
            
            const msgPreview = item.querySelector('p.text-xs');
            if(msgPreview) {
                msgPreview.classList.remove('font-bold', 'text-gray-800');
                msgPreview.classList.add('text-gray-500');
            }

            // 3. Update Header Chat Kanan
            document.getElementById('current-chat-user').textContent = userName;
            document.getElementById('header-avatar-text').textContent = userName.charAt(0);
            document.getElementById('header-status').classList.remove('hidden');
            document.getElementById('empty-state').classList.add('hidden'); // Sembunyikan placeholder

            // 4. Fetch Chat ID dari Server
            // Kita fetch ulang untuk memastikan ID valid dan chat room terbuka
            fetch(`/admin/chat/user/${userId}`, {
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    currentChatId = data.chat_id;
                    document.getElementById('chat-id').value = currentChatId;
                    
                    // Aktifkan Input Form
                    const inputField = document.getElementById('chat-input');
                    const btnSubmit = document.getElementById('btn-submit');
                    inputField.disabled = false;
                    btnSubmit.disabled = false;
                    inputField.focus();
                    
                    // Load pesan dan paksa scroll ke bawah
                    loadChats(true);
                    
                    // UPDATE URL Browser tanpa reload (PENTING agar kalau refresh tetap di chat ini)
                    const url = new URL(window.location);
                    url.searchParams.set('chat_id', currentChatId);
                    window.history.pushState({}, '', url);
                }
            })
            .catch(console.error);
        });

        // B. Search Filter Sidebar
        const searchInput = document.getElementById('search-user');
        if(searchInput){
            searchInput.addEventListener('keyup', function() {
                const filter = this.value.toLowerCase();
                const items = document.querySelectorAll('.user-item');
                items.forEach(item => {
                    const name = item.getAttribute('data-user-name').toLowerCase();
                    if(name.includes(filter)) {
                        item.style.display = "";
                    } else {
                        item.style.display = "none";
                    }
                });
            });
        }

        // C. Setup Awal jika ada Selected Chat (dari URL/Controller)
        if (currentChatId) {
            // Cari item sidebar yang sesuai dengan chat id ini (kita simpan di data-chat-id-ref tadi)
            // Atau trigger klik pada user yang aktif jika ada class 'active-chat'
            const activeItem = document.querySelector('.user-item.active-chat');
            if(activeItem) {
                // Scroll sidebar ke user yang aktif
                activeItem.scrollIntoView({ behavior: 'smooth', block: 'center' });
                
                // Update Header manual karena event click tidak trigget otomatis saat load
                const userName = activeItem.getAttribute('data-user-name');
                document.getElementById('current-chat-user').textContent = userName;
                document.getElementById('header-avatar-text').textContent = userName.charAt(0);
                document.getElementById('header-status').classList.remove('hidden');
                document.getElementById('chat-input').disabled = false;
                document.getElementById('btn-submit').disabled = false;
                
                // Load pesan
                loadChats(true);
            }
        }

        // D. Auto Refresh (Polling)
        setInterval(() => { 
            if (currentChatId) loadChats(false); 
        }, 3000);
        
        // E. Auto Resize Textarea Input
        const chatInput = document.getElementById('chat-input');
        if(chatInput) {
            chatInput.addEventListener('input', function() {
                this.style.height = 'auto';
                this.style.height = (this.scrollHeight) + 'px';
            });
            // Enter to submit
            chatInput.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    document.getElementById('chat-form').dispatchEvent(new Event('submit'));
                }
            });
        }
    });

    // === 3. HANDLE KIRIM PESAN ===
    document.getElementById('chat-form').addEventListener('submit', function(e) {
        e.preventDefault();
        if (!currentChatId) return;

        let input = document.getElementById('chat-input');
        if (!input.value.trim()) return;

        let formData = new FormData(this);

        fetch('/admin/chat/send', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                input.value = '';
                input.style.height = 'auto';
                loadChats(true); // Load dan scroll ke bawah
            }
        })
        .catch(console.error);
    });
</script>

<style>
/* CSS Scrollbar Halus */
.custom-scrollbar::-webkit-scrollbar { width: 5px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background-color: #cbd5e1; border-radius: 10px; }
.custom-scrollbar::-webkit-scrollbar-thumb:hover { background-color: #9ca3af; }

/* Fixed Sidebar Width */
.chat-sidebar {
    width: 350px;
    flex-shrink: 0;
}
/* Mobile Responsive */
@media (max-width: 768px) {
    .chat-sidebar {
        width: 80px; 
    }
    .chat-sidebar h2, .chat-sidebar .relative, .chat-sidebar .user-name, .chat-sidebar p.truncate {
        display: none;
    }
    .chat-sidebar .user-item {
        justify-content: center;
    }
}
</style>
@endsection