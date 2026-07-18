@extends('layouts.app')

@section('content')

<div class="container">

    <h2 class="mb-4">
        ✏ Edit Port
    </h2>

    <div class="card shadow">

        <div class="card-body">

            <form action="{{ route('ports.update',$port->id) }}" method="POST">

                @csrf
                @method('PUT')

                <div class="mb-3">

                    <label>Country</label>

                    <select
                        name="country_id"
                        class="form-control">

                        @foreach($countries as $country)

                        <option
                            value="{{ $country->id }}"
                            {{ $country->id==$port->country_id?'selected':'' }}>

                            {{ $country->name }}

                        </option>

                        @endforeach

                    </select>

                </div>

                <div class="mb-3">

                    <label>Port Name</label>

                    <input
                        type="text"
                        name="port_name"
                        class="form-control"
                        value="{{ $port->port_name }}">

                </div>

                <div class="mb-3">

                    <label>Port Code</label>

                    <input
                        type="text"
                        name="port_code"
                        class="form-control"
                        value="{{ $port->port_code }}">

                </div>

                <div class="mb-3">

                    <label>City</label>

                    <input
                        type="text"
                        name="city"
                        class="form-control"
                        value="{{ $port->city }}">

                </div>

                <div class="mb-3">

                    <label>Type</label>

                    <input
                        type="text"
                        name="type"
                        class="form-control"
                        value="{{ $port->type }}">

                </div>

                <div class="mb-3">

                    <label>Capacity</label>

                    <input
                        type="number"
                        name="capacity"
                        class="form-control"
                        value="{{ $port->capacity }}">

                </div>

                <div class="mb-3">

                    <label>Status</label>

                    <select
                        name="status"
                        class="form-control">

                        <option {{ $port->status=='Open'?'selected':'' }}>Open</option>
                        <option {{ $port->status=='Busy'?'selected':'' }}>Busy</option>
                        <option {{ $port->status=='Maintenance'?'selected':'' }}>Maintenance</option>
                        <option {{ $port->status=='Closed'?'selected':'' }}>Closed</option>

                    </select>

                </div>

                <div class="row">

                    <div class="col-md-6">

                        <label>Latitude</label>

                        <input
                            type="text"
                            name="latitude"
                            class="form-control"
                            value="{{ $port->latitude }}">

                    </div>

                    <div class="col-md-6">

                        <label>Longitude</label>

                        <input
                            type="text"
                            name="longitude"
                            class="form-control"
                            value="{{ $port->longitude }}">

                    </div>

                </div>

                <br>

                <button class="btn btn-primary">

                    Update

                </button>

                <a href="{{ route('ports.index') }}"
                   class="btn btn-secondary">

                    Kembali

                </a>

            </form>

        </div>

    </div>

</div>

@endsection