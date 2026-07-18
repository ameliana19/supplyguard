@extends('layouts.app')

@section('content')

<div class="container">

    <h2 class="mb-4">➕ Tambah Port</h2>

    <div class="card shadow">

        <div class="card-body">

            <form action="{{ route('ports.store') }}" method="POST">

                @csrf

                <div class="mb-3">
                    <label>Country</label>

                    <select name="country_id" class="form-control">

                        @foreach($countries as $country)

                            <option value="{{ $country->id }}">
                                {{ $country->name }}
                            </option>

                        @endforeach

                    </select>

                </div>

                <div class="mb-3">
                    <label>Port Name</label>
                    <input type="text" name="port_name" class="form-control">
                </div>

                <div class="mb-3">
                    <label>Port Code</label>
                    <input type="text" name="port_code" class="form-control">
                </div>

                <div class="mb-3">
                    <label>City</label>
                    <input type="text" name="city" class="form-control">
                </div>

                <div class="mb-3">
                    <label>Type</label>

                    <select name="type" class="form-control">

                        <option>Sea Port</option>

                        <option>Air Port</option>

                    </select>

                </div>

                <div class="mb-3">
                    <label>Capacity</label>
                    <input type="number" name="capacity" class="form-control">
                </div>

                <div class="mb-3">
                    <label>Status</label>

                    <select name="status" class="form-control">

                        <option>Open</option>
                        <option>Busy</option>
                        <option>Closed</option>
                        <option>Maintenance</option>

                    </select>

                </div>

                <div class="mb-3">
                    <label>Latitude</label>
                    <input type="text" name="latitude" class="form-control">
                </div>

                <div class="mb-3">
                    <label>Longitude</label>
                    <input type="text" name="longitude" class="form-control">
                </div>

                <button class="btn btn-success">
                    Simpan
                </button>

                <a href="{{ route('ports.index') }}" class="btn btn-secondary">
                    Kembali
                </a>

            </form>

        </div>

    </div>

</div>

@endsection