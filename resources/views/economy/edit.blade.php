@extends('layouts.app')

@section('content')

<div class="container">

    <h2 class="mb-4 fw-bold">
        ✏ Edit Data Economy
    </h2>

    <div class="card shadow">

        <div class="card-body">

            <form action="{{ route('economy.update', $economy->id) }}" method="POST">

                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label class="form-label">Negara</label>

                    <select name="country_id" class="form-control" required>

                        @foreach($countries as $country)

                            <option value="{{ $country->id }}"
                                {{ $economy->country_id == $country->id ? 'selected' : '' }}>

                                {{ $country->name }}

                            </option>

                        @endforeach

                    </select>

                </div>

                <div class="mb-3">
                    <label class="form-label">GDP (Billion USD)</label>
                    <input type="number"
                           step="0.01"
                           name="gdp"
                           value="{{ $economy->gdp }}"
                           class="form-control"
                           required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Inflation (%)</label>
                    <input type="number"
                           step="0.01"
                           name="inflation"
                           value="{{ $economy->inflation }}"
                           class="form-control"
                           required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Unemployment (%)</label>
                    <input type="number"
                           step="0.01"
                           name="unemployment"
                           value="{{ $economy->unemployment }}"
                           class="form-control"
                           required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Exports (Billion USD)</label>
                    <input type="number"
                           step="0.01"
                           name="exports"
                           value="{{ $economy->exports }}"
                           class="form-control"
                           required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Imports (Billion USD)</label>
                    <input type="number"
                           step="0.01"
                           name="imports"
                           value="{{ $economy->imports }}"
                           class="form-control"
                           required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Year</label>
                    <input type="number"
                           name="year"
                           value="{{ $economy->year }}"
                           class="form-control"
                           required>
                </div>

                <button type="submit" class="btn btn-primary">
                    💾 Update Data
                </button>

                <a href="{{ route('economy.index') }}" class="btn btn-secondary">
                    Kembali
                </a>

            </form>

        </div>

    </div>

</div>

@endsection