{{-- File: resources/views/mahasiswa/components/modal-tutorial-nisn.blade.php --}}

{{-- ========================================== --}}
{{-- 1. MODAL UTAMA: Tutorial NISN              --}}
{{-- ========================================== --}}
<div id="tutorialModal" class="hidden fixed inset-0 z-[9999] bg-gray-50 flex flex-col transition-opacity duration-300 opacity-0">
    
    {{-- Header Modal --}}
    <div class="sticky top-0 z-20 flex justify-between items-center p-4 md:px-6 border-b border-gray-200 bg-white shadow-sm">
        <h3 class="font-bold text-gray-800 text-base">
            <i class="fas fa-book-open text-blue-500 mr-2"></i> Tutorial Cek NISN
        </h3>
        <button type="button" onclick="closeTutorialModal()" class="text-gray-500 hover:text-red-600 bg-gray-100 hover:bg-red-50 transition-colors w-9 h-9 flex items-center justify-center rounded-full active:scale-95">
            <i class="fas fa-times text-lg"></i>
        </button>
    </div>

    {{-- Body Modal (Bisa Scroll Horizontal) --}}
    <div class="flex-1 overflow-y-auto flex flex-col relative">
        <div class="p-4 text-center mt-2">
            <p class="text-sm font-medium text-gray-600 bg-white inline-block px-4 py-1.5 rounded-full shadow-sm border border-gray-100">
                Geser ke samping untuk langkah selanjutnya <i class="fas fa-arrows-alt-h ml-1 text-blue-500"></i>
            </p>
        </div>
        
        <div class="flex gap-4 overflow-x-auto px-6 pb-8 pt-2 snap-x snap-mandatory flex-1 items-start">
            
            {{-- Foto 1 --}}
            <div class="flex-none w-[90vw] md:w-[750px] snap-center rounded-2xl border border-gray-200 bg-white p-3 shadow-lg flex flex-col mb-4">
                <p class="text-sm font-bold text-gray-800 mb-3 border-b pb-2">1</p>
                <div class="bg-gray-100 rounded-xl overflow-hidden w-full">
                    <img src="{{ asset('images/NISN/Step1.png') }}" alt="Langkah 1" class="w-full h-auto block cursor-zoom-in transition-transform hover:opacity-90" onclick="openImageViewer(this.src)" title="Klik untuk perbesar">
                </div>
            </div>

            {{-- Foto 2 --}}
            <div class="flex-none w-[90vw] md:w-[750px] snap-center rounded-2xl border border-gray-200 bg-white p-3 shadow-lg flex flex-col mb-4">
                <p class="text-sm font-bold text-gray-800 mb-3 border-b pb-2">2. </p>
                <div class="bg-gray-100 rounded-xl overflow-hidden w-full">
                    <img src="{{ asset('images/NISN/Step2.png') }}" alt="Langkah 2" class="w-full h-auto block cursor-zoom-in transition-transform hover:opacity-90" onclick="openImageViewer(this.src)" title="Klik untuk perbesar">
                </div>
            </div>

            {{-- Foto 3 --}}
            <div class="flex-none w-[90vw] md:w-[750px] snap-center rounded-2xl border border-gray-200 bg-white p-3 shadow-lg flex flex-col mb-4">
                <p class="text-sm font-bold text-gray-800 mb-3 border-b pb-2">3. </p>
                <div class="bg-gray-100 rounded-xl overflow-hidden w-full">
                    <img src="{{ asset('images/NISN/Step3.png') }}" alt="Langkah 3" class="w-full h-auto block cursor-zoom-in transition-transform hover:opacity-90" onclick="openImageViewer(this.src)" title="Klik untuk perbesar">
                </div>
            </div>
            <div class="flex-none w-[90vw] md:w-[750px] snap-center rounded-2xl border border-gray-200 bg-white p-3 shadow-lg flex flex-col mb-4">
                <p class="text-sm font-bold text-gray-800 mb-3 border-b pb-2">3. </p>
                <div class="bg-gray-100 rounded-xl overflow-hidden w-full">
                    <img src="{{ asset('images/NISN/Step4.png') }}" alt="Langkah 3" class="w-full h-auto block cursor-zoom-in transition-transform hover:opacity-90" onclick="openImageViewer(this.src)" title="Klik untuk perbesar">
                </div>
            </div>
            <div class="flex-none w-[90vw] md:w-[750px] snap-center rounded-2xl border border-gray-200 bg-white p-3 shadow-lg flex flex-col mb-4">
                <p class="text-sm font-bold text-gray-800 mb-3 border-b pb-2">3. </p>
                <div class="bg-gray-100 rounded-xl overflow-hidden w-full">
                    <img src="{{ asset('images/NISN/Step5.png') }}" alt="Langkah 3" class="w-full h-auto block cursor-zoom-in transition-transform hover:opacity-90" onclick="openImageViewer(this.src)" title="Klik untuk perbesar">
                </div>
            </div>
            
        </div>
    </div>

    {{-- Footer Modal --}}
    <div class="sticky bottom-0 z-20 p-4 md:px-6 border-t border-gray-200 bg-white shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.05)]">
        <button type="button" onclick="closeTutorialModal()" class="w-full md:w-auto md:float-right px-6 py-3.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold rounded-xl transition-all shadow-md active:scale-95 flex justify-center items-center gap-2">
            <i class="fas fa-check-circle"></i> Saya Mengerti, Tutup Tutorial
        </button>
        <div class="clear-both"></div>
    </div>
</div>


{{-- ========================================== --}}
{{-- 2. MODAL LAPIS KEDUA: Image Viewer         --}}
{{-- ========================================== --}}
<div id="imageViewer" class="hidden fixed inset-0 z-[10000] bg-black/95 backdrop-blur-sm flex items-center justify-center transition-opacity duration-300 opacity-0 cursor-zoom-out" onclick="closeImageViewer()">
    <button type="button" class="absolute top-4 right-4 md:top-6 md:right-6 text-white hover:text-red-500 w-12 h-12 flex items-center justify-center rounded-full bg-black/50 hover:bg-black/80 transition-all z-10" onclick="closeImageViewer()">
        <i class="fas fa-times text-2xl"></i>
    </button>
    <div class="w-full h-full p-2 md:p-8 flex items-center justify-center">
        <img id="fullScreenImage" src="" alt="Fullscreen View" class="max-w-full max-h-full object-contain shadow-2xl rounded-lg cursor-default" onclick="event.stopPropagation()">
    </div>
</div>


{{-- ========================================== --}}
{{-- 3. JAVASCRIPT LOGIC                        --}}
{{-- ========================================== --}}
<script>
    // Fungsi Buka/Tutup Modal Utama
    function openTutorialModal() {
        const modal = document.getElementById('tutorialModal');
        modal.classList.remove('hidden');
        setTimeout(() => {
            modal.classList.remove('opacity-0');
            modal.classList.add('opacity-100');
            document.body.style.overflow = 'hidden'; 
        }, 10);
    }

    function closeTutorialModal() {
        const modal = document.getElementById('tutorialModal');
        modal.classList.remove('opacity-100');
        modal.classList.add('opacity-0');
        setTimeout(() => {
            modal.classList.add('hidden');
            document.body.style.overflow = 'auto';
        }, 300);
    }

    // Fungsi Buka/Tutup Fullscreen Image Viewer
    function openImageViewer(imageSrc) {
        const viewer = document.getElementById('imageViewer');
        const fullImage = document.getElementById('fullScreenImage');
        
        fullImage.src = imageSrc;
        
        viewer.classList.remove('hidden');
        setTimeout(() => {
            viewer.classList.remove('opacity-0');
            viewer.classList.add('opacity-100');
        }, 10);
    }

    function closeImageViewer() {
        const viewer = document.getElementById('imageViewer');
        
        viewer.classList.remove('opacity-100');
        viewer.classList.add('opacity-0');
        
        setTimeout(() => {
            viewer.classList.add('hidden');
            document.getElementById('fullScreenImage').src = ''; 
        }, 300);
    }
</script>