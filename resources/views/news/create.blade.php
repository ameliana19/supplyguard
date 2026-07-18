@extends('layouts.app')

@section('content')
<div class="container-fluid">

    <div class="card shadow">
        <div class="card-header bg-success text-white">
            ➕ Tambah News
        </div>

        <div class="card-body">

            <form action="{{ route('news.store') }}" method="POST">
                @csrf

                <div class="mb-3">
                    <label class="form-label">Judul</label>
                    <input type="text" name="title" class="form-control" placeholder="Masukkan judul berita" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Kategori</label>
                    <input type="text" name="category" class="form-control" placeholder="Ekonomi / Weather / Ports" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Tanggal</label>
                    <input type="date" name="date" class="form-control" required>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-success">
                        💾 Simpan
                    </button>

                    <a href="{{ route('news.index') }}" class="btn btn-secondary">
                        ← Kembali
                    </a>
                </div>

            </form>

        </div>
    </div>

</div>
@endsection