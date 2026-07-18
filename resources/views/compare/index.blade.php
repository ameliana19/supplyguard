@extends('layouts.app')

@section('title', 'Perbandingan Negara - SupplyGuard')

@section('content')
<div class="container-fluid px-0">
    <!-- Header -->
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="fw-bold mb-1 text-dark" style="letter-spacing: -0.03em;">⚖️ Perbandingan Negara Parametrik</h1>
            <p class="text-muted mb-0">Evaluasi dan bandingkan profil risiko, metrik cuaca, dan data ekonomi antara dua negara secara berdampingan</p>
        </div>
    </div>

    <div class="row justify-content-center mt-5">
        <div class="col-md-6 col-lg-5">
            <div class="card-premium shadow-lg">
                <div class="card-premium-header bg-primary text-white py-3 justify-content-center">
                    <span class="fs-6 fw-bold"><i class="bi bi-arrow-left-right me-2"></i> Pilih Negara</span>
                </div>
                <div class="card-premium-body p-4">
                    @if(session('error'))
                        <div class="alert alert-danger border-0 rounded-4 mb-3">
                            <i class="bi bi-x-circle-fill me-2"></i> {{ session('error') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('compare.result') }}">
                        @csrf

                        <!-- COUNTRY 1 -->
                        <div class="mb-4">
                            <label class="form-label fw-semibold text-dark"><i class="bi bi-globe-americas text-primary me-2"></i> Negara Utama</label>
                            <select name="country1" class="form-select border-2 p-2.5 rounded-3" required>
                                <option value="">-- Pilih Negara --</option>
                                @foreach($countries as $c)
                                    <option value="{{ $c->id }}">{{ $c->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- COUNTRY 2 -->
                        <div class="mb-4">
                            <label class="form-label fw-semibold text-dark"><i class="bi bi-globe-asia-australia text-danger me-2"></i> Negara Pembanding</label>
                            <select name="country2" class="form-select border-2 p-2.5 rounded-3" required>
                                <option value="">-- Pilih Negara --</option>
                                @foreach($countries as $c)
                                    <option value="{{ $c->id }}">{{ $c->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- BUTTON -->
                        <div class="d-grid mt-4">
                            <button type="submit" class="btn btn-primary btn-lg p-2.5 hover-lift fw-bold shadow-sm">
                                ⚖️ Jalankan Analisis Perbandingan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection