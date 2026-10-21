
        let currentStep = PHP_CURRENT_STEP;
        let selectedJalur = '{{ $eregData->jalur_pendaftaran ?? '
        ' }}';
        let ktpValidated = false;

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            // Set jalur yang sudah dipilih
            if (selectedJalur) {
                document.querySelector(`input[name="jalur_pendaftaran"][value="${selectedJalur}"]`).checked = true;
                loadFileRequirements();
            }

            // Add event listeners untuk jalur selection
            document.querySelectorAll('.jalur-option input').forEach(radio => {
                radio.addEventListener('change', function() {
                    selectedJalur = this.value;
                    loadFileRequirements();
                });
            });
        });

        // Step Navigation
        function showStep(step) {
            document.querySelectorAll('.step-content').forEach(el => {
                el.classList.add('hidden');
            });
            document.getElementById(`step${step}`).classList.remove('hidden');
            currentStep = step;

            // Update progress indicators
            document.querySelectorAll('.step-indicator').forEach((indicator, index) => {
                const stepNumber = parseInt(indicator.dataset.step);
                if (stepNumber <= step) {
                    indicator.classList.add('bg-blue-600', 'border-blue-600', 'text-white');
                    indicator.classList.remove('border-gray-300', 'text-gray-500');
                } else {
                    indicator.classList.remove('bg-blue-600', 'border-blue-600', 'text-white');
                    indicator.classList.add('border-gray-300', 'text-gray-500');
                }
            });
        }

        // Step 1: Save Data Diri
        async function saveStep1() {
            const form = document.getElementById('step1Form');
            const formData = new FormData(form);

            // Validasi client-side
            if (!form.checkValidity()) {
                form.reportValidity();
                return;
            }

            showLoading();
            try {
                const response = await fetch('{{ route("mahasiswa.registration.store.step1") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    showStep(2);
                } else {
                    alert('Error: ' + result.message);
                }
            } catch (error) {
                alert('Terjadi kesalahan: ' + error.message);
            }
            hideLoading();
        }

        // Step 2: Save Program Studi
        async function saveStep2() {
            const form = document.getElementById('step2Form');
            const formData = new FormData(form);

            // Validasi client-side
            if (!form.checkValidity()) {
                form.reportValidity();
                return;
            }

            showLoading();
            try {
                const response = await fetch('{{ route("mahasiswa.registration.store.step2") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    showStep(3);
                } else {
                    alert('Error: ' + result.message);
                }
            } catch (error) {
                alert('Terjadi kesalahan: ' + error.message);
            }
            hideLoading();
        }

        // Load File Requirements
        async function loadFileRequirements() {
            if (!selectedJalur) {
                alert('Pilih jalur pendaftaran terlebih dahulu');
                return;
            }

            try {
                const response = await fetch(`/mahasiswa/registration/file-requirements?jalur=${selectedJalur}`);
                const result = await response.json();

                if (result.success) {
                    displayFileRequirements(result.requirements);
                    generateFileUploadFields(result.requirements);
                }
            } catch (error) {
                console.error('Error loading requirements:', error);
            }
        }

        // Display File Requirements
        function displayFileRequirements(requirements) {
            const container = document.getElementById('fileRequirements');
            let html = '<h3 class="text-lg font-medium text-gray-800 mb-4">Berkas yang Diperlukan:</h3><div class="grid grid-cols-1 md:grid-cols-2 gap-2">';

            requirements.forEach(req => {
                html += `
                    <div class="flex items-center space-x-2 text-sm">
                        <i class="fas fa-file text-blue-500"></i>
                        <span>${req.name}</span>
                        ${req.required ? '<span class="text-red-500 text-xs">*</span>' : ''}
                    </div>
                `;
            });

            html += '</div>';
            container.innerHTML = html;
        }

        // Generate File Upload Fields
        function generateFileUploadFields(requirements) {
            const container = document.getElementById('dynamicFileUploads');
            let html = '';

            requirements.forEach(req => {
                if (req.field_name !== 'file_ktp') { // KTP sudah ada di section khusus
                    html += `
                        <div class="p-4 border border-gray-200 rounded-lg">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                ${req.name} ${req.required ? '<span class="text-red-500">*</span>' : ''}
                            </label>
                            <p class="text-sm text-gray-600 mb-2">${req.description}</p>
                            <input type="file" name="${req.field_name}" 
                                accept=".pdf,.jpg,.jpeg,.png"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                    `;
                }
            });

            container.innerHTML = html;
        }

        async function validateKTP() {
            const ktpFile = document.querySelector('input[name="file_ktp"]').files[0];
            if (!ktpFile) {
                alert('Pilih file KTP terlebih dahulu');
                return;
            }

            const formData = new FormData();
            formData.append('file_ktp', ktpFile);

            showLoading();
            try {
                const response = await fetch('{{ route("mahasiswa.validate.ktp") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    ktpValidated = result.ktp_valid;
                    document.getElementById('ktpStatus').textContent = result.ktp_valid ? 'Valid' : 'Tidak Valid';
                    document.getElementById('ktpStatus').className =
                        `px-3 py-1 text-xs rounded-full ${result.ktp_valid ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}`;

                    if (result.ktp_valid) {
                        // Show KTP data preview
                        const preview = document.getElementById('ktpPreview');
                        preview.classList.remove('hidden');
                        // Gunakan data dari result.extracted_nik dan result.extracted_name
                        let nikText = result.extracted_nik ? `NIK: ${result.extracted_nik}` : 'NIK: Tidak terdeteksi';
                        let nameText = result.extracted_name ? `Nama: ${result.extracted_name}` : 'Nama: Tidak terdeteksi';

                        preview.innerHTML = `
                    <div class="bg-green-50 border border-green-200 rounded p-3 mt-2">
                        <h4 class="font-medium text-green-800">Data KTP Terdeteksi:</h4>
                        <p class="text-sm text-green-700">${nikText}</p>
                        <p class="text-sm text-green-700">${nameText}</p>
                    </div>
                `;
                    } else {
                        // Jika KTP tidak valid, sembunyikan preview
                        document.getElementById('ktpPreview').classList.add('hidden');
                    }
                } else {
                    alert('Validasi KTP gagal: ' + result.message);
                    // Jika gagal, pastikan preview disembunyikan
                    document.getElementById('ktpPreview').classList.add('hidden');
                }
            } catch (error) {
                alert('Terjadi kesalahan: ' + error.message);
                // Jika terjadi error, pastikan preview disembunyikan
                document.getElementById('ktpPreview').classList.add('hidden');
            }
            hideLoading();
        }

        // Upload Files
        async function uploadFiles() {
            if (!ktpValidated) {
                alert('Harap validasi KTP terlebih dahulu');
                return;
            }

            const form = document.getElementById('step3Form');
            const formData = new FormData(form);

            showLoading();
            try {
                const response = await fetch('{{ route("mahasiswa.upload") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    showStep(4);
                    loadConfirmationData();
                } else {
                    alert('Error: ' + result.message);
                }
            } catch (error) {
                alert('Terjadi kesalahan: ' + error.message);
            }
            hideLoading();
        }

        // Load Confirmation Data
        async function loadConfirmationData() {
            // Implementasi loading data untuk konfirmasi
            // ...
        }

        // Save Corrections
        async function saveCorrections() {
            showLoading();
            try {
                const response = await fetch('{{ route("mahasiswa.ocr.save") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({}) // Data koreksi bisa ditambahkan
                });

                const result = await response.json();

                if (result.success) {
                    showStep(5);
                } else {
                    alert('Error: ' + result.message);
                }
            } catch (error) {
                alert('Terjadi kesalahan: ' + error.message);
            }
            hideLoading();
        }

        // Utility Functions
        function showLoading() {
            document.getElementById('loading').classList.remove('hidden');
        }

        function hideLoading() {
            document.getElementById('loading').classList.add('hidden');
        }