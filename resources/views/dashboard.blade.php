@extends('layouts.app')

@section('title', 'Dashboard - SupplyGuard')

@section('content')
<div class="container-fluid px-0">
    <!-- Header Page Info -->
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="fw-bold mb-1 text-dark" style="letter-spacing: -0.03em;">📈 Dashboard Pusat Kendali</h1>
            <p class="text-muted mb-0">Pusat Intelijen Rantai Pasok & Pemantauan Risiko Global</p>
        </div>
        <div>
            <button onclick="loadDashboardData()" class="btn btn-primary d-flex align-items-center gap-2 hover-lift px-3 shadow-sm">
                <i class="bi bi-arrow-clockwise"></i> Muat Ulang Metrik
            </button>
        </div>
    </div>

    <!-- Statistics Row -->
    <div class="row g-3 mb-4">
        <!-- Countries -->
        <div class="col-md-3">
            <div class="card-stat primary p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted text-uppercase small mb-1 fw-semibold">Total Negara</h6>
                        <h3 class="mb-0 fw-bold text-dark" id="stat-countries">0</h3>
                    </div>
                    <div class="card-stat-icon primary">
                        <i class="bi bi-globe"></i>
                    </div>
                </div>
            </div>
        </div>
        <!-- High Risk -->
        <div class="col-md-3">
            <div class="card-stat danger p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted text-uppercase small mb-1 fw-semibold">Risiko Tinggi</h6>
                        <h3 class="mb-0 fw-bold text-danger" id="stat-high-risk">0</h3>
                    </div>
                    <div class="card-stat-icon danger">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                    </div>
                </div>
            </div>
        </div>
        <!-- Medium Risk -->
        <div class="col-md-3">
            <div class="card-stat warning p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted text-uppercase small mb-1 fw-semibold">Risiko Sedang</h6>
                        <h3 class="mb-0 fw-bold text-warning" id="stat-medium-risk">0</h3>
                    </div>
                    <div class="card-stat-icon warning">
                        <i class="bi bi-exclamation-circle-fill"></i>
                    </div>
                </div>
            </div>
        </div>
        <!-- Low Risk -->
        <div class="col-md-3">
            <div class="card-stat success p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted text-uppercase small mb-1 fw-semibold">Risiko Rendah</h6>
                        <h3 class="mb-0 fw-bold text-success" id="stat-low-risk">0</h3>
                    </div>
                    <div class="card-stat-icon success">
                        <i class="bi bi-check-circle-fill"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Second Stats row for Ports, Shipments, Watchlists -->
    <div class="row g-3 mb-4">
        <!-- Ports -->
        <div class="col-md-4">
            <div class="card-stat info p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted text-uppercase small mb-1 fw-semibold">Total Pelabuhan</h6>
                        <h3 class="mb-0 fw-bold text-dark" id="stat-ports">0</h3>
                    </div>
                    <div class="card-stat-icon info">
                        <i class="bi bi-tsunami"></i>
                    </div>
                </div>
            </div>
        </div>
        <!-- Shipments -->
        <div class="col-md-4">
            <div class="card-stat primary p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted text-uppercase small mb-1 fw-semibold">Total Pengiriman</h6>
                        <h3 class="mb-0 fw-bold text-dark" id="stat-shipments">0</h3>
                    </div>
                    <div class="card-stat-icon primary">
                        <i class="bi bi-truck"></i>
                    </div>
                </div>
            </div>
        </div>
        <!-- Watchlist -->
        <div class="col-md-4">
            <div class="card-stat info p-3" style="border-left-color: #6f42c1 !important;">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted text-uppercase small mb-1 fw-semibold">Total Daftar Pantauan</h6>
                        <h3 class="mb-0 fw-bold text-dark" id="stat-watchlist">0</h3>
                    </div>
                    <div class="card-stat-icon" style="background: rgba(111, 66, 193, 0.1); color: #6f42c1;">
                        <i class="bi bi-star-fill"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Map & Trends Row -->
    <div class="row g-4 mb-4">
        <!-- Interactive Global Map -->
        <div class="col-lg-8">
            <div class="card-premium">
                <div class="card-premium-header">
                    <span class="fs-6 fw-bold"><i class="bi bi-map text-primary me-2"></i> Peta Dunia</span>
                    <a href="{{ route('map.index') }}" class="btn btn-sm btn-outline-primary px-3 rounded-pill">Lihat</a>
                </div>
                <div class="card-premium-body p-0 position-relative">
                    <div id="dashboardMap" style="height: 380px; width:100%; border-bottom-left-radius: 16px; border-bottom-right-radius: 16px;"></div>
                </div>
            </div>
        </div>

        <!-- Latest Activities & Stats -->
        <div class="col-lg-4">
            <div class="card-premium h-100 mb-0">
                <div class="card-premium-header">
                    <span class="fs-6 fw-bold"><i class="bi bi-lightning-charge text-warning me-2"></i> Indeks Risiko Langsung</span>
                </div>
                <div class="card-premium-body">
                    <div class="text-center py-3">
                        <div class="d-inline-flex align-items-center justify-content-center border border-4 border-primary rounded-circle mb-3" style="width: 120px; height: 120px; background-color: rgba(13, 110, 253, 0.05);">
                            <div class="text-center">
                                <span class="d-block fs-3 fw-bold text-dark" id="avg-risk-score">0%</span>
                                <small class="text-muted text-uppercase" style="font-size: 0.65rem;">Rata-rata Risiko</small>
                            </div>
                        </div>
                        <p class="text-muted small px-3">Rata-rata tingkat risiko dihitung dari kondisi iklim, stabilitas keuangan, status pelabuhan, dan metrik sentimen.</p>
                    </div>

                    <hr class="text-muted opacity-25 my-3">

                    <span class="d-block text-muted small fw-semibold text-uppercase mb-2">Ringkasan Cuaca</span>
                    <ul class="list-group list-group-flush" id="weather-summary-list">
                        <li class="list-group-item text-center text-muted py-2 border-0">Memuat data...</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Analytical Charts Row -->
    <div class="row g-4 mb-4">
        <!-- GDP Trend -->
        <div class="col-md-6 col-lg-3">
            <div class="card-premium h-100">
                <div class="card-premium-header py-3">
                    <span class="fs-6 fw-bold text-truncate"><i class="bi bi-cash-stack text-success me-2"></i> Tren PDB ($ Miliar)</span>
                </div>
                <div class="card-premium-body p-3">
                    <div style="height: 180px;"><canvas id="gdpChart"></canvas></div>
                </div>
            </div>
        </div>

        <!-- Inflation Trend -->
        <div class="col-md-6 col-lg-3">
            <div class="card-premium h-100">
                <div class="card-premium-header py-3">
                    <span class="fs-6 fw-bold text-truncate"><i class="bi bi-percent text-danger me-2"></i> Tren Inflasi (%)</span>
                </div>
                <div class="card-premium-body p-3">
                    <div style="height: 180px;"><canvas id="inflationChart"></canvas></div>
                </div>
            </div>
        </div>

        <!-- Risk Score Trend -->
        <div class="col-md-6 col-lg-3">
            <div class="card-premium h-100">
                <div class="card-premium-header py-3">
                    <span class="fs-6 fw-bold text-truncate"><i class="bi bi-shield-exclamation text-warning me-2"></i> Tren Risiko</span>
                </div>
                <div class="card-premium-body p-3">
                    <div style="height: 180px;"><canvas id="riskChart"></canvas></div>
                </div>
            </div>
        </div>

        <!-- Shipment Volume Trend -->
        <div class="col-md-6 col-lg-3">
            <div class="card-premium h-100">
                <div class="card-premium-header py-3">
                    <span class="fs-6 fw-bold text-truncate"><i class="bi bi-truck text-primary me-2"></i> Tren Pengiriman</span>
                </div>
                <div class="card-premium-body p-3">
                    <div style="height: 180px;"><canvas id="shipmentChart"></canvas></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Active Shipments & Latest News -->
    <div class="row g-4 mb-4">
        <!-- Active Shipments -->
        <div class="col-lg-8">
            <div class="card-premium mb-0 h-100">
                <div class="card-premium-header">
                    <span class="fs-6 fw-bold"><i class="bi bi-box-seam text-primary me-2"></i> Riwayat Pengiriman</span>
                    <a href="{{ route('shipments.history') }}" class="btn btn-sm btn-outline-dark px-3 rounded-pill">Lihat</a>
                </div>
                <div class="card-premium-body p-0">
                    <div class="table-responsive">
                        <table class="table table-modern align-middle">
                            <thead>
                                <tr>
                                    <th>Nomor Pelacakan</th>
                                    <th>Kargo</th>
                                    <th>Rute</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody id="shipments-table-body">
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted">Memuat data...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Latest News -->
        <div class="col-lg-4">
            <div class="card-premium mb-0 h-100">
                <div class="card-premium-header">
                    <span class="fs-6 fw-bold"><i class="bi bi-newspaper text-info me-2"></i> Berita Rantai Pasok</span>
                    <a href="{{ route('news.index') }}" class="btn btn-sm btn-outline-dark px-3 rounded-pill">Lihat</a>
                </div>
                <div class="card-premium-body p-0">
                    <div class="list-group list-group-flush" id="news-list">
                        <div class="list-group-item text-center py-4 text-muted">Memuat data...</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let dashboardMapInstance = null;
    let gdpChartInstance = null;
    let inflationChartInstance = null;
    let riskChartInstance = null;
    let shipmentChartInstance = null;

    document.addEventListener("DOMContentLoaded", function() {
        initDashboardMap();
        loadDashboardData();
    });

    function initDashboardMap() {
        dashboardMapInstance = L.map('dashboardMap', {
            zoomControl: false
        }).setView([-2.5, 118.0], 3); // Centered on Southeast Asia/Global

        L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
            attribution: '&copy; OpenStreetMap &copy; CARTO'
        }).addTo(dashboardMapInstance);
        
        L.control.zoom({ position: 'bottomright' }).addTo(dashboardMapInstance);
    }

    function loadDashboardData() {
        // Fetch API Dashboard Data
        fetch('/api/dashboard')
            .then(res => res.json())
            .then(response => {
                if (response.success) {
                    const data = response.data;
                    const stats = data.statistics;

                    // Populate numeric statistics
                    document.getElementById('stat-countries').textContent = stats.total_countries;
                    document.getElementById('stat-high-risk').textContent = stats.high_risk;
                    document.getElementById('stat-medium-risk').textContent = stats.medium_risk;
                    document.getElementById('stat-low-risk').textContent = stats.low_risk;
                    document.getElementById('stat-ports').textContent = stats.total_ports;
                    document.getElementById('stat-shipments').textContent = stats.total_shipments;
                    document.getElementById('stat-watchlist').textContent = stats.watchlist_count;
                    document.getElementById('avg-risk-score').textContent = stats.average_risk_score + '%';

                    // Populate weather highlights
                    const weatherContainer = document.getElementById('weather-summary-list');
                    weatherContainer.innerHTML = '';
                    if (data.latest_weather && data.latest_weather.length > 0) {
                        data.latest_weather.slice(0, 3).forEach(w => {
                            const li = document.createElement('li');
                            li.className = 'list-group-item d-flex justify-content-between align-items-center px-0 py-2 border-0 bg-transparent';
                            li.innerHTML = `
                                <div class="d-flex align-items-center gap-2">
                                    <i class="bi bi-cloud-sun text-primary"></i>
                                    <span class="small fw-semibold text-dark">${w.city}</span>
                                </div>
                                <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill small">${w.temperature.toFixed(1)}°C</span>
                            `;
                            weatherContainer.appendChild(li);
                        });
                    } else {
                        weatherContainer.innerHTML = '<li class="list-group-item text-center text-muted py-2 border-0 bg-transparent">Data cuaca tidak tersedia</li>';
                    }

                    // Populate news list
                    const newsContainer = document.getElementById('news-list');
                    newsContainer.innerHTML = '';
                    if (data.latest_news && data.latest_news.length > 0) {
                        data.latest_news.slice(0, 4).forEach(n => {
                            const div = document.createElement('div');
                            div.className = 'list-group-item border-0 border-bottom p-3 bg-transparent';
                            
                            // Translate category if needed
                            let categoryText = n.category;
                            if (n.category === 'Logistics') categoryText = 'Logistik';
                            else if (n.category === 'Economy') categoryText = 'Ekonomi';
                            else if (n.category === 'Weather') categoryText = 'Cuaca';
                            else if (n.category === 'Supply Chain') categoryText = 'Rantai Pasok';
                            else if (n.category === 'Trade') categoryText = 'Perdagangan';

                            div.innerHTML = `
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary rounded-pill small">${categoryText}</span>
                                    <small class="text-muted" style="font-size: 0.75rem;">${n.date}</small>
                                </div>
                                <span class="d-block text-dark fw-semibold text-truncate small">${n.title}</span>
                            `;
                            newsContainer.appendChild(div);
                        });
                    } else {
                        newsContainer.innerHTML = '<div class="list-group-item text-center py-4 text-muted border-0 bg-transparent">Berita tidak tersedia</div>';
                    }

                    // Render charts
                    renderTrendsCharts(data.trends);
                }
            })
            .catch(err => console.error("Error loading dashboard API:", err));

        // Fetch Map markers
        fetch('/api/map')
            .then(res => res.json())
            .then(response => {
                if (response.success) {
                    const countries = response.data.countries;
                    const ports = response.data.ports;

                    // Clear previous markers
                    dashboardMapInstance.eachLayer(layer => {
                        if (layer instanceof L.Marker || layer instanceof L.Polyline) {
                            dashboardMapInstance.removeLayer(layer);
                        }
                    });

                    // Add Country Markers
                    countries.forEach(c => {
                        let color = '#198754';
                        if (c.risk_level === 'High') color = '#dc3545';
                        else if (c.risk_level === 'Medium') color = '#ffc107';

                        const countryCircle = L.circleMarker([c.lat, c.lng], {
                            color: color,
                            fillColor: color,
                            fillOpacity: 0.5,
                            radius: 8
                        }).addTo(dashboardMapInstance);

                        let riskLevelText = c.risk_level === 'High' ? 'Risiko Tinggi' : (c.risk_level === 'Medium' ? 'Risiko Sedang' : 'Risiko Rendah');
                        countryCircle.bindPopup(`
                            <strong>${c.name}</strong><br>
                            Tingkat Risiko: ${c.risk_score}% (${riskLevelText})<br>
                            <div class="mt-2 d-flex gap-1">
                                <a href="/countries/${c.id}" class="btn btn-xs btn-primary text-white btn-sm py-0 px-2 fs-7"><i class="bi bi-info-circle"></i> Detail</a>
                                <a href="/news?country_id=${c.id}" class="btn btn-xs btn-outline-dark btn-sm py-0 px-2 fs-7"><i class="bi bi-newspaper"></i> Berita</a>
                            </div>
                        `);
                    });

                    // Add Port Markers (limit to 50 for dashboard performance)
                    ports.slice(0, 50).forEach(p => {
                        const portMarker = L.circleMarker([p.lat, p.lng], {
                            color: '#0dcaf0',
                            fillColor: '#0dcaf0',
                            fillOpacity: 0.8,
                            radius: 4
                        }).addTo(dashboardMapInstance);

                        let portStatusText = p.status === 'Open' ? 'Buka' : (p.status === 'Busy' ? 'Sibuk' : (p.status === 'Maintenance' ? 'Pemeliharaan' : 'Tutup'));
                        portMarker.bindPopup(`<strong>${p.name}</strong><br>${p.city}, ${p.country}<br>Status: ${portStatusText}`);
                    });
                }
            });

        // Fetch Shipments
        fetch('/api/shipments')
            .then(res => res.json())
            .then(response => {
                if (response.success) {
                    const shipments = response.data.data;
                    const tbody = document.getElementById('shipments-table-body');
                    tbody.innerHTML = '';
                    if (shipments && shipments.length > 0) {
                        shipments.slice(0, 5).forEach(s => {
                            let badgeClass = 'bg-secondary';
                            let statusText = s.status;

                            if (s.status === 'In Transit') {
                                badgeClass = 'bg-primary';
                                statusText = 'Dalam Perjalanan';
                            } else if (s.status === 'Delayed') {
                                badgeClass = 'bg-warning text-dark';
                                statusText = 'Tertunda';
                            } else if (s.status === 'Arrived') {
                                badgeClass = 'bg-success';
                                statusText = 'Terkirim';
                            } else if (s.status === 'Pending') {
                                badgeClass = 'bg-secondary';
                                statusText = 'Perencanaan';
                            } else if (s.status === 'Cancelled') {
                                badgeClass = 'bg-danger';
                                statusText = 'Dibatalkan';
                            }

                            const origin = s.origin_port ? s.origin_port.port_name : (s.origin_country ? s.origin_country.name : 'Tidak Diketahui');
                            const dest = s.destination_port ? s.destination_port.port_name : (s.destination_country ? s.destination_country.name : 'Tidak Diketahui');

                            const tr = document.createElement('tr');
                            tr.innerHTML = `
                                <td><span class="font-monospace fw-semibold text-dark">${s.tracking_number}</span></td>
                                <td>${s.cargo_type}</td>
                                <td><small class="text-muted">${origin} ➔ ${dest}</small></td>
                                <td><span class="badge ${badgeClass} rounded-pill">${statusText}</span></td>
                            `;
                            tbody.appendChild(tr);
                        });
                    } else {
                        tbody.innerHTML = '<tr><td colspan="4" class="text-center py-4 text-muted">Pengiriman tidak tersedia</td></tr>';
                    }
                }
            });
    }

    function renderTrendsCharts(trends) {
        // Economy Trend (GDP and Inflation)
        const economyData = trends.economy || [];
        const years = economyData.map(e => e.year);
        const gdps = economyData.map(e => e.avg_gdp);
        const inflations = economyData.map(e => e.avg_inflation);

        // GDP Chart
        const gdpCtx = document.getElementById('gdpChart').getContext('2d');
        if (gdpChartInstance) gdpChartInstance.destroy();
        gdpChartInstance = new Chart(gdpCtx, {
            type: 'line',
            data: {
                labels: years,
                datasets: [{
                    label: 'Rata-rata PDB ($ Miliar)',
                    data: gdps,
                    borderColor: '#D4AF37',
                    backgroundColor: 'rgba(212, 175, 55, 0.15)',
                    fill: true,
                    tension: 0.3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: false } }
            }
        });

        // Inflation Chart
        const infCtx = document.getElementById('inflationChart').getContext('2d');
        if (inflationChartInstance) inflationChartInstance.destroy();
        inflationChartInstance = new Chart(infCtx, {
            type: 'line',
            data: {
                labels: years,
                datasets: [{
                    label: 'Rata-rata Inflasi (%)',
                    data: inflations,
                    borderColor: '#B8860B',
                    backgroundColor: 'rgba(184, 134, 11, 0.15)',
                    fill: true,
                    tension: 0.3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true } }
            }
        });

        // Risk Score Trend
        const riskData = trends.risk || [];
        const riskDates = riskData.map(r => r.date);
        const riskScores = riskData.map(r => r.avg_score);

        const riskCtx = document.getElementById('riskChart').getContext('2d');
        if (riskChartInstance) riskChartInstance.destroy();
        riskChartInstance = new Chart(riskCtx, {
            type: 'line',
            data: {
                labels: riskDates,
                datasets: [{
                    label: 'Indeks Risiko',
                    data: riskScores,
                    borderColor: '#1F2937',
                    backgroundColor: 'rgba(31, 41, 55, 0.1)',
                    fill: true,
                    tension: 0.3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { y: { min: 0, max: 100 } }
            }
        });

        // Shipment Volume Trend
        const shipmentData = trends.shipment || [];
        const shipmentMonths = shipmentData.map(s => s.month);
        const shipmentCounts = shipmentData.map(s => s.total);

        const shipmentCtx = document.getElementById('shipmentChart').getContext('2d');
        if (shipmentChartInstance) shipmentChartInstance.destroy();
        shipmentChartInstance = new Chart(shipmentCtx, {
            type: 'bar',
            data: {
                labels: shipmentMonths,
                datasets: [{
                    label: 'Jumlah Pengiriman',
                    data: shipmentCounts,
                    backgroundColor: '#F4D03F',
                    borderRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true } }
            }
        });
    }
</script>
@endpush