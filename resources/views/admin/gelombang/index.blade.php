@extends('layouts.admin.app')

@section('title', 'Gelombang')

@section('content') 
<div class="cyberpunk-container">
    <div class="cyberpunk-header">
        <h1 class="cyberpunk-title">Data Gelombang</h1>
        <div class="cyberpunk-search-container">
            <div class="cyberpunk-search">
                <svg class="search-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M11 19C15.4183 19 19 15.4183 19 11C19 6.58172 15.4183 3 11 3C6.58172 3 3 6.58172 3 11C3 15.4183 6.58172 19 11 19Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M21 21L16.65 16.65" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <input type="text" id="searchInput" placeholder="Cari data gelombang..." class="cyberpunk-search-input">
                <div class="cyberpunk-filter">
                    <select id="gelombangFilter" class="cyberpunk-filter-select">
                        <option value="">Semua Gelombang</option>
                        <option value="1">Gelombang 1</option>
                        <option value="2">Gelombang 2</option>
                        <option value="3">Gelombang 3</option>
                        <option value="4">Gelombang 4</option>
                        <option value="5">Gelombang 5</option>
                        <option value="6">Gelombang 6</option>  
                        <option value="7">Gelombang 7</option>
                        <option value="8">Gelombang 8</option>
                        <option value="9">Gelombang 9</option>
                        <option value="10">Gelombang 10</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div class="cyberpunk-table-container">
        <table class="cyberpunk-table">
            <thead>
                <tr>
                    <th class="cyberpunk-th">Nama</th>
                    <th class="cyberpunk-th">Email</th>
                    <th class="cyberpunk-th">Telepon</th>
                    <th class="cyberpunk-th">Gelombang</th>
                    <th class="cyberpunk-th">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($data as $item)
                <tr class="cyberpunk-tr" data-gelombang="{{ $item->gelombang }}">
                    <td class="cyberpunk-td">{{ $item->name }}</td>
                    <td class="cyberpunk-td">{{ $item->email }}</td>
                    <td class="cyberpunk-td">{{ $item->phone }}</td>
                    <td class="cyberpunk-td">
                        <span class="gelombang-badge gelombang-{{ $item->gelombang }}">
                            {{ $item->gelombang }}
                        </span>
                    </td>
                    <td class="cyberpunk-td">
                        <form action="{{ route('admin.gelombang.update', ['id' => $item->id]) }}" method="POST" class="cyberpunk-form">
                            @csrf
                            @method('PUT')
                            <div class="cyberpunk-form-group">
                                <input type="number" name="gelombang" value="{{ $item->gelombang }}" min="1" max="10" class="cyberpunk-input">
                                <button type="submit" class="cyberpunk-btn cyberpunk-btn-primary">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M19 21H5C4.46957 21 3.96086 20.7893 3.58579 20.4142C3.21071 20.0391 3 19.5304 3 19V5C3 4.46957 3.21071 3.96086 3.58579 3.58579C3.96086 3.21071 4.46957 3 5 3H16L21 8V19C21 19.5304 20.7893 20.0391 20.4142 20.4142C20.0391 20.7893 19.5304 21 19 21Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                        <path d="M17 21V13H7V21" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                        <path d="M7 3V8H15" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                    Update
                                </button>
                            </div>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<style>
.cyberpunk-container {
    position: relative;
    padding: 1.5rem;
    background: linear-gradient(135deg, rgba(70, 95, 255, 0.05) 0%, rgba(122, 90, 248, 0.05) 100%);
    border-radius: 0.75rem;
    border: 1px solid rgba(70, 95, 255, 0.2);
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
    overflow: hidden;
}

.cyberpunk-container::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 2px;
    background: linear-gradient(90deg, #465fff, #7a5af8, #ee46bc);
    animation: scanline 3s linear infinite;
}

@keyframes scanline {
    0% { transform: translateX(-100%); }
    100% { transform: translateX(100%); }
}

.cyberpunk-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
    flex-wrap: wrap;
    gap: 1rem;
}

.cyberpunk-title {
    font-size: 1.875rem;
    font-weight: 700;
    background: linear-gradient(90deg, #465fff, #7a5af8);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    text-shadow: 0 0 10px rgba(70, 95, 255, 0.3);
}

.cyberpunk-search-container {
    flex: 1;
    max-width: 500px;
}

.cyberpunk-search {
    display: flex;
    align-items: center;
    background: rgba(16, 24, 40, 0.05);
    border-radius: 0.5rem;
    border: 1px solid rgba(70, 95, 255, 0.3);
    padding: 0.5rem;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.cyberpunk-search::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(135deg, rgba(70, 95, 255, 0.1) 0%, rgba(122, 90, 248, 0.1) 100%);
    z-index: -1;
}

.cyberpunk-search:focus-within {
    border-color: #465fff;
    box-shadow: 0 0 0 3px rgba(70, 95, 255, 0.2);
}

.search-icon {
    color: #465fff;
    margin: 0 0.75rem;
}

.cyberpunk-search-input {
    flex: 1;
    background: transparent;
    border: none;
    outline: none;
    color: #101828;
    font-size: 0.875rem;
    padding: 0.5rem 0;
}

.cyberpunk-filter {
    margin-left: 0.5rem;
}

.cyberpunk-filter-select {
    background: rgba(70, 95, 255, 0.1);
    border: 1px solid rgba(70, 95, 255, 0.3);
    border-radius: 0.375rem;
    padding: 0.5rem 1rem;
    color: #465fff;
    font-size: 0.875rem;
    outline: none;
    cursor: pointer;
    transition: all 0.3s ease;
}

.cyberpunk-filter-select:focus {
    border-color: #465fff;
    box-shadow: 0 0 0 2px rgba(70, 95, 255, 0.2);
}

.cyberpunk-table-container {
    overflow-x: auto;
    border-radius: 0.5rem;
    border: 1px solid rgba(70, 95, 255, 0.2);
    background: rgba(255, 255, 255, 0.7);
    backdrop-filter: blur(10px);
}

.cyberpunk-table {
    width: 100%;
    border-collapse: collapse;
}

.cyberpunk-th {
    background: linear-gradient(135deg, rgba(70, 95, 255, 0.1) 0%, rgba(122, 90, 248, 0.1) 100%);
    padding: 1rem;
    text-align: left;
    font-weight: 600;
    color: #465fff;
    border-bottom: 1px solid rgba(70, 95, 255, 0.2);
    position: relative;
}

.cyberpunk-th::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    width: 100%;
    height: 1px;
    background: linear-gradient(90deg, #465fff, #7a5af8, #ee46bc);
}

.cyberpunk-tr {
    transition: all 0.3s ease;
    border-bottom: 1px solid rgba(70, 95, 255, 0.1);
}

.cyberpunk-tr:hover {
    background: rgba(70, 95, 255, 0.05);
}

.cyberpunk-td {
    padding: 1rem;
    color: #344054;
}

.cyberpunk-form {
    display: flex;
    align-items: center;
}

.cyberpunk-form-group {
    display: flex;
    gap: 0.5rem;
}

.cyberpunk-input {
    width: 60px;
    padding: 0.5rem;
    border: 1px solid rgba(70, 95, 255, 0.3);
    border-radius: 0.375rem;
    background: rgba(255, 255, 255, 0.8);
    color: #344054;
    text-align: center;
    outline: none;
    transition: all 0.3s ease;
}

.cyberpunk-input:focus {
    border-color: #465fff;
    box-shadow: 0 0 0 2px rgba(70, 95, 255, 0.2);
}

.cyberpunk-btn {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    border: none;
    border-radius: 0.375rem;
    font-size: 0.875rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s ease;
    outline: none;
}

.cyberpunk-btn-primary {
    background: linear-gradient(135deg, #465fff, #7a5af8);
    color: white;
}

.cyberpunk-btn-primary:hover {
    background: linear-gradient(135deg, #3641f5, #6a55f7);
    box-shadow: 0 4px 12px rgba(70, 95, 255, 0.3);
}

.gelombang-badge {
    display: inline-block;
    padding: 0.25rem 0.75rem;
    border-radius: 1rem;
    font-size: 0.75rem;
    font-weight: 600;
    text-align: center;
    min-width: 2.5rem;
}

.gelombang-1 {
    background: rgba(70, 95, 255, 0.1);
    color: #465fff;
    border: 1px solid rgba(70, 95, 255, 0.3);
}

.gelombang-2 {
    background: rgba(122, 90, 248, 0.1);
    color: #7a5af8;
    border: 1px solid rgba(122, 90, 248, 0.3);
}

.gelombang-3 {
    background: rgba(238, 70, 188, 0.1);
    color: #ee46bc;
    border: 1px solid rgba(238, 70, 188, 0.3);
}

.gelombang-4 {
    background: rgba(251, 101, 20, 0.1);
    color: #fb6514;
    border: 1px solid rgba(251, 101, 20, 0.3);
}

/* Dark Mode Styles */
.dark .cyberpunk-container {
    background: linear-gradient(135deg, rgba(70, 95, 255, 0.1) 0%, rgba(122, 90, 248, 0.1) 100%);
    border: 1px solid rgba(70, 95, 255, 0.3);
}

.dark .cyberpunk-search {
    background: rgba(16, 24, 40, 0.3);
    border: 1px solid rgba(70, 95, 255, 0.4);
}

.dark .cyberpunk-search-input {
    color: #f9fafb;
}

.dark .cyberpunk-table-container {
    background: rgba(16, 24, 40, 0.5);
    border: 1px solid rgba(70, 95, 255, 0.3);
}

.dark .cyberpunk-td {
    color: #f9fafb;
}

.dark .cyberpunk-input {
    background: rgba(16, 24, 40, 0.5);
    color: #f9fafb;
    border: 1px solid rgba(70, 95, 255, 0.4);
}

/* Responsive Styles */
@media (max-width: 768px) {
    .cyberpunk-header {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .cyberpunk-search-container {
        max-width: 100%;
        width: 100%;
    }
    
    .cyberpunk-form-group {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .cyberpunk-table-container {
        font-size: 0.875rem;
    }
    
    .cyberpunk-th, .cyberpunk-td {
        padding: 0.75rem 0.5rem;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const gelombangFilter = document.getElementById('gelombangFilter');
    const tableRows = document.querySelectorAll('.cyberpunk-tr');
    
    function filterTable() {
        const searchTerm = searchInput.value.toLowerCase();
        const selectedGelombang = gelombangFilter.value;
        
        tableRows.forEach(row => {
            const name = row.cells[0].textContent.toLowerCase();
            const email = row.cells[1].textContent.toLowerCase();
            const phone = row.cells[2].textContent.toLowerCase();
            const gelombang = row.getAttribute('data-gelombang');
            
            const matchesSearch = name.includes(searchTerm) || 
                                email.includes(searchTerm) || 
                                phone.includes(searchTerm);
            
            const matchesGelombang = selectedGelombang === '' || gelombang === selectedGelombang;
            
            if (matchesSearch && matchesGelombang) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }
    
    searchInput.addEventListener('input', filterTable);
    gelombangFilter.addEventListener('change', filterTable);
});
</script>
@endsection