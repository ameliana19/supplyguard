@extends('layouts.app')

@section('title', (Auth::user() && Auth::user()->role === 'user' ? 'Profil Pengguna' : 'Profil Administrator') . ' - SupplyGuard')

@section('content')
<div class="container-fluid px-0">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            @if(Auth::user() && Auth::user()->role === 'user')
                <h1 class="fw-bold mb-1 text-dark" style="letter-spacing: -0.03em;">🛡️ Profil Pengguna</h1>
                <p class="text-muted mb-0">Kelola informasi akun dan keamanan akun SupplyGuard.</p>
            @else
                <h1 class="fw-bold mb-1 text-dark" style="letter-spacing: -0.03em;">🛡️ Profil Administrator</h1>
                <p class="text-muted mb-0">Kelola informasi akun administrator dan keamanan akun SupplyGuard.</p>
            @endif
        </div>
    </div>

    <div class="row g-4">
        <!-- Dashboard Admin Panel Card -->
        <div class="col-lg-6">
            <div class="card-premium h-100">
                <div class="card-premium-header">
                    <span class="fs-6 fw-bold"><i class="fas fa-user-shield text-primary me-2"></i> {{ Auth::user() && Auth::user()->role === 'user' ? 'Hak Akses Pengguna' : 'Hak Akses Administrator' }}</span>
                </div>
                <div class="card-premium-body">
                    <ul class="list-group list-group-flush">
                        @if(Auth::user() && Auth::user()->role === 'user')
                            <li class="list-group-item bg-transparent px-0 border-0 py-2 d-flex align-items-center gap-2 text-dark">
                                <i class="bi bi-check-circle-fill text-success"></i> Lihat Data Negara
                            </li>
                            <li class="list-group-item bg-transparent px-0 border-0 py-2 d-flex align-items-center gap-2 text-dark">
                                <i class="bi bi-check-circle-fill text-success"></i> Lihat Data Cuaca
                            </li>
                            <li class="list-group-item bg-transparent px-0 border-0 py-2 d-flex align-items-center gap-2 text-dark">
                                <i class="bi bi-check-circle-fill text-success"></i> Lihat Data Mata Uang
                            </li>
                            <li class="list-group-item bg-transparent px-0 border-0 py-2 d-flex align-items-center gap-2 text-dark">
                                <i class="bi bi-check-circle-fill text-success"></i> Lihat Data Ekonomi
                            </li>
                            <li class="list-group-item bg-transparent px-0 border-0 py-2 d-flex align-items-center gap-2 text-dark">
                                <i class="bi bi-check-circle-fill text-success"></i> Lihat Data Pelabuhan
                            </li>
                            <li class="list-group-item bg-transparent px-0 border-0 py-2 d-flex align-items-center gap-2 text-dark">
                                <i class="bi bi-check-circle-fill text-success"></i> Lihat Tingkat Risiko
                            </li>
                            <li class="list-group-item bg-transparent px-0 border-0 py-2 d-flex align-items-center gap-2 text-dark">
                                <i class="bi bi-check-circle-fill text-success"></i> Lihat Berita
                            </li>
                            <li class="list-group-item bg-transparent px-0 border-0 py-2 d-flex align-items-center gap-2 text-dark">
                                <i class="bi bi-check-circle-fill text-success"></i> Lihat Peta Dunia
                            </li>
                            <li class="list-group-item bg-transparent px-0 border-0 py-2 d-flex align-items-center gap-2 text-dark">
                                <i class="bi bi-check-circle-fill text-success"></i> Lihat Daftar Pantauan
                            </li>
                            <li class="list-group-item bg-transparent px-0 border-0 py-2 d-flex align-items-center gap-2 text-dark">
                                <i class="bi bi-check-circle-fill text-success"></i> Kelola Profil Pengguna
                            </li>
                        @else
                            <li class="list-group-item bg-transparent px-0 border-0 py-2 d-flex align-items-center gap-2 text-dark">
                                <i class="bi bi-check-circle-fill text-success"></i> Kelola Data Negara
                            </li>
                            <li class="list-group-item bg-transparent px-0 border-0 py-2 d-flex align-items-center gap-2 text-dark">
                                <i class="bi bi-check-circle-fill text-success"></i> Kelola Data Cuaca
                            </li>
                            <li class="list-group-item bg-transparent px-0 border-0 py-2 d-flex align-items-center gap-2 text-dark">
                                <i class="bi bi-check-circle-fill text-success"></i> Kelola Data Mata Uang
                            </li>
                            <li class="list-group-item bg-transparent px-0 border-0 py-2 d-flex align-items-center gap-2 text-dark">
                                <i class="bi bi-check-circle-fill text-success"></i> Kelola Data Ekonomi
                            </li>
                            <li class="list-group-item bg-transparent px-0 border-0 py-2 d-flex align-items-center gap-2 text-dark">
                                <i class="bi bi-check-circle-fill text-success"></i> Kelola Data Pelabuhan
                            </li>
                            <li class="list-group-item bg-transparent px-0 border-0 py-2 d-flex align-items-center gap-2 text-dark">
                                <i class="bi bi-check-circle-fill text-success"></i> Kelola Tingkat Risiko
                            </li>
                            <li class="list-group-item bg-transparent px-0 border-0 py-2 d-flex align-items-center gap-2 text-dark">
                                <i class="bi bi-check-circle-fill text-success"></i> Kelola Berita
                            </li>
                            <li class="list-group-item bg-transparent px-0 border-0 py-2 d-flex align-items-center gap-2 text-dark">
                                <i class="bi bi-check-circle-fill text-success"></i> Kelola Perencanaan Pengiriman
                            </li>
                            <li class="list-group-item bg-transparent px-0 border-0 py-2 d-flex align-items-center gap-2 text-dark">
                                <i class="bi bi-check-circle-fill text-success"></i> Kelola Riwayat Pengiriman
                            </li>
                            <li class="list-group-item bg-transparent px-0 border-0 py-2 d-flex align-items-center gap-2 text-dark">
                                <i class="bi bi-check-circle-fill text-success"></i> Kelola Profil Administrator
                            </li>
                        @endif
                    </ul>
                </div>
            </div>
        </div>

        <!-- Profil & Keamanan Admin -->
        <div class="col-lg-6">
            <!-- Profil Card -->
            <div class="card-premium text-center mb-4">
                <div class="card-premium-body p-4">
                    <div class="mb-3" id="avatar-container">
                        <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 100px; height: 100px; font-size: 2rem; font-weight: 700;" id="avatar-placeholder">--</div>
                        <img id="avatar-img" src="" alt="Avatar" class="rounded-circle border border-3 border-primary" style="width: 100px; height: 100px; display: none; object-fit: cover;">
                    </div>
                    <h5 class="fw-bold mb-1" id="profile-name">Memuat...</h5>
                    <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill" id="profile-role">-</span>
                    <p class="text-muted small mt-2 mb-3" id="profile-email">-</p>
                    <label class="btn btn-outline-primary btn-sm rounded-pill">
                        <i class="bi bi-camera me-1"></i> Ubah Foto
                        <input type="file" id="photo-input" accept="image/*" hidden onchange="uploadPhoto(this)">
                    </label>
                </div>
            </div>

            <!-- Form Edit Profil -->
            <div class="card-premium mb-4">
                <div class="card-premium-header">
                    <span class="fs-6 fw-bold"><i class="bi bi-pencil-square text-primary me-2"></i> Informasi Profil</span>
                </div>
                <div class="card-premium-body">
                    <form id="profile-form">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Nama Lengkap</label>
                                <input type="text" name="full_name" id="full_name" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Email</label>
                                <input type="email" name="email" id="email" class="form-control" required readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Nomor Telepon</label>
                                <input type="text" name="phone_number" id="phone_number" class="form-control" placeholder="Belum diisi">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Perusahaan</label>
                                <input type="text" name="company" id="company" class="form-control" placeholder="Opsional">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Jabatan / Role</label>
                                <input type="text" name="role" id="role" class="form-control" placeholder="Administrator" readonly>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Alamat</label>
                                <textarea name="address" id="address" class="form-control" rows="2" placeholder="Belum diisi"></textarea>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary px-4" id="save-profile-btn">
                                    <i class="bi bi-check-lg me-1"></i> Simpan Perubahan
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Ubah Password -->
            <div class="card-premium mb-4">
                <div class="card-premium-header">
                    <span class="fs-6 fw-bold"><i class="bi bi-shield-lock text-danger me-2"></i> Keamanan Akun</span>
                </div>
                <div class="card-premium-body">
                    <form id="password-form">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Password Saat Ini</label>
                                <input type="password" name="current_password" class="form-control" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Password Baru</label>
                                <input type="password" name="new_password" class="form-control" required minlength="8">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Konfirmasi Password</label>
                                <input type="password" name="new_password_confirmation" class="form-control" required>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-outline-danger px-4" id="save-password-btn">
                                    <i class="bi bi-key me-1"></i> Ubah Password
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Aktivitas Terbaru -->
            <div class="card-premium">
                <div class="card-premium-header">
                    <span class="fs-6 fw-bold"><i class="bi bi-clock-history text-warning me-2"></i> Aktivitas Terbaru</span>
                </div>
                <div class="card-premium-body p-0">
                    <ul class="list-group list-group-flush" id="activity-list">
                        <li class="list-group-item text-center text-muted py-3">Memuat aktivitas...</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        loadProfile();
        loadActivities();

        document.getElementById('profile-form').addEventListener('submit', function(e) {
            e.preventDefault();
            const btn = document.getElementById('save-profile-btn');
            btn.disabled = true;
            const data = {
                full_name: document.getElementById('full_name').value,
                email: document.getElementById('email').value,
                phone_number: document.getElementById('phone_number').value,
                company: document.getElementById('company').value,
                role: document.getElementById('role').value,
                address: document.getElementById('address').value,
            };
            fetch('/api/profile', { method: 'POST', headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' }, body: JSON.stringify(data) })
                .then(res => res.json())
                .then(r => { btn.disabled = false; alert(r.success ? 'Profil berhasil diperbarui!' : 'Gagal memperbarui profil.'); if (r.success) loadProfile(); })
                .catch(() => { btn.disabled = false; alert('Terjadi kesalahan.'); });
        });

        document.getElementById('password-form').addEventListener('submit', function(e) {
            e.preventDefault();
            const btn = document.getElementById('save-password-btn');
            btn.disabled = true;
            const fd = new FormData(this);
            const data = Object.fromEntries(fd.entries());
            fetch('/api/profile/password', { method: 'POST', headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' }, body: JSON.stringify(data) })
                .then(res => res.json())
                .then(r => { btn.disabled = false; alert(r.success ? 'Password berhasil diubah!' : (r.message || 'Gagal mengubah password.')); if (r.success) this.reset(); })
                .catch(() => { btn.disabled = false; alert('Terjadi kesalahan.'); });
        });
    });

    function loadProfile() {
        fetch('/api/profile')
            .then(res => res.json())
            .then(response => {
                if (!response.success) return;
                const user = response.data;
                const profile = user.profile || {};
                document.getElementById('profile-name').textContent = profile.full_name || user.name;
                document.getElementById('profile-email').textContent = user.email;
                
                const rawRole = user.role || profile.role || '';
                let displayRole = rawRole;
                if (rawRole.toLowerCase() === 'user') {
                    displayRole = 'Pengguna';
                } else if (rawRole.toLowerCase() === 'administrator') {
                    displayRole = 'Administrator';
                }
                
                document.getElementById('profile-role').textContent = displayRole;
                document.getElementById('full_name').value = profile.full_name || user.name;
                document.getElementById('email').value = user.email;
                document.getElementById('phone_number').value = profile.phone_number || '';
                document.getElementById('company').value = profile.company || '';
                document.getElementById('role').value = displayRole;
                document.getElementById('address').value = profile.address || '';

                const initials = (profile.full_name || user.name || 'U').substring(0, 2).toUpperCase();
                document.getElementById('avatar-placeholder').textContent = initials;

                if (profile.photo) {
                    document.getElementById('avatar-img').src = profile.photo;
                    document.getElementById('avatar-img').style.display = 'inline';
                    document.getElementById('avatar-placeholder').style.display = 'none';
                }
            });
    }

    function translateActivity(activityText) {
        // Registered/Updated Shipment: SG-2026-001 (Status: In Transit)
        // -> Pengiriman SG-2026-001 diperbarui<br>Status: Dalam Perjalanan
        const shipmentRegex = /Registered\/Updated Shipment:\s*([^\s(]+)\s*\(Status:\s*([^)]+)\)/i;
        const shipmentMatch = activityText.match(shipmentRegex);
        if (shipmentMatch) {
            const code = shipmentMatch[1];
            const status = shipmentMatch[2].trim();
            let statusIndo = status;
            if (status.toLowerCase() === 'in transit') {
                statusIndo = 'Dalam Perjalanan';
            } else if (status.toLowerCase() === 'pending') {
                statusIndo = 'Tertunda';
            } else if (status.toLowerCase() === 'arrived' || status.toLowerCase() === 'delivered') {
                statusIndo = 'Tiba';
            } else if (status.toLowerCase() === 'delayed') {
                statusIndo = 'Terlambat';
            } else if (status.toLowerCase() === 'cancelled') {
                statusIndo = 'Dibatalkan';
            }
            return `Pengiriman ${code} diperbarui<br>Status: ${statusIndo}`;
        }

        // Added country to Watchlist: American Samoa
        // -> Negara American Samoa ditambahkan ke Daftar Pantauan
        const watchlistRegex = /Added country to Watchlist:\s*(.*)/i;
        const watchlistMatch = activityText.match(watchlistRegex);
        if (watchlistMatch) {
            const country = watchlistMatch[1].trim();
            return `Negara ${country} ditambahkan ke Daftar Pantauan`;
        }

        // Updated profile details -> Detail profil diperbarui
        if (activityText.toLowerCase() === 'updated profile details') {
            return 'Detail profil diperbarui';
        }

        // Uploaded new profile photo -> Foto profil baru diunggah
        if (activityText.toLowerCase() === 'uploaded new profile photo') {
            return 'Foto profil baru diunggah';
        }

        // Changed security password -> Password keamanan diubah
        if (activityText.toLowerCase() === 'changed security password') {
            return 'Password keamanan diubah';
        }

        return activityText;
    }

    function formatIndonesianDate(timestamp) {
        const d = new Date(timestamp);
        if (isNaN(d.getTime())) return timestamp;
        const day = String(d.getDate()).padStart(2, '0');
        const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
        const month = months[d.getMonth()];
        const year = d.getFullYear();
        const hours = String(d.getHours()).padStart(2, '0');
        const minutes = String(d.getMinutes()).padStart(2, '0');
        const seconds = String(d.getSeconds()).padStart(2, '0');
        return `${day} ${month} ${year} ${hours}:${minutes}:${seconds}`;
    }

    function loadActivities() {
        fetch('/api/profile/activities')
            .then(res => res.json())
            .then(response => {
                const list = document.getElementById('activity-list');
                if (!response.success || !response.data.length) {
                    list.innerHTML = '<li class="list-group-item text-center text-muted py-3">Belum ada aktivitas.</li>';
                    return;
                }
                list.innerHTML = '';
                response.data.forEach(a => {
                    const li = document.createElement('li');
                    li.className = 'list-group-item border-0 py-2 px-3';
                    li.innerHTML = `
                        <div class="small fw-semibold text-dark">${translateActivity(a.activity)}</div>
                        <small class="text-muted">${formatIndonesianDate(a.timestamp)}</small>
                    `;
                    list.appendChild(li);
                });
            });
    }

    function uploadPhoto(input) {
        if (!input.files[0]) return;
        const formData = new FormData();
        formData.append('photo', input.files[0]);
        fetch('/api/profile/photo', { method: 'POST', body: formData })
            .then(res => res.json())
            .then(r => {
                if (r.success) {
                    document.getElementById('avatar-img').src = r.data.photo_url;
                    document.getElementById('avatar-img').style.display = 'inline';
                    document.getElementById('avatar-placeholder').style.display = 'none';
                    alert('Foto profil berhasil diperbarui!');
                } else {
                    alert('Gagal mengunggah foto.');
                }
            });
    }
</script>
@endpush
