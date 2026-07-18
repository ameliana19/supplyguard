@extends('layouts.app')

@section('content')

<div class="container">

    <h2 class="mb-4">➕ Tambah Risk Score</h2>

    <form action="{{ route('risk-score.store') }}" method="POST">
        @csrf

        <div class="mb-3">
            <label>Country</label>
            <select name="country_id" class="form-control" required>
                <option value="">-- Pilih Country --</option>
                @foreach($countries as $c)
                    <option value="{{ $c->id }}" {{ old('country_id') == $c->id ? 'selected' : '' }}>
                        {{ $c->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label>Weather Score (0 - 100)</label>
            <input type="number" name="weather_score" class="form-control"
                   min="0" max="100" value="{{ old('weather_score') }}" required>
        </div>

        <div class="mb-3">
            <label>Currency Score (0 - 100)</label>
            <input type="number" name="currency_score" class="form-control"
                   min="0" max="100" value="{{ old('currency_score') }}" required>
        </div>

        <div class="mb-3">
            <label>Economy Score (0 - 100)</label>
            <input type="number" name="economy_score" class="form-control"
                   min="0" max="100" value="{{ old('economy_score') }}" required>
        </div>

        <div class="mb-3">
            <label>Port Score (0 - 100)</label>
            <input type="number" name="port_score" class="form-control"
                   min="0" max="100" value="{{ old('port_score') }}" required>
        </div>

        <button class="btn btn-primary">
            Save
        </button>

    </form>

</div>

@endsection