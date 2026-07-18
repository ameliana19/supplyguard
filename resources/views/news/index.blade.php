@extends('layouts.app')

@section('title', 'Berita Logistik - SupplyGuard')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1 text-dark">📰 Berita Logistik Rantai Pasok</h2>
            <p class="text-muted mb-0">Dapatkan informasi tentang gangguan rantai pasok real-time, berita pelabuhan, dan intelijen pengiriman</p>
        </div>
        @if(Auth::user() && Auth::user()->role === 'administrator')
        <div class="ms-auto d-flex gap-2">
            <button onclick="openCreateModal()" class="btn btn-dark" id="add-news-btn">
                <i class="bi bi-plus-lg"></i> Tambah Berita
            </button>
            <button onclick="syncNews()" class="btn btn-outline-dark" id="sync-news-btn">
                <i class="bi bi-cloud-arrow-down" id="sync-news-icon"></i> <span id="sync-news-text">Sinkronkan API Berita</span>
            </button>
        </div>
        @endif
    </div>

    <!-- Toast Notification -->
    <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 11">
        <div id="statusToast" class="toast align-items-center text-white border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body" id="toast-message"></div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    </div>

    <!-- Search/Filter Controls -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0 text-muted">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" id="search-news" class="form-control border-start-0" placeholder="Cari judul berita atau penulis..." oninput="applyFilters()">
                    </div>
                </div>
                <div class="col-md-3">
                    <select id="category-filter" class="form-select" onchange="applyFilters()">
                        <option value="">Semua Kategori</option>
                        <option value="Logistik">Logistik</option>
                        <option value="Rantai Pasok">Rantai Pasok</option>
                        <option value="Pelabuhan">Pelabuhan</option>
                        <option value="Ekspor">Ekspor</option>
                        <option value="Impor">Impor</option>
                        <option value="Perdagangan Internasional">Perdagangan Internasional</option>
                        <option value="Maritim">Maritim</option>
                        <option value="Cuaca Pengiriman">Cuaca Pengiriman</option>
                        <option value="Ekonomi Indonesia">Ekonomi Indonesia</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select id="country-filter" class="form-select" onchange="applyFilters()">
                        <option value="">Semua Negara</option>
                        @foreach($countries as $c)
                            @php
                                $indoNames = [
                                    'Indonesia' => 'Indonesia',
                                    'Malaysia' => 'Malaysia',
                                    'Singapore' => 'Singapura',
                                    'Japan' => 'Jepang',
                                    'China' => 'China',
                                    'Saudi Arabia' => 'Arab Saudi',
                                    'United States' => 'Amerika Serikat',
                                    'United Kingdom' => 'Inggris',
                                    'Germany' => 'Jerman',
                                    'France' => 'Prancis',
                                    'Australia' => 'Australia',
                                    'Canada' => 'Kanada',
                                    'India' => 'India',
                                    'Brazil' => 'Brasil',
                                    'South Africa' => 'Afrika Selatan',
                                    'South Korea' => 'Korea Selatan',
                                    'Thailand' => 'Thailand',
                                    'Vietnam' => 'Vietnam',
                                    'Philippines' => 'Filipina',
                                    'Netherlands' => 'Belanda',
                                    'Belgium' => 'Belgia',
                                    'Switzerland' => 'Swiss',
                                    'Italy' => 'Italia',
                                    'Spain' => 'Spanyol',
                                    'New Zealand' => 'Selandia Baru',
                                    'Saudi Arabia' => 'Arab Saudi',
                                    'Turkey' => 'Turki',
                                ];
                                $displayName = $indoNames[$c->name] ?? $c->name;
                            @endphp
                            <option value="{{ $c->id }}">{{ $displayName }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 d-grid">
                    <button onclick="resetFilters()" class="btn btn-outline-dark">
                        <i class="bi bi-x-circle"></i> Hapus Filter
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- News List Grid -->
    <div class="row g-4 mb-4" id="news-grid-container">
        <!-- Dynamic news cards will load here -->
        <div class="col-12 text-center py-5 text-muted">
            Memuat berita...
        </div>
    </div>

    <!-- Pagination -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="text-muted small" id="pagination-info">
            Menampilkan 0 hingga 0 dari 0 entri
        </div>
        <nav aria-label="Page navigation">
            <ul class="pagination pagination-sm mb-0" id="pagination-links">
            </ul>
        </nav>
    </div>

    <!-- Modal Form (Tambah / Ubah / Copy) -->
    <div class="modal fade" id="newsFormModal" tabindex="-1" aria-labelledby="newsFormModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-dark text-white">
                    <h5 class="modal-title" id="newsFormModalLabel">Form Berita</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="newsForm">
                        <input type="hidden" id="news-id">
                        <input type="hidden" id="form-action" value="create"> <!-- create, edit, copy -->
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label for="form-title" class="form-label fw-semibold">Judul Berita</label>
                                <input type="text" class="form-control" id="form-title" required>
                            </div>
                            <div class="col-md-6">
                                <label for="form-category" class="form-label fw-semibold">Kategori</label>
                                <select class="form-select" id="form-category" required>
                                    <option value="">Pilih Kategori</option>
                                    <option value="Logistik">Logistik</option>
                                    <option value="Rantai Pasok">Rantai Pasok</option>
                                    <option value="Pelabuhan">Pelabuhan</option>
                                    <option value="Ekspor">Ekspor</option>
                                    <option value="Impor">Impor</option>
                                    <option value="Perdagangan Internasional">Perdagangan Internasional</option>
                                    <option value="Maritim">Maritim</option>
                                    <option value="Cuaca Pengiriman">Cuaca Pengiriman</option>
                                    <option value="Ekonomi Indonesia">Ekonomi Indonesia</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="form-country" class="form-label fw-semibold">Negara</label>
                                <select class="form-select" id="form-country" required>
                                    <option value="">Pilih Negara</option>
                                    @foreach($countries as $c)
                                        <option value="{{ $c->id }}">{{ $c->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="form-author" class="form-label fw-semibold">Penulis</label>
                                <input type="text" class="form-control" id="form-author" placeholder="Nama penulis/redaksi">
                            </div>
                            <div class="col-md-6">
                                <label for="form-date" class="form-label fw-semibold">Tanggal Terbit</label>
                                <input type="date" class="form-control" id="form-date" required>
                            </div>
                            <div class="col-md-12">
                                <label for="form-image" class="form-label fw-semibold">URL Gambar</label>
                                <input type="text" class="form-control" id="form-image" placeholder="https://example.com/image.jpg">
                            </div>
                            <div class="col-md-12">
                                <label for="form-url" class="form-label fw-semibold">URL Sumber Berita Asli</label>
                                <input type="text" class="form-control" id="form-url" placeholder="https://example.com/news-source">
                            </div>
                            <div class="col-md-12">
                                <label for="form-summary" class="form-label fw-semibold">Ringkasan</label>
                                <textarea class="form-control" id="form-summary" rows="3" required placeholder="Tulis ringkasan singkat berita..."></textarea>
                            </div>
                            <div class="col-md-12">
                                <label for="form-content" class="form-label fw-semibold">Isi Konten Lengkap</label>
                                <textarea class="form-control" id="form-content" rows="5" placeholder="Tulis konten lengkap berita (opsional)..."></textarea>
                            </div>
                        </div>
                        <div class="d-flex justify-content-end gap-2 mt-4">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-dark" id="save-news-btn">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Detail -->
    <div class="modal fade" id="newsDetailModal" tabindex="-1" aria-labelledby="newsDetailModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-dark text-white">
                    <h5 class="modal-title" id="newsDetailModalLabel">Detail Berita</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="badge bg-primary" id="detail-category">Kategori</span>
                        <small class="text-muted" id="detail-meta">Tanggal • Penulis • Negara</small>
                    </div>
                    <h3 class="fw-bold mb-4 text-dark" id="detail-title">Judul Berita</h3>
                    <div id="detail-image-container" class="mb-4 text-center d-none">
                        <img src="" id="detail-image" class="img-fluid rounded" style="max-height: 400px; object-fit: cover; width: 100%;">
                    </div>
                    <div class="mb-4">
                        <h5 class="fw-semibold text-dark mb-2">Ringkasan:</h5>
                        <p class="text-muted leading-relaxed" id="detail-summary">Ringkasan berita.</p>
                    </div>
                    <hr>
                    <div class="mb-4">
                        <h5 class="fw-semibold text-dark mb-2">Konten Lengkap:</h5>
                        <div class="text-secondary leading-relaxed" id="detail-content" style="white-space: pre-wrap;">Konten lengkap.</div>
                    </div>
                    <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                        <a href="#" id="detail-url" target="_blank" class="btn btn-outline-dark d-none">
                            <i class="bi bi-box-arrow-up-right"></i> Baca Berita Asli
                        </a>
                        <button type="button" class="btn btn-dark" data-bs-dismiss="modal">Tutup</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const userRole = "{{ Auth::user() ? Auth::user()->role : 'user' }}";
    let newsDataList = [];
    let filteredData = [];
    let currentPage = 1;
    const itemsPerPage = 8; // 8 items for a clean 2x4 card grid

    document.addEventListener("DOMContentLoaded", function() {
        const urlParams = new URLSearchParams(window.location.search);
        const countryIdParam = urlParams.get('country_id');
        if (countryIdParam) {
            const countryFilter = document.getElementById('country-filter');
            if (countryFilter) {
                countryFilter.value = countryIdParam;
            }
        }
        fetchNews();
    });

    function showToast(message, type = 'success') {
        const toastEl = document.getElementById('statusToast');
        const toastMsg = document.getElementById('toast-message');
        toastMsg.innerText = message;
        toastEl.className = `toast align-items-center text-white border-0 bg-${type === 'success' ? 'success' : 'danger'}`;
        const toast = new bootstrap.Toast(toastEl);
        toast.show();
    }

    function fetchNews() {
        const container = document.getElementById('news-grid-container');
        container.innerHTML = '<div class="col-12 text-center py-5"><span class="spinner-border spinner-border-sm text-dark me-2"></span>Memuat berita...</div>';
        
        fetch('/api/news')
            .then(res => res.json())
            .then(response => {
                if (response.success) {
                    newsDataList = response.data.data; // Note: api news index returns standard Laravel paginated response structure
                    applyFilters();
                } else {
                    container.innerHTML = '<div class="col-12 text-center py-5 text-danger">Gagal memuat berita.</div>';
                }
            })
            .catch(err => {
                console.error(err);
                container.innerHTML = '<div class="col-12 text-center py-5 text-danger">Terjadi kesalahan saat memuat berita.</div>';
            });
    }

    function syncNews() {
        const btn = document.getElementById('sync-news-btn');
        const icon = document.getElementById('sync-news-icon');
        const text = document.getElementById('sync-news-text');

        btn.disabled = true;
        icon.className = 'spinner-border spinner-border-sm me-2';
        text.innerText = 'Menyinkronkan...';

        fetch('/api/news/sync', { method: 'POST' })
            .then(res => res.json())
            .then(response => {
                btn.disabled = false;
                icon.className = 'bi bi-cloud-arrow-down';
                text.innerText = 'Sinkronkan API Berita';
                
                if (response.success) {
                    showToast(`${response.message}`, 'success');
                    fetchNews();
                } else {
                    showToast(`${response.message}`, 'error');
                }
            })
            .catch(err => {
                console.error(err);
                btn.disabled = false;
                icon.className = 'bi bi-cloud-arrow-down';
                text.innerText = 'Sinkronkan API Berita';
                showToast('Gagal menyinkronkan berita. Periksa koneksi internet Anda.', 'error');
            });
    }

    function applyFilters() {
        const searchVal = document.getElementById('search-news').value.toLowerCase().trim();
        const categoryVal = document.getElementById('category-filter').value;
        const countryVal = document.getElementById('country-filter').value;

        // Filter by search, category, and country strictly
        const exactFiltered = newsDataList.filter(item => {
            const matchesSearch = !searchVal || 
                (item.title && item.title.toLowerCase().includes(searchVal)) ||
                (item.title_id && item.title_id.toLowerCase().includes(searchVal)) ||
                (item.summary && item.summary.toLowerCase().includes(searchVal)) ||
                (item.summary_id && item.summary_id.toLowerCase().includes(searchVal)) ||
                (item.author && item.author.toLowerCase().includes(searchVal));

            const matchesCategory = !categoryVal || item.category === categoryVal;
            const matchesCountry = !countryVal || String(item.country_id) === String(countryVal);

            return matchesSearch && matchesCategory && matchesCountry;
        });

        // Deduplicate: Satu berita hanya boleh muncul satu kali.
        // Jangan menampilkan artikel yang sama dengan ID, judul, atau URL yang sama.
        // Jika berita termasuk beberapa kategori, tetap hanya tampil satu kali pada hasil pencarian.
        const seenIds = new Set();
        const seenTitles = new Set();
        const seenUrls = new Set();
        const deduplicated = [];

        for (const item of exactFiltered) {
            const cleanTitle = (item.title || item.title_id || '').trim().toLowerCase();
            const cleanUrl = (item.url || '').trim().toLowerCase();
            
            if (seenIds.has(item.id) || seenTitles.has(cleanTitle) || seenUrls.has(cleanUrl)) {
                continue;
            }
            
            seenIds.add(item.id);
            seenTitles.add(cleanTitle);
            seenUrls.add(cleanUrl);
            deduplicated.push(item);
        }

        filteredData = deduplicated;
        currentPage = 1;
        renderGrid();
    }

    function resetFilters() {
        document.getElementById('search-news').value = '';
        document.getElementById('category-filter').value = '';
        document.getElementById('country-filter').value = '';
        applyFilters();
    }

    function renderGrid() {
        const container = document.getElementById('news-grid-container');
        container.innerHTML = '';

        if (newsDataList.length === 0) {
            container.innerHTML = '<div class="col-12 text-center py-5 text-muted">Belum ada berita logistik tersedia.</div>';
            document.getElementById('pagination-info').innerText = 'Menampilkan 0 hingga 0 dari 0 entri';
            document.getElementById('pagination-links').innerHTML = '';
            return;
        }

        if (filteredData.length === 0) {
            const countryVal = document.getElementById('country-filter').value;
            const emptyMsg = countryVal ? 'Belum ada berita untuk negara ini' : 'Tidak ada berita logistik yang cocok dengan filter.';
            container.innerHTML = `<div class="col-12 text-center py-5 text-muted">${emptyMsg}</div>`;
            document.getElementById('pagination-info').innerText = 'Menampilkan 0 hingga 0 dari 0 entri';
            document.getElementById('pagination-links').innerHTML = '';
            return;
        }

        const startIndex = (currentPage - 1) * itemsPerPage;
        const endIndex = Math.min(startIndex + itemsPerPage, filteredData.length);
        const paginatedData = filteredData.slice(startIndex, endIndex);

        paginatedData.forEach((item, index) => {
            const col = document.createElement('div');
            col.className = 'col-md-6 col-lg-3';

            let categoryColor = 'secondary';
            if (item.category === 'Logistik') categoryColor = 'primary';
            else if (item.category === 'Rantai Pasok') categoryColor = 'warning text-dark';
            else if (item.category === 'Pelabuhan') categoryColor = 'success';
            else if (item.category === 'Ekspor') categoryColor = 'info text-white';
            else if (item.category === 'Impor') categoryColor = 'danger';
            else if (item.category === 'Perdagangan Internasional') categoryColor = 'dark';
            else if (item.category === 'Maritim') categoryColor = 'secondary';
            else if (item.category === 'Cuaca Pengiriman') categoryColor = 'info text-white';
            else if (item.category === 'Ekonomi Indonesia') categoryColor = 'success';
            
            // Format tanggal ke Bahasa Indonesia
            const dateObj = item.published_at ? new Date(item.published_at) : new Date(item.created_at);
            const dateStr = dateObj.toLocaleDateString('id-ID', { 
                day: 'numeric', 
                month: 'long', 
                year: 'numeric' 
            });

            // Gunakan judul terjemahan jika tersedia
            const displayTitle = item.title_id || item.title;
            
            // Gunakan ringkasan terjemahan jika tersedia
            const displaySummary = item.summary_id || item.summary || '';

            // Indonesian mapping of country names for fallback display & image lookup
            const countryFallbacks = {
                'Indonesia': 'https://images.unsplash.com/photo-1594913785162-e6785b49eed9?w=800',
                'Malaysia': 'https://images.unsplash.com/photo-1541088645-3900137a1192?w=800',
                'Singapore': 'https://images.unsplash.com/photo-1525625293386-3f8f99389edd?w=800',
                'Japan': 'https://images.unsplash.com/photo-1493976040374-85c8e12f0c0e?w=800',
                'China': 'https://images.unsplash.com/photo-1508672019048-805c876b67e2?w=800',
                'Saudi Arabia': 'https://images.unsplash.com/photo-1586724237569-f38559dbfe6c?w=800',
                'United States': 'https://images.unsplash.com/photo-1485738422979-f5c462d49f74?w=800',
                'United Kingdom': 'https://images.unsplash.com/photo-1513635269975-59663e0ca1ad?w=800',
                'Germany': 'https://images.unsplash.com/photo-1467269204594-9661b134dd2b?w=800',
                'France': 'https://images.unsplash.com/photo-1502602898657-3e91760cbb34?w=800',
                'Australia': 'https://images.unsplash.com/photo-1523482596112-99d81ebac853?w=800',
                'Canada': 'https://images.unsplash.com/photo-1507608869274-d3177c8bb4c7?w=800',
                'India': 'https://images.unsplash.com/photo-1524492412937-b28074a5d7da?w=800'
            };

            const countryNamesMap = {
                'Singapore': 'Singapura',
                'Japan': 'Jepang',
                'Saudi Arabia': 'Arab Saudi',
                'United States': 'Amerika Serikat',
                'United Kingdom': 'Inggris',
                'Germany': 'Jerman',
                'France': 'Prancis',
                'Canada': 'Kanada',
                'Brazil': 'Brasil',
                'South Africa': 'Afrika Selatan',
                'South Korea': 'Korea Selatan',
                'Netherlands': 'Belanda',
                'Belgium': 'Belgia',
                'Switzerland': 'Swiss',
                'Italy': 'Italia',
                'Spain': 'Spanyol',
                'New Zealand': 'Selandia Baru',
                'Turkey': 'Turki'
            };

            // Eager loaded relationship country or country_name field
            const rawCountryName = item.country_name || (item.country ? item.country.name : null);
            const displayCountry = countryNamesMap[rawCountryName] || rawCountryName || 'Internasional';

            // Gambar dari API atau fallback image per country
            const defaultFallback = 'https://images.unsplash.com/photo-1586528116311-ad8dd3c8310d?w=800';
            const imageUrl = item.image || countryFallbacks[rawCountryName] || defaultFallback;

            // Tentukan label tombol dan link berdasarkan ketersediaan URL asli
            const hasUrl = item.url && item.url !== '#';
            const linkButton = hasUrl
                ? `<small class="text-primary fw-semibold"><i class="bi bi-arrow-right"></i> Baca Berita Asli</small>`
                : `<small class="text-muted"><i class="bi bi-slash-circle"></i> Berita Asli Tidak Tersedia</small>`;

            col.innerHTML = `
                <div class="card h-100 border-0 shadow-sm news-card d-flex flex-column" style="transition: transform 0.3s ease, box-shadow 0.3s ease; ${!hasUrl ? 'opacity: 0.95;' : ''}">
                    ${imageUrl ? `
                    <div class="card-img-top position-relative" style="height: 200px; overflow: hidden;">
                        <img src="${imageUrl}" alt="${displayTitle}" class="w-100 h-100 object-cover" style="transition: transform 0.3s ease;" onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1586528116311-ad8dd3c8310d?w=800';">
                        <div class="position-absolute top-0 end-0 m-2">
                            <span class="badge bg-${categoryColor}">${item.category}</span>
                        </div>
                    </div>
                    ` : ''}
                    <div class="card-body d-flex flex-column" style="flex: 1 0 auto;">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <small class="text-muted"><i class="bi bi-calendar me-1"></i>${dateStr}</small>
                        </div>
                        <h5 class="card-title fw-bold mb-3 text-dark" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; cursor: pointer;" onclick="openDetailModal(${item.id})">${displayTitle}</h5>
                        ${displaySummary ? `
                        <p class="card-text text-muted small mb-4" style="display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden;">${displaySummary}</p>
                        ` : ''}
                        
                        <!-- Actions Row -->
                        <div class="d-flex flex-wrap gap-1 mt-auto pt-3">
                            <button onclick="openDetailModal(${item.id})" class="btn btn-sm btn-outline-secondary flex-fill" title="Detail">
                                <i class="bi bi-eye"></i> Detail
                            </button>
                            ${userRole === 'administrator' ? `
                            <button onclick="openCopyModal(${item.id})" class="btn btn-sm btn-outline-dark flex-fill" title="Copy">
                                <i class="bi bi-files"></i> Copy
                            </button>
                            <button onclick="openEditModal(${item.id})" class="btn btn-sm btn-outline-primary flex-fill" title="Ubah">
                                <i class="bi bi-pencil"></i> Ubah
                            </button>
                            <button onclick="confirmDeleteNews(${item.id})" class="btn btn-sm btn-outline-danger flex-fill" title="Hapus">
                                <i class="bi bi-trash"></i> Hapus
                            </button>
                            ` : ''}
                        </div>
                    </div>
                    <div class="card-footer bg-white border-0 pt-0">
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted"><i class="bi bi-person me-1"></i>${item.author || 'Anonim'} • <i class="bi bi-globe me-1"></i>${displayCountry}</small>
                            ${hasUrl ? `<a href="${item.url}" target="_blank" rel="noopener noreferrer" class="text-decoration-none text-primary small fw-semibold"><i class="bi bi-arrow-right"></i> Sumber</a>` : ''}
                        </div>
                    </div>
                </div>
            `;
            
            // Add hover effect
            col.querySelector('.news-card').addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-5px)';
                this.style.boxShadow = '0 10px 25px rgba(0,0,0,0.1)';
                if (this.querySelector('img')) {
                    this.querySelector('img').style.transform = 'scale(1.05)';
                }
            });
            
            col.querySelector('.news-card').addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
                this.style.boxShadow = '0 0.125rem 0.25rem rgba(0,0,0,0.075)';
                if (this.querySelector('img')) {
                    this.querySelector('img').style.transform = 'scale(1)';
                }
            });
            container.appendChild(col);
        });

        document.getElementById('pagination-info').innerText = `Menampilkan ${startIndex + 1} hingga ${endIndex} dari ${filteredData.length} entri`;

        const totalPages = Math.ceil(filteredData.length / itemsPerPage);
        const linksContainer = document.getElementById('pagination-links');
        linksContainer.innerHTML = '';

        const prevLi = document.createElement('li');
        prevLi.className = `page-item ${currentPage === 1 ? 'disabled' : ''}`;
        prevLi.innerHTML = `<button class="page-link" onclick="changePage(${currentPage - 1})">Sebelumnya</button>`;
        linksContainer.appendChild(prevLi);

        let startPage = Math.max(1, currentPage - 2);
        let endPage = Math.min(totalPages, startPage + 4);
        if (endPage - startPage < 4) {
            startPage = Math.max(1, endPage - 4);
        }

        for (let p = startPage; p <= endPage; p++) {
            const li = document.createElement('li');
            li.className = `page-item ${currentPage === p ? 'active' : ''}`;
            li.innerHTML = `<button class="page-link" onclick="changePage(${p})">${p}</button>`;
            linksContainer.appendChild(li);
        }

        const nextLi = document.createElement('li');
        nextLi.className = `page-item ${currentPage === totalPages ? 'disabled' : ''}`;
        nextLi.innerHTML = `<button class="page-link" onclick="changePage(${currentPage + 1})">Berikutnya</button>`;
        linksContainer.appendChild(nextLi);
    }

    function changePage(page) {
        const totalPages = Math.ceil(filteredData.length / itemsPerPage);
        if (page < 1 || page > totalPages) return;
        currentPage = page;
        renderGrid();
    }

    const newsFormModal = new bootstrap.Modal(document.getElementById('newsFormModal'));
    const newsDetailModal = new bootstrap.Modal(document.getElementById('newsDetailModal'));

    function openCreateModal() {
        document.getElementById('newsForm').reset();
        document.getElementById('news-id').value = '';
        document.getElementById('form-action').value = 'create';
        document.getElementById('newsFormModalLabel').innerText = 'Tambah Berita Baru';
        document.getElementById('form-date').value = new Date().toISOString().split('T')[0];
        newsFormModal.show();
    }

    function openEditModal(id) {
        document.getElementById('newsForm').reset();
        document.getElementById('news-id').value = id;
        document.getElementById('form-action').value = 'edit';
        document.getElementById('newsFormModalLabel').innerText = 'Ubah Berita';

        fetch(`/api/news/${id}`)
            .then(res => res.json())
            .then(response => {
                if (response.success) {
                    const data = response.data;
                    document.getElementById('form-title').value = data.title;
                    document.getElementById('form-category').value = data.category;
                    document.getElementById('form-country').value = data.country_id;
                    document.getElementById('form-author').value = data.author || '';
                    document.getElementById('form-date').value = data.published_at ? data.published_at.split('T')[0] : '';
                    document.getElementById('form-image').value = data.image || '';
                    document.getElementById('form-url').value = data.url || '';
                    document.getElementById('form-summary').value = data.summary || '';
                    document.getElementById('form-content').value = data.content || '';
                    newsFormModal.show();
                } else {
                    showToast('Gagal memuat detail berita.', 'error');
                }
            })
            .catch(err => {
                console.error(err);
                showToast('Terjadi kesalahan koneksi.', 'error');
            });
    }

    function openCopyModal(id) {
        document.getElementById('newsForm').reset();
        document.getElementById('news-id').value = id;
        document.getElementById('form-action').value = 'copy';
        document.getElementById('newsFormModalLabel').innerText = 'Salin Berita';

        fetch(`/api/news/${id}`)
            .then(res => res.json())
            .then(response => {
                if (response.success) {
                    const data = response.data;
                    document.getElementById('form-title').value = 'Copy - ' + data.title;
                    document.getElementById('form-category').value = data.category;
                    document.getElementById('form-country').value = data.country_id;
                    document.getElementById('form-author').value = data.author || '';
                    document.getElementById('form-date').value = new Date().toISOString().split('T')[0];
                    document.getElementById('form-image').value = data.image || '';
                    document.getElementById('form-url').value = data.url ? data.url + '-copy' : '';
                    document.getElementById('form-summary').value = data.summary || '';
                    document.getElementById('form-content').value = data.content || '';
                    newsFormModal.show();
                } else {
                    showToast('Gagal memuat detail berita.', 'error');
                }
            })
            .catch(err => {
                console.error(err);
                showToast('Terjadi kesalahan koneksi.', 'error');
            });
    }

    function openDetailModal(id) {
        fetch(`/api/news/${id}`)
            .then(res => res.json())
            .then(response => {
                if (response.success) {
                    const data = response.data;
                    document.getElementById('detail-title').innerText = data.title_id || data.title;
                    document.getElementById('detail-category').innerText = data.category;
                    
                    const dateObj = data.published_at ? new Date(data.published_at) : new Date(data.created_at);
                    const dateStr = dateObj.toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
                    const countryName = data.country ? data.country.name : 'Internasional';
                    document.getElementById('detail-meta').innerText = `${dateStr} • ${data.author || 'Anonim'} • ${countryName}`;
                    
                    document.getElementById('detail-summary').innerText = data.summary_id || data.summary || 'Tidak ada ringkasan.';
                    document.getElementById('detail-content').innerText = data.content || 'Tidak ada isi konten.';

                    const imgContainer = document.getElementById('detail-image-container');
                    const imgEl = document.getElementById('detail-image');
                    if (data.image) {
                        imgEl.src = data.image;
                        imgContainer.classList.remove('d-none');
                    } else {
                        imgContainer.classList.add('d-none');
                    }

                    const urlBtn = document.getElementById('detail-url');
                    if (data.url && data.url !== '#') {
                        urlBtn.href = data.url;
                        urlBtn.classList.remove('d-none');
                    } else {
                        urlBtn.classList.add('d-none');
                    }

                    newsDetailModal.show();
                } else {
                    showToast('Gagal memuat detail berita.', 'error');
                }
            })
            .catch(err => {
                console.error(err);
                showToast('Terjadi kesalahan koneksi.', 'error');
            });
    }

    function confirmDeleteNews(id) {
        if (confirm('Apakah Anda yakin ingin menghapus berita ini?')) {
            const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            fetch(`/api/news/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': token,
                    'Accept': 'application/json'
                }
            })
            .then(res => res.json())
            .then(response => {
                if (response.success) {
                    showToast('Berita berhasil dihapus.', 'success');
                    fetchNews();
                } else {
                    showToast('Gagal menghapus berita: ' + response.message, 'error');
                }
            })
            .catch(err => {
                console.error(err);
                showToast('Terjadi kesalahan koneksi.', 'error');
            });
        }
    }

    document.getElementById('newsForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const id = document.getElementById('news-id').value;
        const action = document.getElementById('form-action').value;
        const method = action === 'edit' ? 'PUT' : 'POST';
        const url = action === 'edit' ? `/api/news/${id}` : '/api/news';
        const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        const payload = {
            title: document.getElementById('form-title').value,
            category: document.getElementById('form-category').value,
            country_id: document.getElementById('form-country').value,
            author: document.getElementById('form-author').value,
            published_at: document.getElementById('form-date').value,
            image: document.getElementById('form-image').value,
            image_url: document.getElementById('form-image').value,
            url: document.getElementById('form-url').value,
            summary: document.getElementById('form-summary').value,
            content: document.getElementById('form-content').value,
        };

        const saveBtn = document.getElementById('save-news-btn');
        saveBtn.disabled = true;
        saveBtn.innerText = 'Menyimpan...';

        fetch(url, {
            method: method,
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token,
                'Accept': 'application/json'
            },
            body: JSON.stringify(payload)
        })
        .then(res => res.json())
        .then(response => {
            saveBtn.disabled = false;
            saveBtn.innerText = 'Simpan';
            if (response.success) {
                showToast(action === 'edit' ? 'Berita berhasil diperbarui.' : 'Berita berhasil ditambahkan.', 'success');
                newsFormModal.hide();
                fetchNews();
            } else {
                showToast('Gagal menyimpan berita: ' + (response.errors ? Object.values(response.errors).flat().join(' ') : response.message), 'error');
            }
        })
        .catch(err => {
            saveBtn.disabled = false;
            saveBtn.innerText = 'Simpan';
            console.error(err);
            showToast('Terjadi kesalahan koneksi.', 'error');
        });
    });
</script>
@endpush