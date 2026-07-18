@extends('layouts.app')

@section('content')
<div class="container">

    <h2>✏️ Ubah Mata Uang</h2>

    <div class="card p-3">

        <form action="{{ route('currency.update', $currency->id) }}" method="POST">

            @csrf
            @method('PUT')

            <div class="mb-3">
                <label>Nama Mata Uang</label>
                <input type="text" name="name" class="form-control"
                       value="{{ $currency->name }}" required>
            </div>

            <div class="mb-3">
                <label>Kode</label>
                <input type="text" name="code" class="form-control"
                       value="{{ $currency->code }}" required>
            </div>

            <div class="mb-3">
                <label>Simbol</label>
                <input type="text" name="symbol" class="form-control"
                       value="{{ $currency->symbol }}" required>
            </div>

            <div class="mb-3">
                <label>Nilai Tukar (per USD)</label>
                <input type="number" step="any" name="rate" class="form-control"
                       value="{{ $currency->rate }}" required>
            </div>

            <button type="submit" class="btn btn-success">
                Simpan
            </button>

            <a href="{{ route('currency.index') }}" class="btn btn-secondary">
                Kembali
            </a>

        </form>

    </div>

</div>
@endsection