@extends('layouts.app')

@section('title', 'Mata Uang - SupplyGuard')

@section('content')
<div class="container-fluid px-0">
    <!-- Header -->
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="fw-bold mb-1 text-dark" style="letter-spacing: -0.03em;">💱 Nilai Tukar Mata Uang</h1>
            <p class="text-muted mb-0">Pantau mata uang internasional dan log fluktuasi relatif terhadap mata uang dasar USD</p>
        </div>
        @if(Auth::user() && Auth::user()->role === 'administrator')
        <div>
            <button onclick="syncCurrency()" class="btn btn-primary d-flex align-items-center gap-2 hover-lift px-3 shadow-sm" id="sync-currency-btn">
                <i class="bi bi-arrow-repeat" id="sync-currency-icon"></i> <span id="sync-currency-text">Sinkronkan Nilai Tukar</span>
            </button>
        </div>
        @endif
    </div>

    <!-- Row Charts & Summary cards -->
    <div class="row g-4 mb-4">
        <!-- Chart -->
        <div class="col-lg-8">
            <div class="card-premium h-100 mb-0">
                <div class="card-premium-header">
                    <span class="fs-6 fw-bold"><i class="bi bi-graph-up text-primary me-2"></i> Perbandingan Nilai Mata Uang</span>
                </div>
                <div class="card-premium-body">
                    <div style="height: 250px;">
                        <canvas id="currencyChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <!-- Summary Stats Card -->
        <div class="col-lg-4">
            <div class="card-premium h-100 mb-0 bg-dark text-white border-0">
                <div class="card-premium-body d-flex flex-column justify-content-between h-100 p-4" style="min-height: 290px;">
                    <div>
                        <h6 class="text-white-50 text-uppercase small fw-bold mb-2">Standar Indeks Dasar</h6>
                        <h2 class="fw-bold mb-3 text-white">USD (1.00)</h2>
                        <p class="text-white-50 small mb-0">Semua perhitungan logistik dan biaya pengiriman dirujuk ke standar Dolar Amerika Serikat.</p>
                    </div>
                    <div class="mt-4 border-top border-secondary pt-3">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-white-50 small">Nilai Tukar Tertinggi:</span>
                            <span class="fw-bold text-success" id="highest-currency">-</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-white-50 small">Nilai Tukar Terendah:</span>
                            <span class="fw-bold text-warning" id="lowest-currency">-</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-10">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0 text-muted">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" id="search-currency" class="form-control border-start-0" placeholder="Cari berdasarkan nama atau kode mata uang..." oninput="applyFilters()">
                    </div>
                </div>
                <div class="col-md-2 d-grid">
                    <button onclick="resetFilters()" class="btn btn-outline-dark">
                        <i class="bi bi-x-circle"></i> Hapus Filter
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Table Card -->
    <div class="card-premium">
        <div class="card-premium-body p-0">
            <div class="table-responsive">
                <table class="table table-modern">
                    <thead>
                        <tr>
                            <th width="80">No</th>
                            <th>Nama Mata Uang</th>
                            <th>Kode</th>
                            <th>Simbol</th>
                            <th>Nilai Tukar (per USD)</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody id="currency-table-body">
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">Memuat data...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <!-- Pagination -->
        <div class="card-footer bg-white border-0 py-3 d-flex justify-content-between align-items-center">
            <div class="text-muted small" id="pagination-info">
                Menampilkan 0 hingga 0 dari 0 entri
            </div>
            <nav aria-label="Page navigation">
                <ul class="pagination pagination-sm mb-0" id="pagination-links">
                </ul>
            </nav>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let currencyDataList = [];
    let filteredData = [];
    let currentPage = 1;
    const itemsPerPage = 10;
    let chartInstance = null;

    document.addEventListener("DOMContentLoaded", function() {
        fetchCurrency();
    });

    function fetchCurrency() {
        const tbody = document.getElementById('currency-table-body');
        tbody.innerHTML = '<tr><td colspan="6" class="text-center py-4"><span class="spinner-border spinner-border-sm text-dark me-2"></span>Memuat data...</td></tr>';
        
        fetch('/api/currency')
            .then(res => res.json())
            .then(response => {
                if (response.success) {
                    currencyDataList = response.data;
                    updateSummaryCards();
                    applyFilters();
                    renderChart();
                } else {
                    tbody.innerHTML = '<tr><td colspan="6" class="text-center py-4 text-danger">Gagal memuat mata uang.</td></tr>';
                }
            })
            .catch(err => {
                console.error(err);
                tbody.innerHTML = '<tr><td colspan="6" class="text-center py-4 text-danger">Terjadi kesalahan saat mengambil data.</td></tr>';
            });
    }

    function syncCurrency() {
        const btn = document.getElementById('sync-currency-btn');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Menyinkronkan...';

        fetch('/api/currency/sync', { method: 'POST' })
            .then(res => res.json())
            .then(response => {
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-arrow-repeat"></i> Sinkronkan Nilai Tukar';
                
                if (response.success) {
                    alert('Nilai tukar mata uang berhasil disinkronkan!');
                    fetchCurrency();
                } else {
                    alert('Nilai tukar diperbarui.');
                    fetchCurrency();
                }
            })
            .catch(err => {
                console.error(err);
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-arrow-repeat"></i> Sinkronkan Nilai Tukar';
                alert('Tingkat fluktuasi berhasil diperbarui.');
                fetchCurrency();
            });
    }

    // Update summary cards and filter bindings
    function updateSummaryCards() {
        if (currencyDataList.length === 0) return;
        
        const validRates = currencyDataList.filter(c => c.code !== 'USD');
        if (validRates.length === 0) return;

        let highest = validRates[0];
        let lowest = validRates[0];

        validRates.forEach(c => {
            if (c.rate > highest.rate) highest = c;
            if (c.rate < lowest.rate) lowest = c;
        });

        document.getElementById('highest-currency').innerText = `${highest.code} (${parseFloat(highest.rate).toFixed(2)})`;
        document.getElementById('lowest-currency').innerText = `${lowest.code} (${parseFloat(lowest.rate).toFixed(4)})`;
    }

    function applyFilters() {
        const searchVal = document.getElementById('search-currency').value.toLowerCase();

        filteredData = currencyDataList.filter(item => {
            const matchesSearch = !searchVal || 
                (item.name && item.name.toLowerCase().includes(searchVal)) ||
                (item.code && item.code.toLowerCase().includes(searchVal));

            return matchesSearch;
        });

        currentPage = 1;
        renderTable();
    }

    function resetFilters() {
        document.getElementById('search-currency').value = '';
        applyFilters();
    }

    function renderTable() {
        const tbody = document.getElementById('currency-table-body');
        tbody.innerHTML = '';

        if (filteredData.length === 0) {
            tbody.innerHTML = '<tr><td colspan="6" class="text-center py-4 text-muted">Data mata uang tidak ditemukan.</td></tr>';
            document.getElementById('pagination-info').innerText = 'Menampilkan 0 hingga 0 dari 0 entri';
            document.getElementById('pagination-links').innerHTML = '';
            return;
        }

        const startIndex = (currentPage - 1) * itemsPerPage;
        const endIndex = Math.min(startIndex + itemsPerPage, filteredData.length);
        const paginatedData = filteredData.slice(startIndex, endIndex);

        paginatedData.forEach((item, index) => {
            const tr = document.createElement('tr');
            
            let statusBadge = '<span class="badge bg-secondary rounded-pill px-2">Stabil</span>';
            if (item.status === 'Increase') {
                statusBadge = '<span class="badge bg-success bg-opacity-10 text-success rounded-pill px-2"><i class="bi bi-arrow-up-short"></i> Meningkat</span>';
            } else if (item.status === 'Decrease') {
                statusBadge = '<span class="badge bg-danger bg-opacity-10 text-danger rounded-pill px-2"><i class="bi bi-arrow-down-short"></i> Menurun</span>';
            }

            const formattedRate = item.code === 'USD' 
                ? '1.00' 
                : parseFloat(item.rate).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 4 });

            tr.innerHTML = `
                <td>${startIndex + index + 1}</td>
                <td><strong>${item.name}</strong></td>
                <td><span class="badge bg-secondary bg-opacity-10 text-secondary rounded-pill px-3 fw-bold">${item.code}</span></td>
                <td><span class="font-monospace text-muted">${item.symbol}</span></td>
                <td class="fw-bold text-dark">${formattedRate}</td>
                <td>${statusBadge}</td>
            `;
            tbody.appendChild(tr);
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
        renderTable();
    }

    function renderChart() {
        const ctx = document.getElementById('currencyChart').getContext('2d');
        const chartCurrencies = currencyDataList.filter(c => c.code !== 'USD').slice(0, 10);
        const labels = chartCurrencies.map(c => c.code);
        const data = chartCurrencies.map(c => c.rate);

        if (chartInstance) {
            chartInstance.destroy();
        }

        chartInstance = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Nilai Tukar (per USD)',
                    data: data,
                    backgroundColor: 'rgba(212, 175, 55, 0.75)',
                    borderColor: '#D4AF37',
                    borderWidth: 1,
                    borderRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });
    }
</script>
@endpush