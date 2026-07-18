@extends('layouts.app')

@section('content')

<div class="container">

    <h2 class="mb-4">Ubah Negara</h2>

    <div class="card shadow">
        <div class="card-body">

            <form action="{{ route('countries.update', $country->id) }}" method="POST">

                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label>Nama Negara</label>
                    <input type="text" name="name" class="form-control" value="{{ $country->name }}" required>
                </div>

                <div class="mb-3">
                    <label>Ibukota</label>
                    <input type="text" name="capital" class="form-control" value="{{ $country->capital }}" required>
                </div>

                <div class="mb-3">
                    <label>Wilayah</label>
                    <input type="text" name="region" class="form-control" value="{{ $country->region }}" required>
                </div>

                <div class="mb-3">
                    <label>Mata Uang</label>
                    <input type="text" name="currency" class="form-control" value="{{ $country->currency }}" required>
                </div>

                <div class="mb-3">
                    <label>Jumlah Penduduk</label>
                    <input type="number" name="population" class="form-control" value="{{ $country->population }}" required>
                </div>

                <div class="mb-3">
                    <label>Latitude</label>
                    <input type="text" name="latitude" class="form-control" value="{{ $country->latitude }}">
                </div>

                <div class="mb-3">
                    <label>Longitude</label>
                    <input type="text" name="longitude" class="form-control" value="{{ $country->longitude }}">
                </div>

                <button type="submit" class="btn btn-primary">
                    Simpan
                </button>

                <a href="{{ route('countries.index') }}" class="btn btn-secondary">
                    Kembali
                </a>

            </form>

        </div>
    </div>

</div>

@endsection