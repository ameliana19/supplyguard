@extends('layouts.app')

@section('title', 'Riwayat Pengiriman - SupplyGuard')

@section('content')
<div class="container-fluid px-0">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="fw-bold mb-1 text-dark" style="letter-spacing: -0.03em;">🚚 Riwayat Pengiriman</h1>
            <p class="text-muted mb-0">Lacak status dan riwayat perjalanan kargo secara detail</p>
        </div>
    </div>

    <!-- Pencarian -->
    <div class="card-premium mb-4">
        <div class="card-premium-body">
            <div class="row g-3 align-items-end">
                <div class="col-md-8">
                    <label class="form-label fw-semibold">Cari Nomor Pelacakan</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                        <input type="text" id="tracking-input" class="form-control" placeholder="Masukkan nomor pelacakan, contoh: SG-2026-001">
                    </div>
                </div>
                <div class="col-md-4">
                    <button onclick="searchTracking()" class="btn btn-primary w-100 py-2">
                        <i class="bi bi-geo-alt me-1"></i> Lacak Pengiriman
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Daftar Pengiriman -->
    <div class="card-premium mb-4">
        <div class="card-premium-header">
            <span class="fs-6 fw-bold"><i class="bi bi-list-ul text-primary me-2"></i> Semua Pengiriman</span>
        </div>
        <div class="card-premium-body p-0">
            <div class="table-responsive">
                <table class="table table-modern">
                    <thead>
                        <tr>
                            <th>Nomor Pelacakan</th>
                            <th>Kargo</th>
                            <th>Rute</th>
                            <th>Keberangkatan</th>
                            <th>Kedatangan</th>
                            <th>Status</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="shipments-list">
                        <tr><td colspan="7" class="text-center py-4 text-muted">Memuat data...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Detail Riwayat -->
    <div class="card-premium" id="history-panel" style="display:none;">
        <div class="card-premium-header">
            <span class="fs-6 fw-bold"><i class="bi bi-clock-history text-info me-2"></i> Riwayat Pelacakan: <span id="history-tracking"></span></span>
        </div>
        <div class="card-premium-body">
            <div id="shipment-info" class="mb-4"></div>
            <div class="timeline" id="history-timeline"></div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .timeline { position: relative; padding-left: 30px; }
    .timeline::before { content: ''; position: absolute; left: 10px; top: 0; bottom: 0; width: 2px; background: #e2e8f0; }
    .timeline-item { position: relative; margin-bottom: 1.5rem; }
    .timeline-item::before { content: ''; position: absolute; left: -24px; top: 4px; width: 12px; height: 12px; border-radius: 50%; background: #D4AF37; border: 2px solid #fff; box-shadow: 0 0 0 2px #D4AF37; }
    .timeline-item.delayed::before { background: #dc3545; box-shadow: 0 0 0 2px #dc3545; }
    .timeline-item.arrived::before { background: #198754; box-shadow: 0 0 0 2px #198754; }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', loadShipments);

    function statusLabel(s) {
        const map = { 'In Transit': 'Dalam Perjalanan', Delayed: 'Tertunda', Pending: 'Perencanaan', Arrived: 'Terkirim', Cancelled: 'Dibatalkan' };
        return map[s] || s;
    }

    function statusBadge(s) {
        let cls = 'bg-secondary';
        if (s === 'In Transit') cls = 'bg-primary';
        else if (s === 'Delayed') cls = 'bg-warning text-dark';
        else if (s === 'Arrived') cls = 'bg-success';
        else if (s === 'Cancelled') cls = 'bg-danger';
        return `<span class="badge ${cls} rounded-pill">${statusLabel(s)}</span>`;
    }

    function loadShipments() {
        fetch('/api/shipments')
            .then(res => res.json())
            .then(response => {
                const tbody = document.getElementById('shipments-list');
                if (!response.success) {
                    tbody.innerHTML = '<tr><td colspan="7" class="text-center py-4 text-danger">Gagal memuat data.</td></tr>';
                    return;
                }
                const shipments = response.data.data || [];
                if (!shipments.length) {
                    tbody.innerHTML = '<tr><td colspan="7" class="text-center py-4 text-muted">Belum ada pengiriman.</td></tr>';
                    return;
                }
                tbody.innerHTML = '';
                shipments.forEach(s => {
                    const origin = s.origin_port ? s.origin_port.port_name : (s.origin_country ? s.origin_country.name : '-');
                    const dest = s.destination_port ? s.destination_port.port_name : (s.destination_country ? s.destination_country.name : '-');
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td><span class="font-monospace fw-semibold">${s.tracking_number}</span></td>
                        <td>${s.cargo_type}</td>
                        <td><small class="text-muted">${origin} → ${dest}</small></td>
                        <td>${s.estimated_departure ? new Date(s.estimated_departure).toLocaleDateString('id-ID') : '-'}</td>
                        <td>${s.estimated_arrival ? new Date(s.estimated_arrival).toLocaleDateString('id-ID') : '-'}</td>
                        <td>${statusBadge(s.status)}</td>
                        <td>
                            <div style="display: flex; justify-content: center; align-items: center; gap: 10px; flex-wrap: nowrap;">
                                <button onclick="viewHistory('${s.tracking_number}')" class="btn btn-sm btn-outline-primary rounded-pill px-2"><i class="bi bi-geo-alt"></i> Lacak</button>
                                <a href="/shipments/${s.id}" class="btn btn-sm btn-outline-dark rounded-pill px-2"><i class="bi bi-info-circle"></i> Detail</a>
                            </div>
                        </td>
                    `;
                    tbody.appendChild(tr);
                });
            });
    }

    function searchTracking() {
        const tracking = document.getElementById('tracking-input').value.trim();
        if (!tracking) return alert('Masukkan nomor pelacakan.');
        viewHistory(tracking);
    }

    function viewHistory(tracking) {
        fetch(`/api/shipments/history/${encodeURIComponent(tracking)}`)
            .then(res => res.json())
            .then(response => {
                if (!response.success) {
                    alert('Pengiriman tidak ditemukan.');
                    return;
                }
                const s = response.data;
                document.getElementById('history-panel').style.display = 'block';
                document.getElementById('history-tracking').textContent = s.tracking_number;

                const origin = s.origin_port ? s.origin_port.port_name : (s.origin_country ? s.origin_country.name : '-');
                const dest = s.destination_port ? s.destination_port.port_name : (s.destination_country ? s.destination_country.name : '-');

                document.getElementById('shipment-info').innerHTML = `
                    <div class="row g-3">
                        <div class="col-md-3"><small class="text-muted">Kontainer</small><div class="fw-semibold">${s.container_number}</div></div>
                        <div class="col-md-3"><small class="text-muted">Kargo</small><div class="fw-semibold">${s.cargo_type}</div></div>
                        <div class="col-md-3"><small class="text-muted">Rute</small><div class="fw-semibold">${origin} → ${dest}</div></div>
                        <div class="col-md-3"><small class="text-muted">Status</small><div>${statusBadge(s.status)}</div></div>
                    </div>
                `;

                const timeline = document.getElementById('history-timeline');
                const histories = s.histories || [];
                if (!histories.length) {
                    timeline.innerHTML = '<p class="text-muted">Belum ada riwayat pelacakan.</p>';
                    return;
                }
                timeline.innerHTML = '';
                histories.sort((a, b) => new Date(b.event_time) - new Date(a.event_time));
                histories.forEach(h => {
                    let cls = '';
                    if (h.status === 'Delayed') cls = 'delayed';
                    else if (h.status === 'Arrived') cls = 'arrived';
                    const div = document.createElement('div');
                    div.className = `timeline-item ${cls}`;
                    div.innerHTML = `
                        <div class="d-flex justify-content-between">
                            <strong>${statusLabel(h.status)}</strong>
                            <small class="text-muted">${new Date(h.event_time).toLocaleString('id-ID')}</small>
                        </div>
                        <div class="text-muted small"><i class="bi bi-geo-alt me-1"></i>${h.location}</div>
                        ${h.notes ? `<p class="small mb-0 mt-1">${h.notes}</p>` : ''}
                    `;
                    timeline.appendChild(div);
                });

                document.getElementById('history-panel').scrollIntoView({ behavior: 'smooth' });
            });
    }
</script>
@endpush
