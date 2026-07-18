@extends('layouts.app')

@section('content')

<div class="container">

    <div class="card shadow">

        <div class="card-header bg-warning">
            <h4>✏ Ubah Data Cuaca</h4>
        </div>

        <div class="card-body">

            <form action="{{ route('weather.update', $weather->id) }}" method="POST">

                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label class="form-label">Negara</label>

                    <select name="country_id" class="form-control" required>

                        @foreach($countries as $country)

                            <option value="{{ $country->id }}"
                                {{ $weather->country_id == $country->id ? 'selected' : '' }}>
                                {{ $country->name }}
                            </option>

                        @endforeach

                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Kota</label>
                    <input
                        type="text"
                        name="city"
                        class="form-control"
                        value="{{ $weather->city }}"
                        required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Suhu (°C)</label>
                    <input
                        type="number"
                        step="0.1"
                        name="temperature"
                        class="form-control"
                        value="{{ $weather->temperature }}"
                        required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Kelembaban (%)</label>
                    <input
                        type="number"
                        name="humidity"
                        class="form-control"
                        value="{{ $weather->humidity }}"
                        required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Kecepatan Angin (m/s)</label>
                    <input
                        type="number"
                        step="0.1"
                        name="wind_speed"
                        class="form-control"
                        value="{{ $weather->wind_speed }}"
                        required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Tekanan Udara (hPa)</label>
                    <input
                        type="number"
                        name="pressure"
                        class="form-control"
                        value="{{ $weather->pressure }}">
                </div>

                <div class="mb-3">
                    <label class="form-label">Kondisi Cuaca</label>

                    <select name="weather_condition" class="form-control">

                        <option value="Sunny" {{ $weather->weather_condition == 'Sunny' ? 'selected' : '' }}>☀️ Cerah</option>

                        <option value="Cloudy" {{ $weather->weather_condition == 'Cloudy' ? 'selected' : '' }}>☁️ Berawan</option>

                        <option value="Rain" {{ $weather->weather_condition == 'Rain' ? 'selected' : '' }}>🌧️ Hujan</option>

                        <option value="Storm" {{ $weather->weather_condition == 'Storm' ? 'selected' : '' }}>⛈️ Badai</option>

                        <option value="Snow" {{ $weather->weather_condition == 'Snow' ? 'selected' : '' }}>❄️ Salju</option>

                        <option value="Fog" {{ $weather->weather_condition == 'Fog' ? 'selected' : '' }}>🌫️ Kabut</option>

                    </select>

                </div>

                <button type="submit" class="btn btn-warning text-dark fw-bold">
                    💾 Simpan
                </button>

                <a href="{{ route('weather.index') }}" class="btn btn-secondary">
                    ← Kembali
                </a>

            </form>

        </div>

    </div>

</div>

@endsection