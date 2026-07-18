@extends('layouts.app')

@section('title', 'Perencanaan Pengiriman - SupplyGuard')

@section('content')
<div class="container-fluid px-0">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="fw-bold mb-1 text-dark" style="letter-spacing: -0.03em;">📅 Perencanaan Pengiriman</h1>
            <p class="text-muted mb-0">Jadwalkan dan kelola rencana pengiriman kargo lintas negara</p>
        </div>
        <button class="btn btn-primary d-flex align-items-center gap-2 hover-lift px-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#addShipmentModal">
            <i class="bi bi-plus-circle"></i> Tambah Rencana
        </button>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card-stat primary p-3">
                <h6 class="text-muted text-uppercase small mb-1">Total Rencana</h6>
                <h3 class="mb-0 fw-bold" id="stat-total">0</h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card-stat warning p-3">
                <h6 class="text-muted text-uppercase small mb-1">Dalam Perjalanan</h6>
                <h3 class="mb-0 fw-bold" id="stat-transit">0</h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card-stat danger p-3">
                <h6 class="text-muted text-uppercase small mb-1">Tertunda</h6>
                <h3 class="mb-0 fw-bold" id="stat-delayed">0</h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card-stat success p-3">
                <h6 class="text-muted text-uppercase small mb-1">Perencanaan</h6>
                <h3 class="mb-0 fw-bold" id="stat-pending">0</h3>
            </div>
        </div>
    </div>

    <div class="card-premium">
        <div class="card-premium-header">
            <span class="fs-6 fw-bold"><i class="bi bi-calendar3 text-primary me-2"></i> Kalender Rencana Pengiriman</span>
        </div>
        <div class="card-premium-body p-0">
            <div class="table-responsive">
                <table class="table table-modern">
                    <thead>
                        <tr>
                            <th>Judul Rencana</th>
                            <th>Nomor Pelacakan</th>
                            <th>Rute</th>
                            <th>Tanggal Mulai</th>
                            <th>Tanggal Selesai</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody id="planner-table">
                        <tr><td colspan="6" class="text-center py-4 text-muted">Memuat data...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah Rencana -->
<div class="modal fade" id="addShipmentModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Tambah Rencana Pengiriman</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="shipment-form">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Judul Rencana</label>
                            <input type="text" name="title" class="form-control" required placeholder="Contoh: Pengiriman Elektronik Q3">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Nomor Pelacakan</label>
                            <input type="text" name="tracking_number" class="form-control" required placeholder="SG-2026-001">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Nomor Kontainer</label>
                            <input type="text" name="container_number" class="form-control" required placeholder="CONT-12345">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Jenis Kargo</label>
                            <input type="text" name="cargo_type" class="form-control" required placeholder="Elektronik, Tekstil, dll">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Negara Asal</label>
                            <select name="origin_country_id" id="origin-country" class="form-select" required></select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Negara Tujuan</label>
                            <select name="destination_country_id" id="dest-country" class="form-select" required></select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Estimasi Keberangkatan</label>
                            <input type="date" name="estimated_departure" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Estimasi Kedatangan</label>
                            <input type="date" name="estimated_arrival" class="form-control" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Deskripsi</label>
                            <textarea name="description" class="form-control" rows="2" placeholder="Catatan tambahan (opsional)"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" id="submit-btn">Simpan Rencana</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        loadCountries();
        loadPlanners();
        loadStats();

        document.getElementById('shipment-form').addEventListener('submit', function(e) {
            e.preventDefault();
            const btn = document.getElementById('submit-btn');
            btn.disabled = true;
            btn.textContent = 'Menyimpan...';

            const formData = new FormData(this);
            const data = Object.fromEntries(formData.entries());

            fetch('/api/shipments', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify(data)
            })
            .then(res => res.json())
            .then(response => {
                btn.disabled = false;
                btn.textContent = 'Simpan Rencana';
                if (response.success) {
                    bootstrap.Modal.getInstance(document.getElementById('addShipmentModal')).hide();
                    this.reset();
                    loadPlanners();
                    loadStats();
                    alert('Rencana pengiriman berhasil ditambahkan!');
                } else {
                    alert('Gagal: ' + (response.message || 'Validasi gagal'));
                }
            })
            .catch(() => {
                btn.disabled = false;
                btn.textContent = 'Simpan Rencana';
                alert('Terjadi kesalahan saat menyimpan.');
            });
        });
    });

    function loadCountries() {
        fetch('/api/countries?per_page=300')
            .then(res => res.json())
            .then(response => {
                if (!response.success) return;
                const countries = response.data.data || response.data;
                const origin = document.getElementById('origin-country');
                const dest = document.getElementById('dest-country');
                let options = '<option value="">-- Pilih Negara --</option>';
                countries.forEach(c => {
                    options += `<option value="${c.id}">${c.name}</option>`;
                });
                origin.innerHTML = options;
                dest.innerHTML = options;
            });
    }

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

    function loadPlanners() {
        fetch('/api/shipments/planners')
            .then(res => res.json())
            .then(response => {
                const tbody = document.getElementById('planner-table');
                if (!response.success || !response.data.length) {
                    tbody.innerHTML = '<tr><td colspan="6" class="text-center py-4 text-muted">Belum ada rencana pengiriman.</td></tr>';
                    return;
                }
                tbody.innerHTML = '';
                response.data.forEach(p => {
                    const s = p.shipment || {};
                    const origin = s.origin_country ? s.origin_country.name : '-';
                    const dest = s.destination_country ? s.destination_country.name : '-';
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td><strong>${p.title}</strong></td>
                        <td><span class="font-monospace small">${s.tracking_number || '-'}</span></td>
                        <td><small>${origin} → ${dest}</small></td>
                        <td>${p.start_date ? new Date(p.start_date).toLocaleDateString('id-ID') : '-'}</td>
                        <td>${p.end_date ? new Date(p.end_date).toLocaleDateString('id-ID') : '-'}</td>
                        <td>${statusBadge(s.status || 'Pending')}</td>
                    `;
                    tbody.appendChild(tr);
                });
            });
    }

    function loadStats() {
        fetch('/api/shipments/stats')
            .then(res => res.json())
            .then(response => {
                if (!response.success) return;
                const s = response.data.statuses;
                let total = 0;
                Object.values(s).forEach(v => total += v);
                document.getElementById('stat-total').textContent = total;
                document.getElementById('stat-transit').textContent = s['In Transit'] || 0;
                document.getElementById('stat-delayed').textContent = s['Delayed'] || 0;
                document.getElementById('stat-pending').textContent = s['Pending'] || 0;
            });
    }
</script>
@endpush
