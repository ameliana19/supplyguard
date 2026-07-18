@extends('layouts.app')

@section('content')

<div class="container">

    <div class="card shadow">

        <div class="card-header bg-primary text-white">
            <h4>➕ Tambah Data Cuaca</h4>
        </div>

        <div class="card-body">

            <form action="{{ route('weather.store') }}" method="POST">

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
                    <label class="form-label">Kota</label>
                    <input type="text" name="city" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Suhu (°C)</label>
                    <input type="number" step="0.1" name="temperature" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Kelembaban (%)</label>
                    <input type="number" name="humidity" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Kecepatan Angin (m/s)</label>
                    <input type="number" step="0.1" name="wind_speed" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Tekanan Udara (hPa)</label>
                    <input type="number" name="pressure" class="form-control">
                </div>

                <div class="mb-3">
                    <label class="form-label">Kondisi Cuaca</label>

                    <select name="weather_condition" class="form-control">

                        <option value="Sunny">☀️ Cerah</option>
                        <option value="Cloudy">☁️ Berawan</option>
                        <option value="Rain">🌧️ Hujan</option>
                        <option value="Storm">⛈️ Badai</option>
                        <option value="Snow">❄️ Salju</option>
                        <option value="Fog">🌫️ Kabut</option>

                    </select>

                </div>

                <div class="mt-4">

                    <button type="submit" class="btn btn-success">
                        💾 Simpan
                    </button>

                    <a href="{{ route('weather.index') }}" class="btn btn-secondary">
                        ← Kembali
                    </a>

                </div>

            </form>

        </div>

    </div>

</div>

@endsection