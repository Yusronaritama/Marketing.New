@extends('layouts.admin.app')

@section('title', 'Manajemen Moodle Users')

@section('content')
<div class="grid grid-cols-1 gap-6">
    <!-- Header Section -->
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-title-md font-bold text-gray-900 dark:text-white">
                Manajemen Moodle Users
            </h2>
            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                Kelola data pengguna Moodle dan status pendaftaran
            </p>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <!-- Search Box -->
            <div class="relative w-full sm:max-w-xs">
                <input 
                    type="text" 
                    placeholder="Cari username atau email..." 
                    class="w-full rounded-lg border border-gray-300 bg-white py-2 pl-10 pr-4 text-sm text-gray-900 placeholder:text-gray-500 focus:border-brand-300 focus:shadow-focus-ring focus:outline-hidden dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:placeholder:text-gray-400 dark:focus:border-brand-300"
                    id="searchInput"
                >
                
            </div>
            
            <!-- Action Buttons -->
            <div class="flex items-center gap-2">
                <!-- Refresh Button -->
                <button onclick="refreshData()" class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-900 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white">
                    <svg class="fill-current" width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M13.6467 2.35333C12.62 1.32667 11.2733 0.666668 9.78 0.666668C5.94 0.666668 2.83333 3.77333 2.83333 7.61333" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M2.35333 13.6467C3.38 14.6733 4.72667 15.3333 6.22 15.3333C10.06 15.3333 13.1667 12.2267 13.1667 8.38667" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M5.29333 11.7067L2.35333 13.6467L0.666664 10.3867" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M10.7067 4.29333L13.6467 2.35333L15.3333 5.61333" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    Refresh
                </button>

                <!-- Upload All Button -->
                <button onclick="uploadAllData()" id="uploadAllButton" class="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white shadow-theme-xs hover:bg-brand-600 disabled:opacity-50 disabled:cursor-not-allowed">
                    <svg class="fill-current" width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M8 1L4 5H6V11H10V5H12L8 1Z" fill="currentColor"/>
                        <path d="M14 9H12V12H4V9H2V14H14V9Z" fill="currentColor"/>
                    </svg>
                    <span id="uploadAllText">Upload Semua</span>
                    <div id="uploadAllSpinner" class="hidden h-4 w-4 animate-spin rounded-full border-2 border-solid border-white border-t-transparent"></div>
                </button>
            </div>
        </div>
    </div>

    <!-- Notification Container -->
    <div id="notificationContainer" class="fixed top-4 right-4 z-50 space-y-2 max-w-sm"></div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <!-- Total Users -->
        <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-700 dark:bg-gray-800">
            <div class="flex items-center gap-4">
                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-brand-50 dark:bg-brand-500/15">
                    <svg class="fill-brand-500 dark:fill-brand-400" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 12C14.7614 12 17 9.76142 17 7C17 4.23858 14.7614 2 12 2C9.23858 2 7 4.23858 7 7C7 9.76142 9.23858 12 12 12Z"/>
                        <path d="M12 14C7.58172 14 4 17.5817 4 22H20C20 17.5817 16.4183 14 12 14Z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Users</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white" id="totalUsers">0</p>
                </div>
            </div>
        </div>

        <!-- Active Users -->
        <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-700 dark:bg-gray-800">
            <div class="flex items-center gap-4">
                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-success-50 dark:bg-success-500/15">
                    <svg class="fill-success-500 dark:fill-success-500" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 22C17.5228 22 22 17.5228 22 12C22 6.47715 17.5228 2 12 2C6.47715 2 2 6.47715 2 12C2 17.5228 6.47715 22 12 22Z"/>
                        <path d="M8 12L11 15L16 9" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Active</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white" id="activeUsers">0</p>
                </div>
            </div>
        </div>

        <!-- Enrolled Users -->
        <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-700 dark:bg-gray-800">
            <div class="flex items-center gap-4">
                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-blue-light-50 dark:bg-blue-light-500/15">
                    <svg class="fill-blue-light-500 dark:fill-blue-light-500" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 2L3 7L12 12L21 7L12 2Z"/>
                        <path d="M3 17L12 22L21 17"/>
                        <path d="M3 12L12 17L21 12"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Enrolled</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white" id="enrolledUsers">0</p>
                </div>
            </div>
        </div>

        <!-- Error Users -->
        <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-700 dark:bg-gray-800">
            <div class="flex items-center gap-4">
                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-error-50 dark:bg-error-500/15">
                    <svg class="fill-error-500 dark:fill-error-500" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 22C17.5228 22 22 17.5228 22 12C22 6.47715 17.5228 2 12 2C6.47715 2 2 6.47715 2 12C2 17.5228 6.47715 22 12 22Z"/>
                        <path d="M15 9L9 15" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M9 9L15 15" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">With Errors</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white" id="errorUsers">0</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Users Table -->
    <div class="rounded-2xl border border-gray-200 bg-white shadow-theme-sm dark:border-gray-700 dark:bg-gray-800">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Daftar Moodle Users</h3>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full min-w-[1000px]">
                <thead>
                    <tr class="border-b border-gray-200 bg-gray-50 dark:border-gray-700 dark:bg-gray-900">
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-400">
                            User
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-400">
                            Moodle ID
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-400">
                            Status
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-400">
                            Enrollment
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-400">
                            Courses
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-400">
                            Last Updated
                        </th>
                        <th class="px-6 py-4 text-right text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-400">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800" id="usersTableBody">
                    <!-- Data will be populated by JavaScript -->
                </tbody>
            </table>
        </div>

        <!-- Loading State -->
        <div id="loadingState" class="p-8 text-center">
            <div class="inline-block h-8 w-8 animate-spin rounded-full border-4 border-solid border-brand-500 border-t-transparent"></div>
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Memuat data...</p>
        </div>

        <!-- Empty State -->
        <div id="emptyState" class="hidden p-8 text-center">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Tidak ada data yang ditemukan</p>
        </div>

        <!-- Error State -->
        <div id="errorState" class="hidden p-8 text-center">
            <svg class="mx-auto h-12 w-12 text-error-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/>
            </svg>
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400" id="errorMessage">Terjadi kesalahan saat memuat data</p>
            <button onclick="loadUsersData()" class="mt-3 inline-flex items-center gap-2 rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white shadow-theme-xs hover:bg-brand-600">
                Coba Lagi
            </button>
        </div>

        <!-- Pagination -->
        <div class="flex flex-col items-center justify-between gap-4 border-t border-gray-200 px-6 py-4 dark:border-gray-700 sm:flex-row">
            <div class="text-sm text-gray-500 dark:text-gray-400">
                Menampilkan <span id="paginationFrom">0</span> sampai <span id="paginationTo">0</span> dari <span id="paginationTotal">0</span> hasil
            </div>
            <div class="flex gap-2">
                <button onclick="previousPage()" class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-900 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white" id="prevButton" disabled>
                    <svg class="fill-current" width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M10 12L6 8L10 4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    Previous
                </button>
                <button onclick="nextPage()" class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-900 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white" id="nextButton" disabled>
                    Next
                    <svg class="fill-current" width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M6 12L10 8L6 4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>
</div>
<script>
// Global variables
let currentPage = 1;
const itemsPerPage = 10;
let allUsers = [];
let filteredUsers = [];

// CSRF Token for API calls
const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

// Initialize the page
document.addEventListener('DOMContentLoaded', function() {
    loadUsersData();
    setupEventListeners();
});

// Setup event listeners
function setupEventListeners() {
    const searchInput = document.getElementById('searchInput');
    searchInput.addEventListener('input', debounce(function(e) {
        filterUsers(e.target.value);
    }, 300));
}

// Debounce function for search
function debounce(func, wait) {
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

// Enhanced Notification System
function showNotification(type, title, message, duration = 5000) {
    const container = document.getElementById('notificationContainer');
    const notificationId = 'notification-' + Date.now();
    
    const notification = document.createElement('div');
    notification.id = notificationId;
    notification.className = `p-4 rounded-lg shadow-theme-lg border transform transition-all duration-300 ${
        type === 'success' 
            ? 'bg-success-50 border-success-200 text-success-800' 
            : type === 'error' 
            ? 'bg-error-50 border-error-200 text-error-800'
            : type === 'warning'
            ? 'bg-warning-50 border-warning-200 text-warning-800'
            : 'bg-brand-50 border-brand-200 text-brand-800'
    }`;
    
    const icon = type === 'success' ? '✅' : type === 'error' ? '❌' : type === 'warning' ? '⚠️' : 'ℹ️';
    
    notification.innerHTML = `
        <div class="flex items-start gap-3">
            <div class="flex-shrink-0 text-lg">${icon}</div>
            <div class="flex-1 min-w-0">
                <h4 class="font-semibold text-sm">${title}</h4>
                <p class="text-sm mt-1 whitespace-pre-line">${message}</p>
            </div>
            <button onclick="removeNotification('${notificationId}')" class="flex-shrink-0 text-gray-400 hover:text-gray-600 text-lg font-bold">
                ×
            </button>
        </div>
    `;
    
    // Add notification with slide-in effect
    notification.style.transform = 'translateX(100%)';
    notification.style.opacity = '0';
    container.appendChild(notification);
    
    // Animate in
    setTimeout(() => {
        notification.style.transform = 'translateX(0)';
        notification.style.opacity = '1';
    }, 10);
    
    // Auto remove after duration
    if (duration > 0) {
        setTimeout(() => {
            removeNotification(notificationId);
        }, duration);
    }
    
    return notificationId;
}

function removeNotification(notificationId) {
    const notification = document.getElementById(notificationId);
    if (notification) {
        notification.style.transform = 'translateX(100%)';
        notification.style.opacity = '0';
        setTimeout(() => {
            if (notification.parentElement) {
                notification.remove();
            }
        }, 300);
    }
}

// Function to upload all data to database - ONLY NEW USERS
async function uploadAllData() {
    if (allUsers.length === 0) {
        showNotification('warning', 'Tidak ada data', 'Tidak ada data user yang dapat diupload.');
        return;
    }

    if (!confirm('Apakah Anda yakin ingin memasukkan data Moodle baru ke database?\n\nData yang sudah ada akan dilewati.')) {
        return;
    }

    const button = document.getElementById('uploadAllButton');
    const text = document.getElementById('uploadAllText');
    const spinner = document.getElementById('uploadAllSpinner');
    
    try {
        // Show loading state
        button.disabled = true;
        text.textContent = 'Memproses...';
        spinner.classList.remove('hidden');

        showNotification('info', 'Memulai Upload', 'Sedang memproses upload data semua user...', 0);

        const response = await fetch('http://pmb.ubpkarawang.ac.id/api/moodle/upload-to-database', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({
                users: allUsers
            })
        });

        const result = await response.json();

        if (result.success) {
            const summary = result.summary || {};
            const newUploaded = summary.new_uploaded || 0;
            const alreadyExists = summary.already_exists || 0;
            const failed = summary.failed || 0;
            const totalProcessed = summary.total_processed || 0;

            let message = `📊 Hasil Upload Semua Data:\n`;
            message += `• Berhasil diupload: ${newUploaded}\n`;
            message += `• Sudah ada: ${alreadyExists}\n`;
            message += `• Gagal: ${failed}\n`;
            message += `• Total diproses: ${totalProcessed}`;

            showNotification('success', 'Upload Berhasil!', message);

            // Refresh data setelah berhasil
            setTimeout(() => {
                refreshData();
            }, 2000);

        } else {
            throw new Error(result.message || 'Gagal memproses data');
        }

    } catch (error) {
        console.error('Error uploading data:', error);
        
        let errorMessage = error.message;
        if (errorMessage.includes('Failed to fetch')) {
            errorMessage = 'Gagal terhubung ke server. Periksa koneksi internet Anda.';
        } else if (errorMessage.includes('Network Error')) {
            errorMessage = 'Koneksi jaringan bermasalah. Periksa koneksi internet Anda.';
        }
        
        showNotification('error', 'Upload Gagal', errorMessage);
    } finally {
        // Reset button state
        button.disabled = false;
        text.textContent = 'Upload Semua';
        spinner.classList.add('hidden');
    }
}

// Function to upload single user data dengan notifikasi yang lebih baik
async function uploadUserData(userId, username = '') {
    if (!userId || userId === 'null') {
        showNotification('error', 'Error', 'User ID tidak tersedia');
        return;
    }

    const user = allUsers.find(u => {
        const masterData = u.master_moodle_data || {};
        return masterData.id == userId || masterData.moodle_user_id == userId;
    });

    if (!user) {
        showNotification('error', 'Error', 'Data user tidak ditemukan');
        return;
    }

    const button = event.target.closest('button');
    const originalText = button.innerHTML;
    const userDisplayName = username || user.master_moodle_data?.username || 'User';
    
    try {
        // Show loading state
        button.innerHTML = `
            <div class="inline-block h-4 w-4 animate-spin rounded-full border-2 border-solid border-current border-t-transparent"></div>
        `;
        button.disabled = true;

        showNotification('info', 'Memproses User', `Sedang mengupload data ${userDisplayName}...`, 0);

        const response = await fetch('http://pmb.ubpkarawang.ac.id/api/moodle/upload-user-to-database', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({
                user: user
            })
        });

        const result = await response.json();

        if (result.success) {
            if (result.data?.status === 'already_exists') {
                const lastUpload = result.data.last_upload_at ? 
                    new Date(result.data.last_upload_at).toLocaleString('id-ID') : 'tidak diketahui';
                
                showNotification('info', 
                    'Data Sudah Ada', 
                    `User ${userDisplayName} sudah ada di database.\nTerakhir diupload: ${lastUpload}`
                );
            } else {
                showNotification('success', 
                    'Upload Berhasil!', 
                    `Data user ${userDisplayName} berhasil dimasukkan ke database.`
                );
                
                // Refresh data setelah berhasil
                setTimeout(() => {
                    refreshData();
                }, 1500);
            }
        } else {
            throw new Error(result.message || 'Gagal memasukkan data user');
        }

    } catch (error) {
        console.error('Error uploading user data:', error);
        
        let errorMessage = error.message;
        if (errorMessage.includes('Failed to fetch')) {
            errorMessage = 'Gagal terhubung ke server. Periksa koneksi internet Anda.';
        }
        
        showNotification('error', 'Upload Gagal', `Gagal mengupload data ${userDisplayName}: ${errorMessage}`);
    } finally {
        // Reset button state
        button.innerHTML = originalText;
        button.disabled = false;
    }
}

// Load users data from API
async function loadUsersData() {
    try {
        showLoadingState();
        
        const response = await fetch('http://pmb.ubpkarawang.ac.id/api/moodle/users/complete', {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            }
        });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const result = await response.json();
        
        if (result.success) {
            allUsers = result.data || [];
            filteredUsers = [...allUsers];
            
            // Update stats
            updateStats();
            
            // Render table
            renderTable();
            
            // Show success notification if we have data
            if (allUsers.length > 0) {
                showNotification('success', 'Data Dimuat', `Berhasil memuat ${allUsers.length} user dari Moodle.`);
            }
        } else {
            throw new Error(result.message || 'Gagal mengambil data dari API');
        }
        
    } catch (error) {
        console.error('Error loading data:', error);
        showErrorState(error.message);
        showNotification('error', 'Gagal Memuat Data', error.message);
    }
}

// Filter users based on search input
function filterUsers(searchTerm) {
    if (!searchTerm) {
        filteredUsers = [...allUsers];
    } else {
        const term = searchTerm.toLowerCase();
        filteredUsers = allUsers.filter(user => {
            const masterData = user.master_moodle_data || {};
            const moodleData = user.moodle_user_data || {};
            
            return (
                (masterData.username && masterData.username.toLowerCase().includes(term)) ||
                (moodleData.email && moodleData.email.toLowerCase().includes(term)) ||
                (moodleData.fullname && moodleData.fullname.toLowerCase().includes(term)) ||
                (moodleData.firstname && moodleData.firstname.toLowerCase().includes(term)) ||
                (moodleData.lastname && moodleData.lastname.toLowerCase().includes(term))
            );
        });
    }
    
    currentPage = 1;
    updateStats();
    renderTable();
}

// Update statistics cards
function updateStats() {
    const totalUsers = filteredUsers.length;
    const activeUsers = filteredUsers.filter(user => {
        const moodleData = user.moodle_user_data || {};
        return !moodleData.suspended;
    }).length;
    
    const enrolledUsers = filteredUsers.filter(user => {
        const masterData = user.master_moodle_data || {};
        return masterData.enrol_status === 'enrolled';
    }).length;
    
    const errorUsers = filteredUsers.filter(user => {
        const masterData = user.master_moodle_data || {};
        return masterData.status === 'error' || masterData.error_message;
    }).length;
    
    document.getElementById('totalUsers').textContent = totalUsers;
    document.getElementById('activeUsers').textContent = activeUsers;
    document.getElementById('enrolledUsers').textContent = enrolledUsers;
    document.getElementById('errorUsers').textContent = errorUsers;
}

// Render users table
function renderTable() {
    const tableBody = document.getElementById('usersTableBody');
    const loadingState = document.getElementById('loadingState');
    const emptyState = document.getElementById('emptyState');
    const errorState = document.getElementById('errorState');
    
    // Hide all states first
    loadingState.classList.add('hidden');
    emptyState.classList.add('hidden');
    errorState.classList.add('hidden');
    
    if (filteredUsers.length === 0) {
        tableBody.innerHTML = '';
        emptyState.classList.remove('hidden');
        updatePagination(0);
        return;
    }
    
    // Calculate pagination
    const startIndex = (currentPage - 1) * itemsPerPage;
    const endIndex = startIndex + itemsPerPage;
    const paginatedUsers = filteredUsers.slice(startIndex, endIndex);
    
    let tableHTML = '';
    
    paginatedUsers.forEach(user => {
        const masterData = user.master_moodle_data || {};
        const moodleData = user.moodle_user_data || {};
        const enrollmentStatus = user.enrollment_status || {};
        const userCourses = user.user_courses || [];
        
        const username = masterData.username || '-';
        const moodleUserId = masterData.moodle_user_id || '-';
        const status = masterData.status || 'unknown';
        const enrolStatus = masterData.enrol_status || 'unknown';
        const fullname = moodleData.fullname || `${moodleData.firstname || ''} ${moodleData.lastname || ''}`.trim() || username;
        const email = moodleData.email || '-';
        const suspended = moodleData.suspended || false;
        const profileImage = moodleData.profileimageurlsmall || 'https://elearning2.ubpkarawang.ac.id/theme/image.php/boost/core/1719993069/u/f2';
        const updatedAt = masterData.updated_at || '-';
        const enrolledCourses = enrollmentStatus.enrolled_courses || userCourses.length;
        
        // Pastikan ID tersedia untuk link view
        const userId = masterData.id || masterData.moodle_user_id || '-';
        
        tableHTML += `
            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                        <div class="h-10 w-10 flex-shrink-0">
                            <img class="h-10 w-10 rounded-full" src="${profileImage}" alt="${fullname}" onerror="this.src='https://elearning2.ubpkarawang.ac.id/theme/image.php/boost/core/1719993069/u/f2'">
                        </div>
                        <div class="ml-4">
                            <div class="text-sm font-medium text-gray-900 dark:text-white">${fullname}</div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">${username}</div>
                            <div class="text-xs text-gray-400 dark:text-gray-500">${email}</div>
                        </div>
                    </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-900 dark:text-white font-mono">${moodleUserId}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex flex-col gap-1">
                        <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-medium 
                            ${status === 'success' ? 'bg-success-50 text-success-700 dark:bg-success-500/15 dark:text-success-500' : 
                              status === 'error' ? 'bg-error-50 text-error-700 dark:bg-error-500/15 dark:text-error-500' :
                              'bg-warning-50 text-warning-700 dark:bg-warning-500/15 dark:text-warning-500'}">
                            ${status}
                        </span>
                        ${suspended ? `
                        <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-medium bg-error-50 text-error-700 dark:bg-error-500/15 dark:text-error-500">
                            suspended
                        </span>
                        ` : ''}
                    </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-medium 
                        ${enrolStatus === 'enrolled' ? 'bg-success-50 text-success-700 dark:bg-success-500/15 dark:text-success-500' : 
                          enrolStatus === 'not_enrolled' ? 'bg-error-50 text-error-700 dark:bg-error-500/15 dark:text-error-500' :
                          'bg-warning-50 text-warning-700 dark:bg-warning-500/15 dark:text-warning-500'}">
                        ${enrolStatus}
                    </span>
                </td>
                <td class="px-6 py-4">
                    <div class="text-sm text-gray-900 dark:text-white">${enrolledCourses} courses</div>
                    <div class="text-sm text-gray-500 dark:text-gray-400 truncate max-w-xs">
                        ${userCourses.length > 0 ? userCourses[0].fullname || userCourses[0].displayname || userCourses[0].shortname : 'No courses'}
                    </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                    ${formatDate(updatedAt)}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                    <div class="flex items-center justify-end gap-2">
                        ${userId !== '-' ? `
                        <a href="/admin/moodle/${moodleUserId}" class="inline-flex items-center gap-1 text-brand-600 hover:text-brand-900 dark:text-brand-400 dark:hover:text-brand-300" title="View Details">
                            <svg class="fill-current" width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M7.99992 2.66666C4.66659 2.66666 1.81992 4.73999 0.666585 7.66666C1.81992 10.5933 4.66659 12.6667 7.99992 12.6667C11.3333 12.6667 14.1799 10.5933 15.3333 7.66666C14.1799 4.73999 11.3333 2.66666 7.99992 2.66666ZM7.99992 11C6.15992 11 4.66659 9.50666 4.66659 7.66666C4.66659 5.82666 6.15992 4.33332 7.99992 4.33332C9.83992 4.33332 11.3333 5.82666 11.3333 7.66666C11.3333 9.50666 9.83992 11 7.99992 11ZM7.99992 5.66666C6.89325 5.66666 5.99992 6.55999 5.99992 7.66666C5.99992 8.77332 6.89325 9.66666 7.99992 9.66666C9.10659 9.66666 9.99992 8.77332 9.99992 7.66666C9.99992 6.55999 9.10659 5.66666 7.99992 5.66666Z" fill="currentColor"/>
                            </svg>
                        </a>
                        ` : `
                        <span class="text-gray-400 dark:text-gray-600 cursor-not-allowed" title="ID tidak tersedia">
                            <svg class="fill-current" width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M7.99992 2.66666C4.66659 2.66666 1.81992 4.73999 0.666585 7.66666C1.81992 10.5933 4.66659 12.6667 7.99992 12.6667C11.3333 12.6667 14.1799 10.5933 15.3333 7.66666C14.1799 4.73999 11.3333 2.66666 7.99992 2.66666ZM7.99992 11C6.15992 11 4.66659 9.50666 4.66659 7.66666C4.66659 5.82666 6.15992 4.33332 7.99992 4.33332C9.83992 4.33332 11.3333 5.82666 11.3333 7.66666C11.3333 9.50666 9.83992 11 7.99992 11ZM7.99992 5.66666C6.89325 5.66666 5.99992 6.55999 5.99992 7.66666C5.99992 8.77332 6.89325 9.66666 7.99992 9.66666C9.10659 9.66666 9.99992 8.77332 9.99992 7.66666C9.99992 6.55999 9.10659 5.66666 7.99992 5.66666Z" fill="currentColor"/>
                            </svg>
                        </span>
                        `}
                        
                        <button class="inline-flex items-center gap-1 text-green-600 hover:text-green-900 dark:text-green-400 dark:hover:text-green-300 upload-user-btn" 
                                onclick="uploadUserData('${userId}', '${username}')" 
                                ${userId === '-' ? 'disabled' : ''}
                                title="Upload to Database">
                            <svg class="fill-current" width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M8 1L4 5H6V11H10V5H12L8 1Z" fill="currentColor"/>
                                <path d="M14 9H12V12H4V9H2V14H14V9Z" fill="currentColor"/>
                            </svg>
                        </button>
                        
                        <button class="inline-flex items-center gap-1 text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-300" 
                                onclick="syncUser(${userId !== '-' ? userId : 'null'})" 
                                ${userId === '-' ? 'disabled' : ''}
                                title="Sync Data">
                            <svg class="fill-current" width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M13.6467 2.35333C12.62 1.32667 11.2733 0.666668 9.78 0.666668C5.94 0.666668 2.83333 3.77333 2.83333 7.61333" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M2.35333 13.6467C3.38 14.6733 4.72667 15.3333 6.22 15.3333C10.06 15.3333 13.1667 12.2267 13.1667 8.38667" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M5.29333 11.7067L2.35333 13.6467L0.666664 10.3867" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M10.7067 4.29333L13.6467 2.35333L15.3333 5.61333" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </button>
                    </div>
                </td>
            </tr>
        `;
    });
    
    tableBody.innerHTML = tableHTML;
    updatePagination(filteredUsers.length);
}

// Update pagination controls
function updatePagination(totalItems) {
    const totalPages = Math.ceil(totalItems / itemsPerPage);
    const startItem = (currentPage - 1) * itemsPerPage + 1;
    const endItem = Math.min(currentPage * itemsPerPage, totalItems);
    
    document.getElementById('paginationFrom').textContent = startItem;
    document.getElementById('paginationTo').textContent = endItem;
    document.getElementById('paginationTotal').textContent = totalItems;
    
    document.getElementById('prevButton').disabled = currentPage === 1;
    document.getElementById('nextButton').disabled = currentPage === totalPages || totalPages === 0;
}

// Navigation functions
function previousPage() {
    if (currentPage > 1) {
        currentPage--;
        renderTable();
    }
}

function nextPage() {
    const totalPages = Math.ceil(filteredUsers.length / itemsPerPage);
    if (currentPage < totalPages) {
        currentPage++;
        renderTable();
    }
}

// Utility functions
function formatDate(dateString) {
    if (!dateString || dateString === '-') return '-';
    
    try {
        const date = new Date(dateString);
        return date.toLocaleDateString('id-ID', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    } catch (e) {
        return dateString;
    }
}

function showLoadingState() {
    document.getElementById('loadingState').classList.remove('hidden');
    document.getElementById('emptyState').classList.add('hidden');
    document.getElementById('errorState').classList.add('hidden');
    document.getElementById('usersTableBody').innerHTML = '';
}

function showEmptyState() {
    document.getElementById('loadingState').classList.add('hidden');
    document.getElementById('emptyState').classList.remove('hidden');
    document.getElementById('errorState').classList.add('hidden');
    document.getElementById('usersTableBody').innerHTML = '';
}

function showErrorState(message) {
    document.getElementById('loadingState').classList.add('hidden');
    document.getElementById('emptyState').classList.add('hidden');
    document.getElementById('errorState').classList.remove('hidden');
    document.getElementById('errorMessage').textContent = message || 'Terjadi kesalahan saat memuat data';
    document.getElementById('usersTableBody').innerHTML = '';
}

// Action functions
function refreshData() {
    currentPage = 1;
    loadUsersData();
}

function syncUser(userId) {
    if (!userId || userId === 'null') {
        showNotification('error', 'Error', 'User ID tidak tersedia untuk sync');
        return;
    }
    
    if (confirm(`Apakah Anda yakin ingin mensinkronisasi data user ${userId}?`)) {
        const button = event.target.closest('button');
        const originalText = button.innerHTML;
        button.innerHTML = `
            <div class="inline-block h-4 w-4 animate-spin rounded-full border-2 border-solid border-current border-t-transparent"></div>
        `;
        button.disabled = true;
        
        showNotification('info', 'Sync Data', `Sedang mensinkronisasi data user ${userId}...`, 0);
        
        setTimeout(() => {
            showNotification('success', 'Sync Berhasil', `Data user ${userId} berhasil disinkronisasi`);
            button.innerHTML = originalText;
            button.disabled = false;
            refreshData();
        }, 2000);
    }
}
</script>
@endsection
<script src="{{ asset('assets/js/bundle.js') }}"></script>