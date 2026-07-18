@extends('layouts.app')

@section('content')
<div class="container-fluid">

    <div class="card shadow">

        <div class="card-header bg-primary text-white">
            ✏ Edit News
        </div>

        <div class="card-body">

            <form method="POST" action="{{ route('news.update', $id) }}">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label class="form-label">Judul</label>
                    <input type="text"
                           name="title"
                           class="form-control"
                           value="{{ $item['title'] ?? '' }}"
                           required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Kategori</label>
                    <input type="text"
                           name="category"
                           class="form-control"
                           value="{{ $item['category'] ?? '' }}"
                           required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Tanggal</label>
                    <input type="date"
                           name="date"
                           class="form-control"
                           value="{{ $item['date'] ?? '' }}"
                           required>
                </div>

                <div class="d-flex gap-2">
                    <button class="btn btn-primary">
                        💾 Update
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