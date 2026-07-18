@extends('layouts.app')

@section('content')
<div class="container">

    <h2>➕ Tambah Mata Uang</h2>

    <form action="{{ route('currency.store') }}" method="POST">
        @csrf

        <div class="mb-3">
            <label>Nama Mata Uang</label>
            <input type="text" name="name" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Kode</label>
            <input type="text" name="code" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Simbol</label>
            <input type="text" name="symbol" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Nilai Tukar (per USD)</label>
            <input type="number" step="any" name="rate" class="form-control" required>
        </div>

        <button type="submit" class="btn btn-success">
            Simpan
        </button>

        <a href="{{ route('currency.index') }}" class="btn btn-secondary">
            Kembali
        </a>

    </form>

</div>
@endsection