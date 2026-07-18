@extends('layouts.app')

@section('content')

<div class="container">

    <h2 class="mb-4">✏️ Edit Risk Score</h2>

    <form action="{{ route('risk-score.update', $riskScore->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label>Country</label>
            <select name="country_id" class="form-control" required>
                @foreach($countries as $c)
                    <option value="{{ $c->id }}"
                        {{ $riskScore->country_id == $c->id ? 'selected' : '' }}>
                        {{ $c->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label>Weather Score</label>
            <input type="number" name="weather_score" class="form-control"
                   value="{{ $riskScore->weather_score }}" min="0" max="100" required>
        </div>

        <div class="mb-3">
            <label>Currency Score</label>
            <input type="number" name="currency_score" class="form-control"
                   value="{{ $riskScore->currency_score }}" min="0" max="100" required>
        </div>

        <div class="mb-3">
            <label>Economy Score</label>
            <input type="number" name="economy_score" class="form-control"
                   value="{{ $riskScore->economy_score }}" min="0" max="100" required>
        </div>

        <div class="mb-3">
            <label>Port Score</label>
            <input type="number" name="port_score" class="form-control"
                   value="{{ $riskScore->port_score }}" min="0" max="100" required>
        </div>

        <button class="btn btn-primary">
            Update
        </button>

    </form>

</div>

@endsection