@extends('layouts.app')

@section('title', 'Edit Daftar Pantauan - SupplyGuard')

@section('content')
<div class="container-fluid px-0">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="fw-bold mb-1 text-dark">✏️ Edit Daftar Pantauan</h1>
            <p class="text-muted mb-0">Perbarui negara dan catatan pemantauan</p>
        </div>
        <a href="{{ route('watchlist.index') }}" class="btn btn-outline-dark">
            <i class="bi bi-arrow-left me-1"></i> Kembali
        </a>
    </div>

    <div class="card-premium">
        <div class="card-premium-body p-4">
            <form action="{{ route('watchlist.update', $watchlist->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label class="form-label fw-semibold">Pilih Negara</label>
                    <select name="country_id" class="form-select" required>
                        @foreach($countries as $c)
                            <option value="{{ $c->id }}" {{ $watchlist->country_id == $c->id ? 'selected' : '' }}>
                                {{ $c->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Catatan Pemantauan</label>
                    <textarea name="note" class="form-control" rows="3" placeholder="Catatan opsional">{{ $watchlist->note }}</textarea>
                </div>

                <button type="submit" class="btn btn-primary px-4">
                    <i class="bi bi-check-lg me-1"></i> Simpan Perubahan
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
