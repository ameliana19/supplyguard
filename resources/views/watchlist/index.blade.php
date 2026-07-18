@extends('layouts.app')

@section('title', 'Daftar Pantauan - SupplyGuard')

@section('content')
<div class="container-fluid px-0">
    <!-- Header -->
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="fw-bold mb-1 text-dark" style="letter-spacing: -0.03em;">⭐ Ruang Kontrol Daftar Pantauan</h1>
            <p class="text-muted mb-0">Pantau wilayah yang ditandai khusus dan indikator risiko untuk koridor kargo utama</p>
        </div>
    </div>

    <!-- Alert notifications -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-4 mb-4" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm rounded-4 mb-4" role="alert">
            <i class="bi bi-exclamation-octagon-fill me-2"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Add Watchlist Form -->
    @if(Auth::user() && Auth::user()->role === 'administrator')
    <div class="card-premium mb-4">
        <div class="card-premium-header">
            <span class="fs-6 fw-bold"><i class="bi bi-bookmark-plus text-primary me-2"></i> Tambah Negara ke Daftar Pantauan</span>
        </div>
        <div class="card-premium-body">
            <form action="{{ route('watchlist.store') }}" method="POST">
                @csrf
                <div class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Pilih Negara</label>
                        <select name="country_id" class="form-select border-2" required>
                            <option value="">-- Pilih Negara --</option>
                            @foreach($countries as $country)
                                <option value="{{ $country->id }}">{{ $country->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-5">
                        <label class="form-label fw-semibold">Catatan Pemantauan</label>
                        <input type="text" name="note" class="form-control border-2" placeholder="contoh: Pemasok bahan baku kritis, peringatan mogok pelabuhan...">
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary w-100 hover-lift fw-bold py-2 shadow-sm">
                            <i class="bi bi-plus-circle me-1"></i> Tambah Negara
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    @endif

    <!-- Cards Grid -->
    <div class="row g-4">
        @forelse($watchlists as $item)
            @php
                $riskData = optional($item->country)->riskScores()->latest()->first();
                $riskVal = $riskData->total_score ?? 15;
                $level = strtolower($riskData->risk_level ?? 'low');

                $badgeClass = 'bg-success bg-opacity-10 text-success';
                $cardAccent = 'success';
                
                if ($level === 'high' || $riskVal >= 70) {
                    $badgeClass = 'bg-danger bg-opacity-10 text-danger';
                    $cardAccent = 'danger';
                } else if ($level === 'medium' || $riskVal >= 35) {
                    $badgeClass = 'bg-warning bg-opacity-10 text-warning';
                    $cardAccent = 'warning';
                }
            @endphp
            <div class="col-md-4 col-lg-3">
                <div class="card-premium h-100 mb-0 hover-lift" style="border-top: 4px solid var(--{{ $cardAccent }}-gradient);">
                    <div class="card-body p-4 text-center d-flex flex-column justify-content-between h-100">
                        <div>
                            <span class="d-block fw-bold text-dark fs-5 mb-1">{{ $item->country->name ?? '-' }}</span>
                            <small class="text-muted text-uppercase fw-semibold" style="font-size:0.7rem;">Risiko Saat Ini</small>
                            
                            <h2 class="fw-bold my-3 text-{{ $cardAccent === 'warning' ? 'warning' : $cardAccent }}" style="font-size: 2.25rem;">
                                {{ number_format($riskVal, 0) }}%
                            </h2>

                            <span class="badge {{ $badgeClass }} rounded-pill px-3 py-1 fw-bold mb-3">
                                RISIKO {{ strtoupper($level) }}
                            </span>

                            <div class="progress mb-4" style="height: 6px;">
                                <div class="progress-bar bg-{{ $cardAccent }}" role="progressbar" style="width: {{ $riskVal }}%"></div>
                            </div>

                            <p class="text-muted small border-top pt-3 mb-0">
                                <i class="bi bi-sticky text-secondary me-1"></i> {{ $item->note ?: 'Tidak ada catatan' }}
                            </p>
                        </div>

                        @if(Auth::user() && Auth::user()->role === 'administrator')
                        <div class="mt-4">
                            <form action="{{ route('watchlist.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Hapus negara ini dari daftar pantauan?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger w-100 rounded-pill">
                                    <i class="bi bi-trash"></i> Hapus dari Pantauan
                                </button>
                            </form>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="card-premium py-5 text-center">
                    <i class="bi bi-star text-muted fs-1 mb-3"></i>
                    <h5 class="fw-bold text-dark">Daftar Pantauan saat ini kosong</h5>
                    <p class="text-muted">Daftarkan negara di ruang kontrol daftar pantauan untuk melacak metrik langsung mereka.</p>
                </div>
            </div>
        @endforelse
    </div>
</div>
@endsection