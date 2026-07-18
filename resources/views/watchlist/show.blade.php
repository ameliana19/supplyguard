@extends('layouts.app')

@section('title', 'Detail Daftar Pantauan - SupplyGuard')

@section('content')
<div class="container-fluid px-0">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="fw-bold mb-1 text-dark">⭐ Detail Daftar Pantauan</h1>
            <p class="text-muted mb-0">Informasi negara yang sedang dipantau</p>
        </div>
        <a href="{{ route('watchlist.index') }}" class="btn btn-outline-dark">
            <i class="bi bi-arrow-left me-1"></i> Kembali
        </a>
    </div>

    <div class="card-premium">
        <div class="card-premium-body p-4">
            <h3 class="fw-bold text-dark mb-3">{{ $watchlist->country->name ?? 'Negara tidak ditemukan' }}</h3>

            @php
                $risk = optional($watchlist->country)->riskScores()->latest()->first();
            @endphp

            @if($risk)
                <div class="mb-3">
                    <span class="text-muted small">Tingkat Risiko Saat Ini:</span>
                    <span class="badge {{ $risk->risk_level === 'High' ? 'bg-danger' : ($risk->risk_level === 'Medium' ? 'bg-warning text-dark' : 'bg-success') }} rounded-pill ms-2">
                        {{ $risk->risk_level === 'High' ? 'Risiko Tinggi' : ($risk->risk_level === 'Medium' ? 'Risiko Sedang' : 'Risiko Rendah') }}
                        ({{ number_format($risk->total_score, 1) }}%)
                    </span>
                </div>
            @endif

            <div class="border-top pt-3">
                <h6 class="fw-semibold text-muted small text-uppercase">Catatan Pemantauan</h6>
                <p class="mb-0">{{ $watchlist->note ?: 'Tidak ada catatan.' }}</p>
            </div>

            <div class="mt-4 d-flex gap-2">
                <a href="{{ route('watchlist.edit', $watchlist->id) }}" class="btn btn-primary btn-sm rounded-pill px-3">
                    <i class="bi bi-pencil me-1"></i> Edit
                </a>
                <form action="{{ route('watchlist.destroy', $watchlist->id) }}" method="POST" onsubmit="return confirm('Hapus negara ini dari daftar pantauan?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger btn-sm rounded-pill px-3">
                        <i class="bi bi-trash me-1"></i> Hapus
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
