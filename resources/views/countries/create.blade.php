@extends('layouts.app')

@section('content')

<div class="container">

    <h2 class="mb-4">Tambah Negara</h2>

    <div class="card shadow">
        <div class="card-body">

            <form action="{{ route('countries.store') }}" method="POST">

                @csrf

                <div class="mb-3">
                    <label class="form-label">Nama Negara</label>
                    <input type="text" name="name" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Ibukota</label>
                    <input type="text" name="capital" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Wilayah</label>
                    <input type="text" name="region" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Mata Uang</label>
                    <input type="text" name="currency" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Jumlah Penduduk</label>
                    <input type="number" name="population" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Latitude</label>
                    <input type="text" name="latitude" class="form-control">
                </div>

                <div class="mb-3">
                    <label class="form-label">Longitude</label>
                    <input type="text" name="longitude" class="form-control">
                </div>

                <button type="submit" class="btn btn-success">
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