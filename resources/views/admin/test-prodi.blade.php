@extends('layouts.admin.app')

@section('title', 'Test Data Prodi')

@section('content')
<div class="rounded-sm border border-stroke bg-white shadow-default dark:border-strokedark dark:bg-boxdark">
    <div class="border-b border-stroke px-4 py-4 dark:border-strokedark sm:px-6 xl:px-7.5">
        <h3 class="font-medium text-black dark:text-white">
            Test Retrieval Data Prodi & Nilai
        </h3>
    </div>
    
    <div class="p-6">
        <!-- Connection Test -->
        <div class="mb-6">
            <h4 class="mb-3 text-lg font-semibold text-black dark:text-white">Test Koneksi Moodle</h4>
            <button onclick="testMoodleConnection()" class="inline-flex items-center justify-center rounded-md bg-blue-light-500 px-4 py-2 text-sm font-medium text-white hover:bg-blue-light-600">
                Test Koneksi Moodle
            </button>
            <div id="connectionResult" class="mt-2"></div>
        </div>

        <!-- Main Test Form -->
        <div class="mb-6">
            <h4 class="mb-3 text-lg font-semibold text-black dark:text-white">Ambil Data Prodi & Nilai</h4>
            <form id="prodiForm">
                @csrf
                <div class="mb-4">
                    <label class="mb-2.5 block font-medium text-black dark:text-white">
                        ID Pendaftaran
                    </label>
                    <input type="number" name="id_pendaftaran" 
                           class="w-full rounded-lg border border-stroke bg-transparent py-3 pl-4 pr-10 text-black outline-none focus:border-brand-500 focus-visible:shadow-none dark:border-form-strokedark dark:bg-form-input dark:text-white dark:focus:border-brand-500"
                           placeholder="Masukkan ID Pendaftaran" required>
                </div>
                <button type="submit" class="inline-flex items-center justify-center rounded-md bg-brand-500 px-4 py-2 text-sm font-medium text-white hover:bg-brand-600">
                    Ambil Data
                </button>
            </form>
        </div>

        <!-- Results Display -->
        <div id="results" class="hidden">
            <h4 class="mb-3 text-lg font-semibold text-black dark:text-white">Hasil Data</h4>
            <div class="rounded-lg border border-stroke bg-gray-50 p-4 dark:border-strokedark dark:bg-gray-800">
                <pre id="resultData" class="text-sm whitespace-pre-wrap"></pre>
            </div>
        </div>

        <!-- Loading Spinner -->
        <div id="loading" class="hidden text-center">
            <div class="inline-block h-8 w-8 animate-spin rounded-full border-4 border-solid border-brand-500 border-t-transparent"></div>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">Memproses data...</p>
        </div>
    </div>
</div>
{{-- Tambahkan bagian ini di view --}}
<div class="mb-6">
    <h4 class="mb-3 text-lg font-semibold text-black dark:text-white">Daftar Kode Prodi</h4>
    <button onclick="showProdiCodes()" class="inline-flex items-center justify-center rounded-md bg-gray-500 px-4 py-2 text-sm font-medium text-white hover:bg-gray-600 mb-3">
        Tampilkan Kode Prodi
    </button>
    <div id="prodiCodes" class="hidden grid grid-cols-2 md:grid-cols-3 gap-2 text-sm"></div>
</div>

<script>
function showProdiCodes() {
    const prodiCodesDiv = document.getElementById('prodiCodes');
    
    fetch('{{ route("admin.prodi.codes") }}')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                let html = '';
                for (const [kode, label] of Object.entries(data.data)) {
                    html += `<div class="bg-gray-100 dark:bg-gray-700 p-2 rounded">
                        <strong>${kode}</strong>: ${label}
                    </div>`;
                }
                prodiCodesDiv.innerHTML = html;
                prodiCodesDiv.classList.remove('hidden');
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
}
</script>
<script>
function testMoodleConnection() {
    const resultDiv = document.getElementById('connectionResult');
    resultDiv.innerHTML = '<div class="text-blue-500">Testing connection...</div>';

    fetch('{{ route("admin.test.moodle") }}')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                resultDiv.innerHTML = `
                    <div class="rounded-md bg-success-50 p-3 text-success-700 dark:bg-success-500/15 dark:text-success-500">
                        <strong>✓ Berhasil:</strong> ${data.message}
                        <br><small>Sitename: ${data.data.sitename}</small>
                    </div>
                `;
            } else {
                resultDiv.innerHTML = `
                    <div class="rounded-md bg-error-50 p-3 text-error-700 dark:bg-error-500/15 dark:text-error-500">
                        <strong>✗ Gagal:</strong> ${data.message}
                    </div>
                `;
            }
        })
        .catch(error => {
            resultDiv.innerHTML = `
                <div class="rounded-md bg-error-50 p-3 text-error-700 dark:bg-error-500/15 dark:text-error-500">
                    <strong>✗ Error:</strong> ${error.message}
                </div>
            `;
        });
}

document.getElementById('prodiForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const resultsDiv = document.getElementById('results');
    const loadingDiv = document.getElementById('loading');
    const resultData = document.getElementById('resultData');
    
    // Show loading, hide results
    loadingDiv.classList.remove('hidden');
    resultsDiv.classList.add('hidden');
    
    fetch('{{ route("admin.get.prodi.data") }}', {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        loadingDiv.classList.add('hidden');
        resultsDiv.classList.remove('hidden');
        
        if (data.success) {
            resultData.innerHTML = JSON.stringify(data.data, null, 2);
            resultData.className = 'text-sm whitespace-pre-wrap text-success-600';
        } else {
            resultData.innerHTML = `Error: ${data.message}`;
            resultData.className = 'text-sm whitespace-pre-wrap text-error-600';
        }
    })
    .catch(error => {
        loadingDiv.classList.add('hidden');
        resultsDiv.classList.remove('hidden');
        resultData.innerHTML = `Network Error: ${error.message}`;
        resultData.className = 'text-sm whitespace-pre-wrap text-error-600';
    });
});
</script>

<style>
#resultData {
    max-height: 400px;
    overflow-y: auto;
}
</style>
@endsection
<script src="{{ asset('assets/js/bundle.js') }}"></script>