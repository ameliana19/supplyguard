@extends('layouts.app')

@section('title', 'Negara - SupplyGuard')

@section('content')
<div class="container-fluid px-0">
    <!-- Header -->
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="fw-bold mb-1 text-dark" style="letter-spacing: -0.03em;">🌎 Pengendalian Risiko Negara</h1>
            <p class="text-muted mb-0">Pantau indeks risiko nasional dan metrik yang disinkronkan dari kumpulan data global</p>
        </div>
        @if(Auth::user() && Auth::user()->role === 'administrator')
        <div>
            <button onclick="syncFromApi()" class="btn btn-primary d-flex align-items-center gap-2 hover-lift px-3 shadow-sm" id="sync-btn">
                <i class="bi bi-cloud-arrow-down" id="sync-icon"></i> <span id="sync-text">Sinkronkan Data API</span>
            </button>
        </div>
        @endif
    </div>

    <!-- Alert Notification -->
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4 d-flex align-items-center" role="alert">
        <i class="bi bi-check-circle-fill me-2 fs-5"></i>
        <div>{{ session('success') }}</div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4 d-flex align-items-center" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i>
        <div>{{ session('error') }}</div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <!-- Search & Filter Controls -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form action="{{ route('countries.index') }}" method="GET">
                <div class="row g-3">
                    <div class="col-md-7">
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0 text-muted">
                                <i class="bi bi-search"></i>
                            </span>
                            <input type="text" name="search" class="form-control border-start-0" placeholder="Cari berdasarkan nama atau ibu kota..." value="{{ $search ?? '' }}">
                        </div>
                    </div>
                    <div class="col-md-2 d-grid">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-search"></i> Cari
                        </button>
                    </div>
                    @if($search)
                    <div class="col-md-3 d-grid">
                        <a href="{{ route('countries.index') }}" class="btn btn-outline-dark">
                            <i class="bi bi-x-circle"></i> Hapus Filter
                        </a>
                    </div>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Data Table Card -->
    <div class="card-premium">
        <div class="card-premium-body p-0">
            <div class="table-responsive">
                <table class="table table-modern">
                    <thead>
                        <tr>
                            <th width="80">Bendera</th>
                            <th>Negara</th>
                            <th>Ibu Kota</th>
                            <th>Wilayah</th>
                            <th>Mata Uang</th>
                            <th>Jumlah Penduduk</th>
                            <th>Koordinat</th>
                            <th>Tingkat Risiko</th>
                            <th width="120" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($countries as $country)
                        <tr>
                            {{-- Flag Rendering --}}
                            <td class="text-center">
                                @if($country->code)
                                    <img src="https://flagcdn.com/w40/{{ strtolower($country->code) }}.png" width="28" height="18" class="border border-1 rounded shadow-sm" alt="flag">
                                @else
                                    <div class="bg-light border text-muted text-center rounded small" style="width:28px; height:18px; font-size:0.6rem;">?</div>
                                @endif
                            </td>
                            <td><strong>{{ $country->name }}</strong></td>
                            <td>{{ $country->capital }}</td>
                            <td><span class="badge bg-secondary bg-opacity-10 text-secondary rounded-pill">{{ $country->region }}</span></td>
                            <td><span class="font-monospace">{{ $country->currency }}</span></td>
                            <td>{{ number_format($country->population) }}</td>
                            <td><small class="text-muted font-monospace">
                                @if($country->latitude && $country->longitude)
                                    {{ number_format($country->latitude, 2) }}, {{ number_format($country->longitude, 2) }}
                                @else
                                    -
                                @endif
                            </small></td>
                            <td>
                                @php
                                    $score = $country->latestRiskScore ? $country->latestRiskScore->total_score : 15;
                                    if ($score >= 70) {
                                        $riskClass = 'bg-danger';
                                        $riskLevel = 'Risiko Tinggi';
                                    } elseif ($score >= 35) {
                                        $riskClass = 'bg-warning text-dark';
                                        $riskLevel = 'Risiko Sedang';
                                    } else {
                                        $riskClass = 'bg-success';
                                        $riskLevel = 'Risiko Rendah';
                                    }
                                @endphp
                                <span class="badge {{ $riskClass }} rounded-pill px-2 py-1">{{ $riskLevel }} ({{ number_format($score, 0) }}%)</span>
                            </td>
                            <td class="text-center">
                                <a href="{{ route('countries.show', $country->id) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">Detail</a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center py-4 text-muted">Tidak ada data negara.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <!-- Pagination footer -->
        <div class="card-footer bg-white border-0 py-3 d-flex justify-content-between align-items-center">
            <div class="text-muted small">
                Menampilkan {{ $countries->firstItem() }} hingga {{ $countries->lastItem() }} dari {{ $countries->total() }} entri
            </div>
            <nav aria-label="Page navigation">
                {{ $countries->links('pagination::bootstrap-4') }}
            </nav>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function syncFromApi() {
        const btn = document.getElementById('sync-btn');
        const icon = document.getElementById('sync-icon');
        const text = document.getElementById('sync-text');

        btn.disabled = true;
        icon.className = 'spinner-border spinner-border-sm me-2';
        text.innerText = 'Menyinkronkan...';

        // Panggil route web yang benar: /countries/import-api
        window.location.href = '/countries/import-api';
    }
</script>
@endpush
