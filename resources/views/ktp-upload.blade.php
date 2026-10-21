<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Foto KTP - OCR Scanner</title>
    @if (app()->environment('local', 'development'))
        <script src="https://cdn.tailwindcss.com"></script>
    @endif
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-50 min-h-screen">
    <div class="container mx-auto px-4 py-8 max-w-2xl">
        <div class="bg-white rounded-lg shadow-md p-6">
            <!-- Header -->
            <div class="text-center mb-8">
                <h1 class="text-2xl font-bold text-gray-800 mb-2">
                    <i class="fas fa-id-card text-blue-500 mr-2"></i>
                    Scanner KTP OCR
                </h1>
                <p class="text-gray-600">Upload foto KTP untuk ekstraksi data otomatis</p>
            </div>

            <!-- Upload Form -->
            <form id="ktpUploadForm" enctype="multipart/form-data" class="space-y-6">
                @csrf
                
                <!-- Upload Area -->
                <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center transition-colors duration-200 hover:border-blue-400"
                     id="uploadArea">
                    <input type="file" 
                           id="fotoKtp" 
                           name="fotoKtp" 
                           accept="image/*" 
                           class="hidden" 
                           required>
                    
                    <div class="space-y-3">
                        <i class="fas fa-cloud-upload-alt text-4xl text-gray-400"></i>
                        <div>
                            <p class="text-lg font-medium text-gray-700">Pilih atau seret foto KTP</p>
                            <p class="text-sm text-gray-500">Format: JPG, PNG (Maks. 5MB)</p>
                        </div>
                        <button type="button" 
                                onclick="document.getElementById('fotoKtp').click()" 
                                class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-lg transition-colors duration-200">
                            <i class="fas fa-folder-open mr-2"></i>Pilih File
                        </button>
                    </div>
                </div>

                <!-- Preview Image -->
                <div id="imagePreview" class="hidden text-center">
                    <p class="text-sm text-gray-600 mb-2">Preview:</p>
                    <img id="preview" class="mx-auto max-w-xs rounded-lg shadow-md border">
                </div>

                <!-- Loading Indicator -->
                <div id="loading" class="hidden text-center py-4">
                    <div class="inline-flex items-center space-x-2">
                        <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-500"></div>
                        <span class="text-gray-600">Memproses gambar...</span>
                    </div>
                </div>

                <!-- Result Section -->
                <div id="resultSection" class="hidden space-y-4">
                    <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                        <div class="flex items-center space-x-2 text-green-800">
                            <i class="fas fa-check-circle"></i>
                            <span class="font-medium" id="resultMessage"></span>
                        </div>
                    </div>

                    <!-- Data Results -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4" id="dataResults">
                        <!-- Data akan diisi oleh JavaScript -->
                    </div>
                </div>

                <!-- Error Message -->
                <div id="errorMessage" class="hidden bg-red-50 border border-red-200 rounded-lg p-4">
                    <div class="flex items-center space-x-2 text-red-800">
                        <i class="fas fa-exclamation-circle"></i>
                        <span class="font-medium" id="errorText"></span>
                    </div>
                </div>
            </form>

            <!-- Instructions -->
            <div class="mt-8 p-4 bg-blue-50 rounded-lg">
                <h3 class="font-medium text-blue-800 mb-2">
                    <i class="fas fa-info-circle mr-2"></i>Tips Foto KTP yang Baik:
                </h3>
                <ul class="text-sm text-blue-700 space-y-1 list-disc list-inside">
                    <li>Pastikan KTP dalam kondisi baik dan terbaca jelas</li>
                    <li>Foto dalam pencahayaan yang cukup</li>
                    <li>Pastikan seluruh bagian KTP terlihat</li>
                    <li>Hindari bayangan dan silau</li>
                </ul>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const fileInput = document.getElementById('fotoKtp');
            const uploadArea = document.getElementById('uploadArea');
            const imagePreview = document.getElementById('imagePreview');
            const preview = document.getElementById('preview');
            const loading = document.getElementById('loading');
            const resultSection = document.getElementById('resultSection');
            const resultMessage = document.getElementById('resultMessage');
            const dataResults = document.getElementById('dataResults');
            const errorMessage = document.getElementById('errorMessage');
            const errorText = document.getElementById('errorText');

            // Drag and drop functionality
            uploadArea.addEventListener('dragover', function(e) {
                e.preventDefault();
                uploadArea.classList.add('border-blue-400', 'bg-blue-50');
            });

            uploadArea.addEventListener('dragleave', function(e) {
                e.preventDefault();
                uploadArea.classList.remove('border-blue-400', 'bg-blue-50');
            });

            uploadArea.addEventListener('drop', function(e) {
                e.preventDefault();
                uploadArea.classList.remove('border-blue-400', 'bg-blue-50');
                
                if (e.dataTransfer.files.length) {
                    fileInput.files = e.dataTransfer.files;
                    handleFileSelect(e.dataTransfer.files[0]);
                }
            });

            // File input change
            fileInput.addEventListener('change', function(e) {
                if (e.target.files.length) {
                    handleFileSelect(e.target.files[0]);
                }
            });

            function handleFileSelect(file) {
                // Validasi file
                if (!file.type.match('image.*')) {
                    showError('Silakan pilih file gambar (JPG, PNG)');
                    return;
                }

                if (file.size > 5 * 1024 * 1024) {
                    showError('Ukuran file maksimal 5MB');
                    return;
                }

                // Show preview
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    imagePreview.classList.remove('hidden');
                };
                reader.readAsDataURL(file);

                // Upload and process
                uploadAndProcess(file);
            }

            function uploadAndProcess(file) {
                const formData = new FormData();
                formData.append('fotoKtp', file);
                formData.append('_token', document.querySelector('input[name="_token"]').value);

                // Show loading
                loading.classList.remove('hidden');
                resultSection.classList.add('hidden');
                errorMessage.classList.add('hidden');

                fetch('{{ route("ktp.process") }}', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    loading.classList.add('hidden');

                    if (data.success) {
                        showResults(data);
                    } else {
                        showError(data.message);
                    }
                })
                .catch(error => {
                    loading.classList.add('hidden');
                    showError('Terjadi kesalahan jaringan: ' + error.message);
                });
            }

            function showResults(data) {
                resultMessage.textContent = data.message;
                
                // Clear previous results
                dataResults.innerHTML = '';

                // Add data fields
                const fields = [
                    { label: 'NIK', value: data.data.nik, icon: 'fingerprint' },
                    { label: 'Nama Lengkap', value: data.data.nama_lengkap, icon: 'user' },
                    { label: 'Jenis Kelamin', value: data.data.jenis_kelamin, icon: 'venus-mars' },
                    { label: 'Agama', value: data.data.agama, icon: 'pray' },
                    { label: 'Status Perkawinan', value: data.data.status_perkawinan, icon: 'heart' }
                ];

                fields.forEach(field => {
                    if (field.value) {
                        const fieldElement = document.createElement('div');
                        fieldElement.className = 'bg-gray-50 p-3 rounded-lg';
                        fieldElement.innerHTML = `
                            <div class="flex items-center space-x-2 text-sm">
                                <i class="fas fa-${field.icon} text-gray-400"></i>
                                <span class="font-medium text-gray-700">${field.label}:</span>
                                <span class="text-gray-900">${field.value}</span>
                            </div>
                        `;
                        dataResults.appendChild(fieldElement);
                    }
                });

                resultSection.classList.remove('hidden');
            }

            function showError(message) {
                errorText.textContent = message;
                errorMessage.classList.remove('hidden');
            }
        });
    </script>
</body>
</html>