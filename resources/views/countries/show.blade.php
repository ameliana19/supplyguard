@extends('layouts.app')

@section('title', $country->name . ' - Detail Negara')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex align-items-center mb-4">
        <h2 class="fw-bold mb-0">🌎 Detail Negara: {{ $country->name }}</h2>
        <a href="{{ route('countries.index') }}" class="btn btn-outline-secondary btn-sm ms-auto">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
    </div>

    <div class="row">
        <!-- Metadata -->
        <div class="col-lg-6">
            <div class="card border-0 shadow mb-4">
                <div class="card-header bg-dark text-white fw-bold py-3 d-flex justify-content-between align-items-center">
                    <span>📋 Metadata Negara</span>
                    <a href="{{ route('news.index', ['country_id' => $country->id]) }}" class="btn btn-sm btn-outline-light">
                        <i class="bi bi-newspaper"></i> Berita Negara
                    </a>
                </div>
                <div class="card-body">
                    <table class="table table-bordered mb-0">
                        <tr>
                            <th width="35%">Nama Negara</th>
                            <td>{{ $country->name }}</td>
                        </tr>
                        <tr>
                            <th>Ibu Kota</th>
                            <td>{{ $country->capital }}</td>
                        </tr>
                        <tr>
                            <th>Wilayah</th>
                            <td>{{ $country->region }}</td>
                        </tr>
                        <tr>
                            <th>Mata Uang</th>
                            <td><span class="badge bg-secondary">{{ $country->currency }}</span></td>
                        </tr>
                        <tr>
                            <th>Jumlah Penduduk</th>
                            <td>{{ number_format($country->population) }}</td>
                        </tr>
                        <tr>
                            <th>Koordinat</th>
                            <td>
                                @if($country->latitude && $country->longitude)
                                    {{ $country->latitude }}, {{ $country->longitude }}
                                @else
                                    <span class="text-muted">Tidak ada data koordinat</span>
                                @endif
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Ports list -->
            <div class="card border-0 shadow mb-4">
                <div class="card-header bg-primary text-white fw-bold py-3">
                    🚢 Pelabuhan Terdaftar
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Kode</th>
                                    <th>Nama Pelabuhan</th>
                                    <th>Kota</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($country->ports as $port)
                                    <tr>
                                        <td><code>{{ $port->port_code }}</code></td>
                                        <td>{{ $port->port_name }}</td>
                                        <td>{{ $port->city }}</td>
                                        <td>
                                            @php
                                                $badgeColor = 'success';
                                                $statusText = 'Buka';
                                                if ($port->status === 'Busy') { $badgeColor = 'warning'; $statusText = 'Sibuk'; }
                                                elseif ($port->status === 'Maintenance') { $badgeColor = 'info'; $statusText = 'Pemeliharaan'; }
                                                elseif ($port->status === 'Closed') { $badgeColor = 'danger'; $statusText = 'Tutup'; }
                                            @endphp
                                            <span class="badge bg-{{ $badgeColor }}">{{ $statusText }}</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-3 text-muted">Belum ada pelabuhan terdaftar di negara ini.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- News list -->
            <div class="card border-0 shadow mb-4">
                <div class="card-header bg-info text-white fw-bold py-3 d-flex justify-content-between align-items-center">
                    <span>📰 Berita Rantai Pasok Terkini</span>
                    <a href="{{ route('news.index', ['country_id' => $country->id]) }}" class="btn btn-sm btn-light py-0 px-2 fs-7 fw-semibold">
                        Semua Berita <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        @forelse($latestNews as $art)
                            <a href="{{ $art->url }}" target="_blank" class="list-group-item list-group-item-action p-3 border-0 border-bottom">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary">{{ $art->category }}</span>
                                    <small class="text-muted"><i class="bi bi-calendar-event me-1"></i>{{ \Carbon\Carbon::parse($art->published_at)->locale('id')->translatedFormat('d M Y') }}</small>
                                </div>
                                <h6 class="fw-bold mb-1 text-dark">{{ $art->title_id ?? $art->title }}</h6>
                                <p class="text-muted small mb-0 text-truncate-2" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                                    {{ $art->summary_id ?? $art->summary }}
                                </p>
                            </a>
                        @empty
                            <div class="text-center py-4 text-muted">
                                <i class="bi bi-newspaper fs-3 d-block mb-2"></i>
                                Belum ada berita spesifik untuk negara ini.
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <!-- Map & Weather/Risk Summary -->
        <div class="col-lg-6">
            <!-- Map View -->
            <div class="card border-0 shadow mb-4">
                <div class="card-header bg-dark text-white fw-bold py-3">
                    🗺 Peta Wilayah
                </div>
                <div class="card-body p-0">
                    @if($country->latitude && $country->longitude)
                        <div id="countryMap" style="height: 350px;"></div>
                    @else
                        <div class="d-flex align-items-center justify-content-center bg-light text-muted" style="height: 350px;">
                            <p class="mb-0"><i class="bi bi-geo-alt-fill"></i> Peta tidak dapat dimuat karena koordinat tidak valid.</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Risk and Weather Cards -->
            <div class="row">
                <!-- Latest Weather -->
                <div class="col-md-6 mb-4">
                    <div class="card border-0 shadow h-100">
                        <div class="card-header bg-info text-white fw-bold">
                            🌦 Cuaca Terkini
                        </div>
                        <div class="card-body">
                            @php
                                $w = $country->weather->first();
                            @endphp
                            @if($w)
                                <div class="d-flex align-items-center">
                                    @if($w->weather_icon)
                                        <img src="https://openweathermap.org/img/wn/{{ $w->weather_icon }}@2x.png" alt="weather icon" style="width:60px;">
                                    @endif
                                    <div>
                                        <h3 class="mb-0 fw-bold">{{ $w->temperature }}°C</h3>
                                        <span class="badge bg-primary">
                                            {{ $w->weather_condition === 'Sunny' ? 'Cerah' : ($w->weather_condition === 'Cloudy' ? 'Berawan' : ($w->weather_condition === 'Rain' ? 'Hujan' : ($w->weather_condition === 'Storm' ? 'Badai' : ($w->weather_condition === 'Snow' ? 'Salju' : ($w->weather_condition === 'Fog' ? 'Kabut' : $w->weather_condition))))) }}
                                        </span>
                                    </div>
                                </div>
                                <hr class="my-2">
                                <small class="text-muted">
                                    Kelembaban: {{ $w->humidity }}%<br>
                                    Kecepatan Angin: {{ $w->wind_speed }} km/j<br>
                                    Tekanan Udara: {{ $w->pressure }} hPa
                                </small>
                            @else
                                <p class="text-muted py-3 text-center mb-0">Tidak ada data cuaca terkini.</p>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Latest Risk Score -->
                <div class="col-md-6 mb-4">
                    <div class="card border-0 shadow h-100">
                        <div class="card-header bg-danger text-white fw-bold">
                            ⚠️ Skor Risiko Terbaru
                        </div>
                        <div class="card-body">
                            @php
                                $rs = $country->riskScores()->latest()->first();
                            @endphp
                            @if($rs)
                                <div class="text-center py-2">
                                    <h2 class="fw-bold mb-0 text-danger">{{ number_format($rs->total_score, 1) }}%</h2>
                                    <span class="badge bg-{{ $rs->risk_level === 'High' ? 'danger' : ($rs->risk_level === 'Medium' ? 'warning text-dark' : 'success') }} px-3 py-2 mt-1">
                                        {{ $rs->risk_level === 'High' ? 'Risiko Tinggi' : ($rs->risk_level === 'Medium' ? 'Risiko Sedang' : 'Risiko Rendah') }}
                                    </span>
                                </div>
                                <hr class="my-2">
                                <div class="small">
                                    <div class="d-flex justify-content-between mb-1">
                                        <span>Cuaca:</span> <span>{{ $rs->weather_score }}%</span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-1">
                                        <span>Ekonomi:</span> <span>{{ $rs->economy_score }}%</span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-1">
                                        <span>Mata Uang:</span> <span>{{ $rs->currency_score }}%</span>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <span>Pelabuhan:</span> <span>{{ $rs->port_score }}%</span>
                                    </div>
                                </div>
                            @else
                                <p class="text-muted py-3 text-center mb-0">Belum ada evaluasi risiko.</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@if($country->latitude && $country->longitude)
    @push('scripts')
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const map = L.map('countryMap').setView([{{ $country->latitude }}, {{ $country->longitude }}], 6);

            L.tileLayer('https://{s}.tile.layer.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors'
            }).addTo(map);

            L.marker([{{ $country->latitude }}, {{ $country->longitude }}])
                .addTo(map)
                .bindPopup('<strong>{{ $country->name }}</strong><br>Ibu Kota: {{ $country->capital }}')
                .openPopup();
        });
    </script>
    @push('styles')
    <style>
        #countryMap {
            border-bottom-left-radius: 0.375rem;
            border-bottom-right-radius: 0.375rem;
        }
    </style>
    @endpush
    @endpush
@endif
