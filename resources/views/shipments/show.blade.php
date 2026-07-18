@extends('layouts.app')

@section('title', 'Detail Pengiriman - SupplyGuard')

@section('content')
<div class="container-fluid px-0">
    <!-- Header -->
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="fw-bold mb-1 text-dark" style="letter-spacing: -0.03em;">📦 Detail Pengiriman</h1>
            <p class="text-muted mb-0">Informasi lengkap status kargo dan riwayat logistik pengiriman</p>
        </div>
        <a href="{{ route('shipments.history') }}" class="btn btn-outline-dark d-flex align-items-center gap-2 hover-lift px-3 shadow-sm">
            <i class="bi bi-arrow-left"></i> Kembali ke Riwayat
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible border-0 fade show rounded-4 mb-4 shadow-sm" role="alert">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-check-circle-fill fs-5"></i>
                <div>{{ session('success') }}</div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row g-4">
        <!-- Left: Shipment Info -->
        <div class="col-lg-8">
            <!-- Card Utama Detail -->
            <div class="card-premium mb-4">
                <div class="card-premium-header bg-light">
                    <span class="fs-6 fw-bold text-dark">
                        <i class="bi bi-info-circle text-primary me-2"></i> Ringkasan Kargo
                    </span>
                    @php
                        $badgeClass = 'bg-secondary';
                        $statusText = $shipment->status;
                        if ($shipment->status === 'In Transit') {
                            $badgeClass = 'bg-primary';
                            $statusText = 'Dalam Perjalanan';
                        } elseif ($shipment->status === 'Delayed') {
                            $badgeClass = 'bg-warning text-dark';
                            $statusText = 'Tertunda';
                        } elseif ($shipment->status === 'Arrived') {
                            $badgeClass = 'bg-success';
                            $statusText = 'Terkirim';
                        } elseif ($shipment->status === 'Cancelled') {
                            $badgeClass = 'bg-danger';
                            $statusText = 'Dibatalkan';
                        } elseif ($shipment->status === 'Pending') {
                            $badgeClass = 'bg-secondary';
                            $statusText = 'Perencanaan';
                        }
                    @endphp
                    <span class="badge {{ $badgeClass }} rounded-pill px-3 py-2 fs-7">{{ $statusText }}</span>
                </div>
                <div class="card-premium-body">
                    <div class="row g-4">
                        <div class="col-md-6 col-lg-4">
                            <small class="text-muted d-block text-uppercase fw-semibold mb-1">Nomor Pelacakan</small>
                            <span class="fs-5 fw-bold text-dark font-monospace">{{ $shipment->tracking_number }}</span>
                        </div>
                        <div class="col-md-6 col-lg-4">
                            <small class="text-muted d-block text-uppercase fw-semibold mb-1">Nomor Kontainer</small>
                            <span class="fs-5 fw-bold text-dark font-monospace">{{ $shipment->container_number }}</span>
                        </div>
                        <div class="col-md-6 col-lg-4">
                            <small class="text-muted d-block text-uppercase fw-semibold mb-1">Jenis Kargo</small>
                            <span class="fs-5 fw-bold text-dark">{{ $shipment->cargo_type }}</span>
                        </div>
                        
                        <div class="col-md-6">
                            <small class="text-muted d-block text-uppercase fw-semibold mb-1">Keberangkatan (Estimasi)</small>
                            <span class="fw-semibold text-dark">
                                <i class="bi bi-calendar-event me-2 text-primary"></i>
                                {{ \Carbon\Carbon::parse($shipment->estimated_departure)->locale('id')->translatedFormat('d F Y H:i') }}
                            </span>
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted d-block text-uppercase fw-semibold mb-1">Kedatangan (Estimasi)</small>
                            <span class="fw-semibold text-dark">
                                <i class="bi bi-calendar-check me-2 text-success"></i>
                                {{ \Carbon\Carbon::parse($shipment->estimated_arrival)->locale('id')->translatedFormat('d F Y H:i') }}
                            </span>
                        </div>
                    </div>

                    <hr class="my-4 text-muted">

                    <!-- Rute Pelabuhan -->
                    <div class="row g-4 align-items-center">
                        <div class="col-md-5">
                            <div class="p-3 bg-light rounded-4 border">
                                <small class="text-primary fw-bold text-uppercase d-block mb-1"><i class="bi bi-geo-alt"></i> Asal</small>
                                <h5 class="fw-bold mb-1 text-dark">{{ $shipment->originPort ? $shipment->originPort->port_name : 'Depot Asal' }}</h5>
                                <p class="mb-0 text-muted small">{{ $shipment->originCountry ? $shipment->originCountry->name : '-' }}</p>
                            </div>
                        </div>
                        <div class="col-md-2 text-center my-3 my-md-0">
                            <div class="d-none d-md-block">
                                <i class="bi bi-arrow-right fs-2 text-muted"></i>
                            </div>
                            <div class="d-md-none">
                                <i class="bi bi-arrow-down fs-2 text-muted"></i>
                            </div>
                        </div>
                        <div class="col-md-5">
                            <div class="p-3 bg-light rounded-4 border">
                                <small class="text-success fw-bold text-uppercase d-block mb-1"><i class="bi bi-geo-alt-fill"></i> Tujuan</small>
                                <h5 class="fw-bold mb-1 text-dark">{{ $shipment->destinationPort ? $shipment->destinationPort->port_name : 'Depot Tujuan' }}</h5>
                                <p class="mb-0 text-muted small">{{ $shipment->destinationCountry ? $shipment->destinationCountry->name : '-' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card Timeline Pelacakan -->
            <div class="card-premium">
                <div class="card-premium-header bg-light">
                    <span class="fs-6 fw-bold text-dark"><i class="bi bi-clock-history text-primary me-2"></i> Log Perjalanan</span>
                </div>
                <div class="card-premium-body">
                    @if($shipment->histories->count() > 0)
                        <div class="timeline">
                            @foreach($shipment->histories as $history)
                                @php
                                    $itemClass = '';
                                    if ($history->status === 'Delayed') $itemClass = 'delayed';
                                    elseif ($history->status === 'Arrived') $itemClass = 'arrived';
                                @endphp
                                <div class="timeline-item {{ $itemClass }}">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <h6 class="fw-bold mb-0 text-dark">
                                            @if($history->status === 'In Transit')
                                                Dalam Perjalanan
                                            @elseif($history->status === 'Delayed')
                                                Tertunda (Delayed)
                                            @elseif($history->status === 'Arrived')
                                                Terkirim (Arrived)
                                            @elseif($history->status === 'Cancelled')
                                                Dibatalkan
                                            @else
                                                Perencanaan
                                            @endif
                                        </h6>
                                        <small class="text-muted font-monospace">
                                            {{ \Carbon\Carbon::parse($history->event_time)->locale('id')->translatedFormat('d M Y H:i') }}
                                        </small>
                                    </div>
                                    <div class="text-muted small mb-2">
                                        <i class="bi bi-geo-alt me-1 text-danger"></i> {{ $history->location ?? '-' }}
                                    </div>
                                    @if($history->notes)
                                        <p class="small text-secondary mb-0 bg-light p-2 rounded-3 border-start border-3 border-primary">
                                            {{ $history->notes }}
                                        </p>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted text-center py-4 mb-0">Belum ada riwayat pelacakan pengiriman.</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Right: Admin Update Panel -->
        <div class="col-lg-4">
            <div class="card-premium sticky-top" style="top: 2rem; z-index: 1;">
                <div class="card-premium-header bg-dark text-white">
                    <span class="fs-6 fw-bold"><i class="bi bi-pencil-square me-2"></i> Perbarui Status</span>
                </div>
                <div class="card-premium-body">
                    <form action="{{ route('shipments.updateStatus', $shipment->id) }}" method="POST">
                        @csrf
                        
                        <!-- Status Dropdown -->
                        <div class="mb-3">
                            <label for="status" class="form-label fw-semibold text-dark">Status Baru</label>
                            <select name="status" id="status" class="form-select border-2" required>
                                <option value="Pending" {{ $shipment->status === 'Pending' ? 'selected' : '' }}>Perencanaan (Pending)</option>
                                <option value="In Transit" {{ $shipment->status === 'In Transit' ? 'selected' : '' }}>Dalam Perjalanan (In Transit)</option>
                                <option value="Arrived" {{ $shipment->status === 'Arrived' ? 'selected' : '' }}>Terkirim (Arrived)</option>
                                <option value="Delayed" {{ $shipment->status === 'Delayed' ? 'selected' : '' }}>Tertunda (Delayed)</option>
                                <option value="Cancelled" {{ $shipment->status === 'Cancelled' ? 'selected' : '' }}>Dibatalkan (Cancelled)</option>
                            </select>
                        </div>

                        <!-- Location Input -->
                        <div class="mb-3">
                            <label for="location" class="form-label fw-semibold text-dark">Lokasi Saat Ini</label>
                            <input type="text" name="location" id="location" class="form-control border-2" placeholder="Contoh: Selat Malaka, Port Klang" required>
                        </div>

                        <!-- Notes Textarea -->
                        <div class="mb-3">
                            <label for="notes" class="form-label fw-semibold text-dark">Catatan Perjalanan</label>
                            <textarea name="notes" id="notes" class="form-control border-2" rows="3" placeholder="Informasi tambahan mengenai kondisi pengiriman kargo..."></textarea>
                        </div>

                        <div class="d-grid mt-4">
                            <button type="submit" class="btn btn-primary btn-lg p-2.5 hover-lift fw-bold shadow-sm">
                                <i class="bi bi-save me-1"></i> Simpan Pembaruan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    /* Premium vertical timeline styling */
    .timeline {
        position: relative;
        padding-left: 32px;
        margin-top: 10px;
    }
    .timeline::before {
        content: '';
        position: absolute;
        left: 11px;
        top: 6px;
        bottom: 6px;
        width: 3px;
        background: #e2e8f0;
        border-radius: 4px;
    }
    .timeline-item {
        position: relative;
        margin-bottom: 2rem;
    }
    .timeline-item:last-child {
        margin-bottom: 0.5rem;
    }
    .timeline-item::before {
        content: '';
        position: absolute;
        left: -27px;
        top: 4px;
        width: 14px;
        height: 14px;
        border-radius: 50%;
        background: #D4AF37;
        border: 3px solid #fff;
        box-shadow: 0 0 0 2px #D4AF37;
        z-index: 2;
    }
    .timeline-item.delayed::before {
        background: #dc3545;
        box-shadow: 0 0 0 2px #dc3545;
    }
    .timeline-item.arrived::before {
        background: #198754;
        box-shadow: 0 0 0 2px #198754;
    }
</style>
@endpush
