@extends('layouts.app')

@section('content')

<div class="container">

    <h2 class="mb-4 fw-bold">
        ➕ Tambah Data Economy
    </h2>

    <div class="card shadow">

        <div class="card-body">

            <form action="{{ route('economy.store') }}" method="POST">

                @csrf

                <div class="mb-3">
                    <label class="form-label">Negara</label>

                    <select name="country_id" class="form-control" required>

                        <option value="">-- Pilih Negara --</option>

                        @foreach($countries as $country)

                            <option value="{{ $country->id }}">
                                {{ $country->name }}
                            </option>

                        @endforeach

                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">GDP (Billion USD)</label>
                    <input type="number" step="0.01" name="gdp" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Inflation (%)</label>
                    <input type="number" step="0.01" name="inflation" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Unemployment (%)</label>
                    <input type="number" step="0.01" name="unemployment" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Exports (Billion USD)</label>
                    <input type="number" step="0.01" name="exports" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Imports (Billion USD)</label>
                    <input type="number" step="0.01" name="imports" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Year</label>
                    <input type="number" name="year" class="form-control" required>
                </div>

                <button type="submit" class="btn btn-success">
                    💾 Simpan
                </button>

                <a href="{{ route('economy.index') }}" class="btn btn-secondary">
                    Kembali
                </a>

            </form>

        </div>

    </div>

</div>

@endsection