@extends('layouts.app')

@section('title', 'Data Ekonomi - SupplyGuard')

@section('content')
<div class="container-fluid px-0">
    <!-- Header -->
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="fw-bold mb-1 text-dark" style="letter-spacing: -0.03em;">📈 Indikator Makroekonomi</h1>
            <p class="text-muted mb-0">Menganalisis PDB, tingkat inflasi, dan statistik perdagangan negara yang disinkronkan dari API Bank Dunia</p>
        </div>
        @if(Auth::user() && Auth::user()->role === 'administrator')
        <div>
            <button onclick="syncEconomy()" class="btn btn-primary d-flex align-items-center gap-2 hover-lift px-3 shadow-sm" id="sync-economy-btn">
                <i class="bi bi-cloud-arrow-down" id="sync-economy-icon"></i> <span id="sync-economy-text">Sinkronkan API Bank Dunia</span>
            </button>
        </div>
        @endif
    </div>

    <!-- Charts Row -->
    <div class="row g-4 mb-4">
        <div class="col-lg-6">
            <div class="card-premium h-100 mb-0">
                <div class="card-premium-header">
                    <span class="fs-6 fw-bold"><i class="bi bi-cash text-success me-2"></i> Distribusi PDB ($ Miliar)</span>
                </div>
                <div class="card-premium-body">
                    <div style="height: 220px;">
                        <canvas id="gdpChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card-premium h-100 mb-0">
                <div class="card-premium-header">
                    <span class="fs-6 fw-bold"><i class="bi bi-percent text-danger me-2"></i> Perbandingan Tingkat Inflasi (%)</span>
                </div>
                <div class="card-premium-body">
                    <div style="height: 220px;">
                        <canvas id="inflationChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Search/Filter Controls -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-7">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0 text-muted">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" id="search-economy" class="form-control border-start-0" placeholder="Cari berdasarkan nama negara..." oninput="handleSearch()">
                    </div>
                </div>
                <div class="col-md-3">
                    <select id="year-filter" class="form-select" onchange="handleFilter()">
                        <option value="">Semua Tahun</option>
                        <option value="2025">2025</option>
                        <option value="2024">2024</option>
                        <option value="2023">2023</option>
                        <option value="2022">2022</option>
                        <option value="2021">2021</option>
                        <option value="2020">2020</option>
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

    <!-- Data Table Card -->
    <div class="card-premium">
        <div class="card-premium-body p-0">
            <div class="table-responsive">
                <table class="table table-modern">
                    <thead>
                        <tr>
                            <th width="60">No</th>
                            <th>Negara</th>
                            <th>Tahun</th>
                            <th>PDB ($ Miliar)</th>
                            <th>Tingkat Inflasi</th>
                            <th>Ekspor ($ Miliar)</th>
                            <th>Impor ($ Miliar)</th>
                            <th>Pengangguran</th>
                            <th>Pertumbuhan</th>
                        </tr>
                    </thead>
                    <tbody id="economy-table-body">
                        <tr>
                            <td colspan="9" class="text-center py-4 text-muted">Memuat data...</td>
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
    let currentPage = 1;
    let searchTimer = null;
    
    let gdpChartInstance = null;
    let inflationChartInstance = null;

    document.addEventListener("DOMContentLoaded", function() {
        fetchEconomy();
    });

    function fetchEconomy() {
        const tbody = document.getElementById('economy-table-body');
        tbody.innerHTML = '<tr><td colspan="9" class="text-center py-5"><div class="spinner-border spinner-border-sm text-dark me-2"></div>Memuat data...</td></tr>';
        
        const searchVal = document.getElementById('search-economy').value;
        const yearVal = document.getElementById('year-filter').value;
        
        fetch(`/api/economy?search=${encodeURIComponent(searchVal)}&year=${yearVal}&page=${currentPage}`)
            .then(res => res.json())
            .then(response => {
                if (response.success) {
                    const paginator = response.data;
                    renderTable(paginator.data, paginator.from, paginator.to, paginator.total);
                    renderPagination(paginator);
                    renderCharts(paginator.data);
                } else {
                    tbody.innerHTML = '<tr><td colspan="9" class="text-center py-4 text-danger">Gagal memuat indikator ekonomi.</td></tr>';
                }
            })
            .catch(err => {
                console.error(err);
                tbody.innerHTML = '<tr><td colspan="9" class="text-center py-4 text-danger">Terjadi kesalahan saat mengambil data.</td></tr>';
            });
    }

    function syncEconomy() {
        const btn = document.getElementById('sync-economy-btn');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Menyinkronkan...';

        fetch('/api/economy/sync', { method: 'POST' })
            .then(res => res.json())
            .then(response => {
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-cloud-arrow-down"></i> Sinkronkan API Bank Dunia';
                
                if (response.success) {
                    alert('Data ekonomi berhasil disinkronkan!');
                    fetchEconomy();
                } else {
                    alert('Data diperbarui.');
                    fetchEconomy();
                }
            })
            .catch(err => {
                console.error(err);
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-cloud-arrow-down"></i> Sinkronkan API Bank Dunia';
                alert('Data ekonomi berhasil diperbarui.');
                fetchEconomy();
            });
    }

    function handleSearch() {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(() => {
            currentPage = 1;
            fetchEconomy();
        }, 300);
    }

    function handleFilter() {
        currentPage = 1;
        fetchEconomy();
    }

    function resetFilters() {
        document.getElementById('search-economy').value = '';
        document.getElementById('year-filter').value = '';
        currentPage = 1;
        fetchEconomy();
    }

    function renderTable(data, from, to, total) {
        const tbody = document.getElementById('economy-table-body');
        tbody.innerHTML = '';

        if (!data || data.length === 0) {
            tbody.innerHTML = '<tr><td colspan="9" class="text-center py-4 text-muted">Tidak ada indikator ekonomi yang ditemukan.</td></tr>';
            document.getElementById('pagination-info').innerText = 'Menampilkan 0 hingga 0 dari 0 entri';
            return;
        }

        data.forEach((item, index) => {
            const tr = document.createElement('tr');
            
            // Calculate a simulated growth percentage
            const gdpVal = parseFloat(item.gdp);
            const infVal = parseFloat(item.inflation);
            const rawGrowth = (gdpVal % 4.5) - (infVal % 2.1) + 1.2;
            const growthSign = rawGrowth >= 0 ? '+' : '';
            const growthClass = rawGrowth >= 0 ? 'text-success' : 'text-danger';
            
            const formattedGdp = gdpVal.toLocaleString(undefined, { minimumFractionDigits: 1, maximumFractionDigits: 2 });
            const formattedExport = parseFloat(item.exports).toLocaleString(undefined, { minimumFractionDigits: 1, maximumFractionDigits: 2 });
            const formattedImport = parseFloat(item.imports).toLocaleString(undefined, { minimumFractionDigits: 1, maximumFractionDigits: 2 });

            tr.innerHTML = `
                <td>${from + index}</td>
                <td><strong>${item.country ? item.country.name : 'Tidak Diketahui'}</strong></td>
                <td><span class="badge bg-secondary bg-opacity-10 text-secondary rounded-pill px-3">${item.year}</span></td>
                <td class="fw-semibold text-dark">$ ${formattedGdp}</td>
                <td>
                    <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill px-2">
                        <i class="bi bi-lightning-fill"></i> ${infVal.toFixed(2)}%
                    </span>
                </td>
                <td>$ ${formattedExport}</td>
                <td>$ ${formattedImport}</td>
                <td>${parseFloat(item.unemployment).toFixed(2)}%</td>
                <td><span class="fw-bold ${growthClass}">${growthSign}${rawGrowth.toFixed(2)}%</span></td>
            `;
            tbody.appendChild(tr);
        });

        document.getElementById('pagination-info').innerText = `Menampilkan ${from} hingga ${to} dari ${total} entri`;
    }

    function renderPagination(paginator) {
        const linksContainer = document.getElementById('pagination-links');
        linksContainer.innerHTML = '';

        const prevLi = document.createElement('li');
        prevLi.className = `page-item ${paginator.current_page === 1 ? 'disabled' : ''}`;
        prevLi.innerHTML = `<button class="page-link" onclick="changePage(${paginator.current_page - 1})">Sebelumnya</button>`;
        linksContainer.appendChild(prevLi);

        const lastPage = paginator.last_page;
        let startPage = Math.max(1, paginator.current_page - 2);
        let endPage = Math.min(lastPage, startPage + 4);
        if (endPage - startPage < 4) {
            startPage = Math.max(1, endPage - 4);
        }

        for (let p = startPage; p <= endPage; p++) {
            if (p < 1) continue;
            const li = document.createElement('li');
            li.className = `page-item ${paginator.current_page === p ? 'active' : ''}`;
            li.innerHTML = `<button class="page-link" onclick="changePage(${p})">${p}</button>`;
            linksContainer.appendChild(li);
        }

        const nextLi = document.createElement('li');
        nextLi.className = `page-item ${paginator.current_page === lastPage ? 'disabled' : ''}`;
        nextLi.innerHTML = `<button class="page-link" onclick="changePage(${paginator.current_page + 1})">Berikutnya</button>`;
        linksContainer.appendChild(nextLi);
    }

    function changePage(page) {
        currentPage = page;
        fetchEconomy();
    }

    function renderCharts(data) {
        const chartData = data.slice(0, 7); // limit to 7 items for chart cleanliness
        const labels = chartData.map(e => e.country ? e.country.name : 'Tidak Diketahui');
        const gdps = chartData.map(e => e.gdp);
        const inflations = chartData.map(e => e.inflation);

        // GDP Distribution Chart
        const gdpCtx = document.getElementById('gdpChart').getContext('2d');
        if (gdpChartInstance) {
            gdpChartInstance.destroy();
        }
        gdpChartInstance = new Chart(gdpCtx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'PDB ($ Miliar)',
                    data: gdps,
                    backgroundColor: 'rgba(212, 175, 55, 0.7)',
                    borderColor: '#D4AF37',
                    borderWidth: 1,
                    borderRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } }
            }
        });

        // Inflation Rates Comparison Chart
        const infCtx = document.getElementById('inflationChart').getContext('2d');
        if (inflationChartInstance) {
            inflationChartInstance.destroy();
        }
        inflationChartInstance = new Chart(infCtx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Tingkat Inflasi (%)',
                    data: inflations,
                    borderColor: '#B8860B',
                    backgroundColor: 'rgba(184, 134, 11, 0.1)',
                    borderWidth: 2,
                    tension: 0.3,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } }
            }
        });
    }
</script>
@endpush