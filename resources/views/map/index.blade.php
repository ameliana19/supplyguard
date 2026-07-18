@extends('layouts.app')

@section('title', 'Peta Dunia - SupplyGuard')

@section('content')
<div class="container-fluid px-0">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="fw-bold mb-1 text-dark" style="letter-spacing: -0.03em;">🗺️ Peta Dunia Interaktif</h1>
            <p class="text-muted mb-0">Visualisasi risiko negara, pelabuhan, dan rute pengiriman secara real-time</p>
        </div>
        <button onclick="loadMapData()" class="btn btn-primary d-flex align-items-center gap-2 hover-lift px-3 shadow-sm">
            <i class="bi bi-arrow-clockwise"></i> Muat Ulang
        </button>
    </div>

    <div class="row g-4">
        <div class="col-lg-9">
            <div class="card-premium mb-0">
                <div class="card-premium-body p-0">
                    <div id="globalMap" style="height: 600px; border-radius: 16px;"></div>
                </div>
            </div>
        </div>
        <div class="col-lg-3">
            <div class="card-premium mb-3">
                <div class="card-premium-header">
                    <span class="fs-6 fw-bold"><i class="bi bi-funnel text-primary me-2"></i> Filter Layer</span>
                </div>
                <div class="card-premium-body">
                    <div class="form-check form-switch mb-2">
                        <input class="form-check-input" type="checkbox" id="show-countries" checked onchange="loadMapData()">
                        <label class="form-check-label" for="show-countries">Negara & Risiko</label>
                    </div>
                    <div class="form-check form-switch mb-2">
                        <input class="form-check-input" type="checkbox" id="show-ports" checked onchange="loadMapData()">
                        <label class="form-check-label" for="show-ports">Pelabuhan</label>
                    </div>
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" id="show-shipments" checked onchange="loadMapData()">
                        <label class="form-check-label" for="show-shipments">Rute Pengiriman</label>
                    </div>
                    <hr>
                    <small class="text-muted d-block mb-2">Legenda Risiko:</small>
                    <div class="d-flex align-items-center gap-2 mb-1"><span class="badge bg-success rounded-circle p-1">&nbsp;</span> Risiko Rendah</div>
                    <div class="d-flex align-items-center gap-2 mb-1"><span class="badge bg-warning rounded-circle p-1">&nbsp;</span> Risiko Sedang</div>
                    <div class="d-flex align-items-center gap-2"><span class="badge bg-danger rounded-circle p-1">&nbsp;</span> Risiko Tinggi</div>
                </div>
            </div>
            <div class="card-premium mb-0">
                <div class="card-premium-header">
                    <span class="fs-6 fw-bold"><i class="bi bi-info-circle text-info me-2"></i> Detail Lokasi</span>
                </div>
                <div class="card-premium-body" id="map-detail-panel">
                    <p class="text-muted small mb-0">Klik marker pada peta untuk melihat detail.</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let globalMap = null;
    let mapLayers = [];

    document.addEventListener('DOMContentLoaded', function() {
        globalMap = L.map('globalMap').setView([-2.5, 118.0], 3);
        L.tileLayer('{{ env("LEAFLET_TILE_URL", "https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png") }}', {
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(globalMap);
        loadMapData();
    });

    function riskLabel(level) {
        if (level === 'High') return 'Risiko Tinggi';
        if (level === 'Medium') return 'Risiko Sedang';
        return 'Risiko Rendah';
    }

    function portStatusLabel(status) {
        const map = { Open: 'Buka', Busy: 'Sibuk', Maintenance: 'Pemeliharaan', Closed: 'Tutup' };
        return map[status] || status;
    }

    function shipmentStatusLabel(status) {
        const map = { 'In Transit': 'Dalam Perjalanan', Delayed: 'Tertunda', Pending: 'Perencanaan', Arrived: 'Terkirim', Cancelled: 'Dibatalkan' };
        return map[status] || status;
    }

    function showDetail(html) {
        document.getElementById('map-detail-panel').innerHTML = html;
    }

    function clearLayers() {
        mapLayers.forEach(layer => globalMap.removeLayer(layer));
        mapLayers = [];
    }

    function loadMapData() {
        clearLayers();
        fetch('/api/map')
            .then(res => res.json())
            .then(response => {
                if (!response.success) return;
                const data = response.data;

                if (document.getElementById('show-countries').checked) {
                    data.countries.forEach(c => {
                        let color = '#198754';
                        if (c.risk_level === 'High') color = '#dc3545';
                        else if (c.risk_level === 'Medium') color = '#ffc107';

                        const marker = L.circleMarker([c.lat, c.lng], {
                            color, fillColor: color, fillOpacity: 0.6, radius: 10
                        }).addTo(globalMap);

                        marker.on('click', () => {
                            showDetail(`
                                <h6 class="fw-bold mb-2">${c.name}</h6>
                                <p class="small mb-1"><strong>Ibu Kota:</strong> ${c.capital || '-'}</p>
                                <p class="small mb-1"><strong>Skor Risiko:</strong> ${c.risk_score.toFixed(1)}%</p>
                                <p class="small mb-3"><strong>Tingkat:</strong> ${riskLabel(c.risk_level)}</p>
                                <div class="d-grid gap-2">
                                    <a href="/countries/${c.id}" class="btn btn-outline-dark text-start btn-sm"><i class="bi bi-info-circle"></i> Detail Negara</a>
                                    <a href="/news?country_id=${c.id}" class="btn btn-outline-primary text-start btn-sm"><i class="bi bi-newspaper"></i> Berita Negara</a>
                                </div>
                            `);
                        });
                        marker.bindPopup(`<strong>${c.name}</strong><br>${riskLabel(c.risk_level)}: ${c.risk_score.toFixed(1)}%`);
                        mapLayers.push(marker);
                    });
                }

                if (document.getElementById('show-ports').checked) {
                    data.ports.forEach(p => {
                        const marker = L.circleMarker([p.lat, p.lng], {
                            color: '#0dcaf0', fillColor: '#0dcaf0', fillOpacity: 0.8, radius: 5
                        }).addTo(globalMap);

                        marker.on('click', () => {
                            showDetail(`
                                <h6 class="fw-bold mb-2">${p.name}</h6>
                                <p class="small mb-1"><strong>Kode:</strong> ${p.code}</p>
                                <p class="small mb-1"><strong>Lokasi:</strong> ${p.city}, ${p.country}</p>
                                <p class="small mb-1"><strong>Kapasitas:</strong> ${p.capacity ? parseInt(p.capacity).toLocaleString() + ' Ton' : '-'}</p>
                                <p class="small mb-0"><strong>Status:</strong> ${portStatusLabel(p.status)}</p>
                            `);
                        });
                        marker.bindPopup(`<strong>${p.name}</strong><br>${p.city}, ${p.country}`);
                        mapLayers.push(marker);
                    });
                }

                if (document.getElementById('show-shipments').checked) {
                    data.shipments.forEach(s => {
                        if (!s.origin.lat || !s.destination.lat) return;

                        const line = L.polyline(
                            [[s.origin.lat, s.origin.lng], [s.destination.lat, s.destination.lng]],
                            { color: s.status === 'Delayed' ? '#dc3545' : '#0d6efd', weight: 2, dashArray: '6 4' }
                        ).addTo(globalMap);

                        line.on('click', () => {
                            showDetail(`
                                <h6 class="fw-bold mb-2">${s.tracking_number}</h6>
                                <p class="small mb-1"><strong>Kargo:</strong> ${s.cargo_type}</p>
                                <p class="small mb-1"><strong>Rute:</strong> ${s.origin.name} → ${s.destination.name}</p>
                                <p class="small mb-0"><strong>Status:</strong> ${shipmentStatusLabel(s.status)}</p>
                            `);
                        });
                        line.bindPopup(`<strong>${s.tracking_number}</strong><br>${s.origin.name} → ${s.destination.name}`);
                        mapLayers.push(line);
                    });
                }
            });
    }
</script>
@endpush
