@extends('layouts.app')

@section('title', 'Edit Reservasi')

@section('content')
<div class="row">
    <div class="col-12 col-lg-8 mx-auto">
        <div class="card">
            <div class="card-header bg-warning text-dark">
                <h5 class="mb-0">Edit Reservasi #{{ $reservation->id }}</h5>
            </div>
            <div class="card-body">
                @if(session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif
                <form method="POST" action="{{ route('admin.reservations.update', $reservation->id) }}">
                    @csrf
                    <div class="mb-3">
                        <label for="start_time" class="form-label">Waktu Mulai</label>
                        <input type="text" class="form-control" name="start_time" id="start_time" value="{{ $reservation->start_time->format('Y-m-d H:i') }}" required>
                    </div>
                    <div class="mb-3">
                        <label for="end_time" class="form-label">Waktu Selesai</label>
                        <input type="text" class="form-control" name="end_time" id="end_time" value="{{ $reservation->end_time->format('Y-m-d H:i') }}" required>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                        <a href="{{ route('admin.reservations.show', $reservation->id) }}" class="btn btn-secondary">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
