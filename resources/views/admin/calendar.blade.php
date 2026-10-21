@extends('layouts.admin.app')

@section('title', 'Kalender')

@section('content')
<main class="min-h-screen bg-gray-50 dark:bg-gray-900 py-6">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="mb-8">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <div class="flex-1">
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center">
                        <svg class="w-6 h-6 mr-2 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        Kalender
                    </h1>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Kelola jadwal dan agenda penting Anda di satu tempat.
                    </p>
                </div>
                
                <div class="mt-4 sm:mt-0 sm:ml-4 flex items-center gap-2">
                    <button id="refreshCalendarBtn" type="button" class="inline-flex items-center justify-center p-2 text-sm font-medium rounded-lg shadow-sm dark:text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500" title="Refresh Kalender">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                        </svg>
                    </button>
                    
                    <button id="openPopupBtn" type="button" class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-2 text-sm font-medium rounded-lg shadow-sm dark:text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                        </svg>
                        Tambah Event
                    </button>
                </div>
                </div>
        </div>

        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-lg rounded-xl">
            <div class="p-4 sm:p-6">
                <div id="calendar" class="w-full"></div>
            </div>
        </div>
    </div>

    <div id="eventPopup" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 transition-opacity duration-300 ease-in-out opacity-0">
        <div class="absolute inset-0 backdrop-blur-md bg-black/40"></div>
        
        <div id="popupContent" class="relative bg-white dark:bg-gray-800 w-full max-w-md rounded-2xl shadow-2xl p-6 transform transition-all duration-300 ease-in-out scale-95 opacity-0">
            <button id="closePopupBtn" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 dark:text-gray-500 dark:hover:text-white">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
            <h2 id="popupTitle" class="text-xl font-semibold mb-5 text-gray-800 dark:text-white flex items-center">
                {{-- SVG dan Teks akan diisi oleh JS --}}
            </h2>
            
            <form id="eventForm" class="space-y-4">
                <input type="hidden" id="eventId">

                <div>
                    <label for="eventTitle" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Judul Event</label>
                    <input type="text" id="eventTitle" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none" placeholder="Contoh: Rapat Bulanan" required>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="eventStart" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Waktu Mulai</label>
                        <input type="datetime-local" id="eventStart" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none" required>
                    </div>
                    <div>
                        <label for="eventEnd" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Waktu Selesai</label>
                        <input type="datetime-local" id="eventEnd" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none" required>
                    </div>
                </div>

                <div class="flex justify-between items-center pt-4">
                    <button type="button" id="deleteEventBtn" class="hidden px-4 py-2 text-sm font-medium rounded-lg text-red-600 dark:text-red-400 hover:bg-red-100 dark:hover:bg-red-900/50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                        Hapus
                    </button>

                    <div class="flex gap-2 ml-auto">
                        <button type="button" id="cancelPopupBtn" class="px-5 py-2 text-sm font-medium bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-200 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-400">
                            Batal
                        </button>
                        <button type="button" id="saveEventBtn" class="px-5 py-2 text-sm font-medium bg-blue-600 dark:text-gray-200 rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Simpan
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</main>

{{-- FullCalendar --}}
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/main.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const popup = document.getElementById('eventPopup');
    const popupContent = document.getElementById('popupContent');
    const popupTitle = document.getElementById('popupTitle');
    const eventIdInput = document.getElementById('eventId');
    const deleteBtn = document.getElementById('deleteEventBtn');
    
    // [FIX] ID tombol ini sekarang ada di HTML
    const refreshCalendarBtn = document.getElementById('refreshCalendarBtn'); 
    
    const openPopupBtn = document.getElementById('openPopupBtn');
    const closePopupBtn = document.getElementById('closePopupBtn');
    const cancelPopupBtn = document.getElementById('cancelPopupBtn');

    const addIcon = '<svg class="w-5 h-5 mr-2 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>';
    const editIcon = '<svg class="w-5 h-5 mr-2 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>';

    function resetForm() {
        document.getElementById('eventForm').reset();
        eventIdInput.value = '';
        popupTitle.innerHTML = `${addIcon} Tambah Event Baru`;
        deleteBtn.classList.add('hidden');
    }

    function openPopup() {
        popup.classList.remove('hidden');
        setTimeout(() => {
            popup.classList.remove('opacity-0');
            popupContent.classList.remove('opacity-0');
            popupContent.classList.remove('scale-95');
        }, 10);
    }

    function closePopup() {
        popup.classList.add('opacity-0');
        popupContent.classList.add('opacity-0');
        popupContent.classList.add('scale-95');
        setTimeout(() => {
            popup.classList.add('hidden');
        }, 300);
    }

    openPopupBtn.addEventListener('click', () => {
        resetForm();
        openPopup();
    });

    // [FIX] Listener ini sekarang tidak akan error karena tombolnya ada
    refreshCalendarBtn.addEventListener('click', function() {
        calendar.refetchEvents(); // Perintah FullCalendar untuk memuat ulang data
    });

    [closePopupBtn, cancelPopupBtn].forEach(btn => {
        btn.addEventListener('click', closePopup);
    });

    // --- Palet warna ---
    const eventColors = [
        '#ffd5be', 
        '#10b981', // emerald-500
        '#f59e0b', // amber-500
        '#ef4444', // red-500
        '#8b5cf6', // violet-500
        '#ec4899', // pink-500
        '#06b6d4', // cyan-500
        '#f97316', // orange-500
        '#14b8a6', // teal-500
        '#65a30d'  // lime-600
    ];

    function getRandomEventColor() {
        return eventColors[Math.floor(Math.random() * eventColors.length)];
    }

    // Inisialisasi Kalender
    const calendarEl = document.getElementById('calendar');
    const calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        events: "{{ route('admin.events.index') }}", 
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        editable: true,
        selectable: true,
        eventTextColor: '#ffffff',
        nowIndicator: true,
        
        eventDataTransform: function(eventData) {
            if (eventData.type === 'holiday' || eventData.type === 'observance') {
                return eventData; 
            }
            if (!eventData.color) {
                eventData.color = getRandomEventColor();
            }
            return eventData;
        },

        eventClick: function(info) {
            if (info.event.extendedProps.type === 'holiday' || info.event.extendedProps.type === 'observance') {
                info.jsEvent.preventDefault(); 
                return false;
            }

            const event = info.event;
            eventIdInput.value = event.id;
            
            document.getElementById('eventTitle').value = event.title;
            document.getElementById('eventStart').value = formatToDatetimeLocal(event.startStr);
            document.getElementById('eventEnd').value = formatToDatetimeLocal(event.endStr || event.startStr);

            popupTitle.innerHTML = `${editIcon} Edit Event`;
            
            deleteBtn.classList.remove('hidden');
            openPopup();
        }
    });
    calendar.render();

    // Helper: format FullCalendar date string ke datetime-local
    function formatToDatetimeLocal(dateStr) {
        if (!dateStr) return '';
        const date = new Date(dateStr);
        if (isNaN(date.getTime())) {
            return dateStr.substring(0, 16);
        }
        const year = date.getFullYear();
        const month = (date.getMonth() + 1).toString().padStart(2, '0');
        const day = date.getDate().toString().padStart(2, '0');
        const hours = date.getHours().toString().padStart(2, '0');
        const minutes = date.getMinutes().toString().padStart(2, '0');
        
        return `${year}-${month}-${day}T${hours}:${minutes}`;
    }
    // Simpan Event (Create atau Update)
    document.getElementById('saveEventBtn').addEventListener('click', function () {
        const id = eventIdInput.value;
        const title = document.getElementById('eventTitle').value.trim();
        const start = document.getElementById('eventStart').value;
        const end = document.getElementById('eventEnd').value;

        if (!title || !start || !end) {
            alert('Semua field wajib diisi!');
            return;
        }

        const url = id 
            ? "{{ url('admin/events') }}/" + id 
            : "{{ route('admin.events.store') }}";
        const method = id ? 'PUT' : 'POST';

        fetch(url, {
            method: method,
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": "{{ csrf_token() }}"
            },
            body: JSON.stringify({ title, start, end })
        })
        .then(res => res.json())
        .then(data => {
            if (data.event) {
                if (id) {
                    // Update event di kalender
                    const event = calendar.getEventById(id);
                    if (event) {
                        event.setProp('title', data.event.title);
                        event.setDates(data.event.start, data.event.end);
                        // Catatan: Warna event yang diupdate akan tetap sama (tidak di-random ulang)
                    }
                } else {
                    // Tambah event baru
                    calendar.addEvent({
                        id: data.event.id,
                        title: data.event.title,
                        start: data.event.start,
                        end: data.event.end,
                        allDay: data.event.allDay,
                        color: getRandomEventColor() // <-- [DIUBAH] Tambahkan warna random saat buat baru
                    });
                }
                closePopup();
                resetForm();
            } else {
                alert(data.message || 'Gagal menyimpan event!');
            }
        })
        .catch(err => {
            console.error(err);
            alert('Terjadi kesalahan. Gagal menyimpan event!');
        });
    });

    // Hapus Event
    deleteBtn.addEventListener('click', function () {
        const id = eventIdInput.value;
        if (!id) return;

        if (!confirm('Apakah Anda yakin ingin menghapus event ini?')) return;

        fetch("{{ url('admin/events') }}/" + id, {
            method: "DELETE",
            headers: {
                "X-CSRF-TOKEN": "{{ csrf_token() }}"
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                const event = calendar.getEventById(id);
                if (event) event.remove();
                closePopup();
                resetForm();
            } else {
                 alert(data.message || 'Gagal menghapus event!');
            }
        })
        .catch(() => alert('Terjadi kesalahan. Gagal menghapus event!'));
    });
});
</script>

<style>
/* Sorot hari ini lebih kuat */
.fc .fc-day-today {
    background-color: #f0f9ff !important; /* light blue-50 */
}
.dark .fc .fc-day-today {
    background-color: #1e3a8a !important; /* dark blue-900 */
}
.fc .fc-col-header-cell {
    background-color: #ff9357 !important;
}
.fc .fc-col-header-cell-cushion { /* Ini untuk teks 'Sen', 'Sel' */
    color: #ffffff !important;
}
/* Kustomisasi warna tombol toolbar FullCalendar agar seragam */
.fc .fc-button-primary {
    background-color: #ffd5be; /* blue-600 */
    border-color: #ffd5be;
}
.fc .fc-button-primary:hover {
    background-color: #fde4d6; /* blue-700 */
    border-color: #fde4d6;
}
.fc .fc-button-primary:disabled {
    background-color:#fff5ef; /* blue-300 */
    border-color: #fff5ef;
}
.fc .fc-day-sat { /* Sabtu */
    color: #2b2121 !important;
    background-color:#ffd2d2 !important; /* light red-50 */
}
.fc .fc-day-sun { /* Minggu */
    color: #2b2121 !important;
    background-color: #ff9393 !important; /* light red-100 */
}

.dark .fc .fc-day-sat { /* Sabtu (Dark Mode) */
    background-color: #2b2121 !important; /* Merah gelap yang sangat samar */
}
.dark .fc .fc-day-sun { /* Minggu (Dark Mode) */
    color:#ffd2d2 !important;
    background-color: #ff9393 ; /* Merah gelap yang sedikit lebih terlihat */
}
/* --- [AKHIR BLOK BARU] --- */


/* Kustomisasi warna tombol toolbar FullCalendar agar seragam */
.fc .fc-button-primary {
    background-color: #ffd5be; /* blue-600 */
    border-color: #ffd5be;
}
.fc .fc-button-primary:hover {
    background-color: #fde4d6; /* blue-700 */
    border-color: #fde4d6;
}
.fc .fc-button-primary:disabled {
    background-color: #93c5fd; /* blue-300 */
    border-color: #93c5fd;
}
</style>
@endsection
 <script src="{{ asset('assets/js/bundle.js') }}"></script>