@extends('layouts.app')

@section('title', 'Skor Risiko - SupplyGuard')

@section('content')
<div class="container-fluid px-0">
    <!-- Header -->
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="fw-bold mb-1 text-dark" style="letter-spacing: -0.03em;">⚠️ Mesin Penilaian Risiko</h1>
            <p class="text-muted mb-0">Evaluasi indeks risiko rantai pasok nasional yang dihitung dari faktor logistik multi-dimensi</p>
        </div>
        @if(Auth::user() && Auth::user()->role === 'administrator')
        <div>
            <a href="{{ route('risk-score.create') }}" class="btn btn-primary d-flex align-items-center gap-2 hover-lift px-3 shadow-sm">
                <i class="bi bi-plus-circle"></i> Tambah Entri Risiko
            </a>
        </div>
        @endif
    </div>

    <!-- Alert status -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-4 border-0 shadow-sm mb-4" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Table Card -->
    <div class="card-premium">
        <div class="card-premium-header">
            <span class="fs-6 fw-bold"><i class="bi bi-file-earmark-spreadsheet text-primary me-2"></i> Evaluasi Risiko Negara</span>
        </div>
        <div class="card-premium-body p-0">
            <div class="table-responsive">
                <table class="table table-modern">
                    <thead>
                        <tr>
                            <th width="60">No</th>
                            <th>Negara</th>
                            <th>Skor Cuaca</th>
                            <th>Skor Mata Uang</th>
                            <th>Skor Ekonomi</th>
                            <th>Skor Pelabuhan</th>
                            <th>Skor Total</th>
                            <th>Tingkat Risiko</th>
                            @if(Auth::user() && Auth::user()->role === 'administrator')
                            <th width="150" class="text-center">Aksi</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($riskScores as $index => $item)
                            @php
                                $riskClass = 'bg-success bg-opacity-10 text-success';
                                if ($item->risk_level === 'High') {
                                    $riskClass = 'bg-danger bg-opacity-10 text-danger';
                                } else if ($item->risk_level === 'Medium') {
                                    $riskClass = 'bg-warning bg-opacity-10 text-warning';
                                }
                            @endphp
                            <tr>
                                <td>{{ $riskScores->firstItem() + $index }}</td>
                                <td><strong>{{ $item->country->name ?? 'Tidak Diketahui' }}</strong></td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="progress flex-grow-1" style="height: 6px; width: 60px;">
                                            <div class="progress-bar bg-primary" role="progressbar" style="width: {{ $item->weather_score }}%"></div>
                                        </div>
                                        <small class="fw-semibold text-muted">{{ number_format($item->weather_score, 0) }}%</small>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="progress flex-grow-1" style="height: 6px; width: 60px;">
                                            <div class="progress-bar bg-info" role="progressbar" style="width: {{ $item->currency_score }}%"></div>
                                        </div>
                                        <small class="fw-semibold text-muted">{{ number_format($item->currency_score, 0) }}%</small>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="progress flex-grow-1" style="height: 6px; width: 60px;">
                                            <div class="progress-bar bg-success" role="progressbar" style="width: {{ $item->economy_score }}%"></div>
                                        </div>
                                        <small class="fw-semibold text-muted">{{ number_format($item->economy_score, 0) }}%</small>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="progress flex-grow-1" style="height: 6px; width: 60px;">
                                            <div class="progress-bar bg-warning" role="progressbar" style="width: {{ $item->port_score }}%"></div>
                                        </div>
                                        <small class="fw-semibold text-muted">{{ number_format($item->port_score, 0) }}%</small>
                                    </div>
                                </td>
                                <td class="fw-bold text-dark fs-6">{{ number_format($item->total_score, 1) }}%</td>
                                <td>
                                    <span class="badge {{ $riskClass }} rounded-pill px-2.5 py-1 fw-bold">{{ $item->risk_level }}</span>
                                </td>
                                @if(Auth::user() && Auth::user()->role === 'administrator')
                                <td class="text-center">
                                    <div class="d-flex justify-content-center gap-2">
                                        <a href="{{ route('risk-score.edit', $item->id) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">Edit</a>
                                        <form action="{{ route('risk-score.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Hapus log penilaian risiko ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-3">Hapus</button>
                                        </form>
                                    </div>
                                </td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ Auth::user() && Auth::user()->role === 'administrator' ? 9 : 8 }}" class="text-center py-4 text-muted">Tidak ada skor risiko yang terdaftar di direktori.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <!-- Pagination footer -->
        <div class="card-footer bg-white border-0 py-3 d-flex justify-content-between align-items-center">
            <div class="text-muted small">
                Menampilkan {{ $riskScores->firstItem() ?? 0 }} hingga {{ $riskScores->lastItem() ?? 0 }} dari {{ $riskScores->total() ?? 0 }} entri
            </div>
            <div>
                {{ $riskScores->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
</div>
@endsection