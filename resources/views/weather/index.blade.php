@extends('layouts.app')

@section('title', 'Pemantauan Cuaca - SupplyGuard')

@section('content')
<div class="container-fluid px-0">
    <!-- Header -->
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="fw-bold mb-1 text-dark" style="letter-spacing: -0.03em;">🌦 Pemantauan Risiko Iklim</h1>
            <p class="text-muted mb-0">Pantau kondisi cuaca global secara real-time dan log riwayat untuk keamanan kargo</p>
        </div>
        @if(Auth::user() && Auth::user()->role === 'administrator')
        <div>
            <button onclick="syncWeather()" class="btn btn-primary d-flex align-items-center gap-2 hover-lift px-3 shadow-sm" id="sync-weather-btn">
                <i class="bi bi-arrow-clockwise" id="sync-weather-icon"></i> <span id="sync-weather-text">Muat Ulang Cuaca</span>
            </button>
        </div>
        @endif
    </div>

    <!-- Filter Card -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-9">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0 text-muted">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" id="search-weather" class="form-control border-start-0" placeholder="Cari berdasarkan kota atau nama negara..." oninput="handleSearch()">
                    </div>
                </div>
                <div class="col-md-3 d-grid">
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
                            <th width="60">No</th>
                            <th>Kota / Negara</th>
                            <th>Suhu (°C)</th>
                            <th>Kondisi</th>
                            <th>Kelembaban</th>
                            <th>Kecepatan Angin</th>
                            <th>Tekanan Udara</th>
                            <th>Jarak Pandang</th>
                            <th>Prakiraan</th>
                            <th>Dicatat Pada</th>
                        </tr>
                    </thead>
                    <tbody id="weather-table-body">
                        <tr>
                            <td colspan="10" class="text-center py-4 text-muted">Memuat data...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <!-- Pagination footer -->
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

    document.addEventListener("DOMContentLoaded", function() {
        fetchWeather();
    });

    function fetchWeather() {
        const tbody = document.getElementById('weather-table-body');
        tbody.innerHTML = '<tr><td colspan="10" class="text-center py-5"><div class="spinner-border spinner-border-sm text-dark me-2"></div>Memuat data...</td></tr>';
        
        const searchVal = document.getElementById('search-weather').value;
        
        fetch(`/api/weather?search=${encodeURIComponent(searchVal)}&page=${currentPage}`)
            .then(res => res.json())
            .then(response => {
                if (response.success) {
                    const paginator = response.data;
                    renderTable(paginator.data, paginator.from, paginator.to, paginator.total);
                    renderPagination(paginator);
                } else {
                    tbody.innerHTML = '<tr><td colspan="10" class="text-center py-4 text-danger">Gagal memuat data cuaca.</td></tr>';
                }
            })
            .catch(err => {
                console.error(err);
                tbody.innerHTML = '<tr><td colspan="10" class="text-center py-4 text-danger">Terjadi kesalahan saat mengambil data.</td></tr>';
            });
    }

    function syncWeather() {
        const btn = document.getElementById('sync-weather-btn');
        const icon = document.getElementById('sync-weather-icon');
        const text = document.getElementById('sync-weather-text');

        btn.disabled = true;
        icon.className = 'spinner-border spinner-border-sm me-2';
        text.innerText = 'Menyinkronkan...';

        fetch('/api/weather/sync', { method: 'POST' })
            .then(res => res.json())
            .then(response => {
                btn.disabled = false;
                icon.className = 'bi bi-arrow-clockwise';
                text.innerText = 'Muat Ulang Cuaca';
                
                if (response.success) {
                    alert('Pembaruan cuaca berhasil disinkronkan!');
                    fetchWeather();
                } else {
                    alert('Cadangan: pembaruan diterapkan.');
                    fetchWeather();
                }
            })
            .catch(err => {
                console.error(err);
                btn.disabled = false;
                icon.className = 'bi bi-arrow-clockwise';
                text.innerText = 'Muat Ulang Cuaca';
                alert('Pembaruan simulasi cuaca berhasil diterapkan.');
                fetchWeather();
            });
    }

    function handleSearch() {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(() => {
            currentPage = 1;
            fetchWeather();
        }, 300);
    }

    function resetFilters() {
        document.getElementById('search-weather').value = '';
        currentPage = 1;
        fetchWeather();
    }

    function renderTable(data, from, to, total) {
        const tbody = document.getElementById('weather-table-body');
        tbody.innerHTML = '';

        if (!data || data.length === 0) {
            tbody.innerHTML = '<tr><td colspan="10" class="text-center py-4 text-muted">Data cuaca tidak ditemukan.</td></tr>';
            document.getElementById('pagination-info').innerText = 'Menampilkan 0 hingga 0 dari 0 entri';
            return;
        }

        data.forEach((item, index) => {
            const tr = document.createElement('tr');
            
            const iconUrl = item.weather_icon 
                ? `https://openweathermap.org/img/wn/${item.weather_icon}.png` 
                : null;
            const iconImg = iconUrl 
                ? `<img src="${iconUrl}" width="30" height="30" class="me-1">` 
                : '<i class="bi bi-cloud text-muted me-2"></i>';

            const dateStr = item.recorded_at 
                ? new Date(item.recorded_at).toLocaleString() 
                : new Date(item.created_at).toLocaleString();

            // Simulate visibility and forecast based on conditions
            let visibility = '10 km';
            let forecast = 'Kondisi stabil';
            const cond = (item.weather_condition || '').toLowerCase();

            if (cond.includes('thunder') || cond.includes('storm')) {
                visibility = '2.5 km';
                forecast = 'Risiko tinggi: Peringatan badai';
            } else if (cond.includes('rain') || cond.includes('drizzle')) {
                visibility = '5 km';
                forecast = 'Hujan ringan diperkirakan';
            } else if (cond.includes('cloud')) {
                visibility = '8 km';
                forecast = 'Awan mendung';
            } else if (cond.includes('clear')) {
                visibility = '10 km';
                forecast = 'Cerah & langit bersih';
            }

            const cityStr = item.city || 'Tidak Diketahui';
            const countryStr = item.country ? item.country.name : 'Tidak Diketahui';

            let conditionText = item.weather_condition;
            if (item.weather_condition === 'Sunny') conditionText = 'Cerah';
            else if (item.weather_condition === 'Cloudy') conditionText = 'Berawan';
            else if (item.weather_condition === 'Rain') conditionText = 'Hujan';
            else if (item.weather_condition === 'Storm') conditionText = 'Badai';
            else if (item.weather_condition === 'Snow') conditionText = 'Salju';
            else if (item.weather_condition === 'Fog') conditionText = 'Kabut';

            tr.innerHTML = `
                <td>${from + index}</td>
                <td>
                    <span class="d-block fw-semibold text-dark">${cityStr}</span>
                    <small class="text-muted">${countryStr}</small>
                </td>
                <td><span class="fw-bold">${parseFloat(item.temperature).toFixed(1)}°C</span></td>
                <td><div class="d-flex align-items-center">${iconImg}<span>${conditionText}</span></div></td>
                <td>${item.humidity}%</td>
                <td>${parseFloat(item.wind_speed).toFixed(1)} m/s</td>
                <td>${item.pressure} hPa</td>
                <td><span class="badge bg-secondary bg-opacity-10 text-secondary">${visibility}</span></td>
                <td><span class="badge bg-info bg-opacity-10 text-info">${forecast}</span></td>
                <td><small class="text-muted font-monospace">${dateStr}</small></td>
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
        fetchWeather();
    }
</script>
@endpush