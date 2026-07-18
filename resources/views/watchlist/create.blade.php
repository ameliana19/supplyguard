@extends('layouts.app')

@section('content')
<div class="container">

    <h3>Tambah Watchlist</h3>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('watchlist.store') }}" method="POST">
        @csrf

        <div class="mb-3">
            <label class="form-label">Pilih Negara</label>

            <select name="country_id" class="form-control" required>
                <option value="">-- Pilih Negara --</option>

                @forelse($countries as $c)
                    <option value="{{ $c->id }}">
                        {{ $c->name }}
                    </option>
                @empty
                    <option value="">Data negara tidak tersedia</option>
                @endforelse

            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Catatan</label>

            <textarea name="note"
                class="form-control"
                rows="3"
                placeholder="Masukkan catatan (opsional)"></textarea>
        </div>

        <button type="submit" class="btn btn-primary">
            Simpan
        </button>

        <a href="{{ route('watchlist.index') }}" class="btn btn-secondary">
            Kembali
        </a>

    </form>

</div>
@endsection