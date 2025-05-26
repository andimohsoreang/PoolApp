@extends('layouts.app')

@section('title', 'Edit Harga')

@section('content')
<div class="row">
    <div class="col-sm-12">
        <div class="page-title-box d-md-flex justify-content-md-between align-items-center">
            <h4 class="page-title">Edit Harga</h4>
            <div class="">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="#">Master</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.prices.index') }}">Harga</a></li>
                    <li class="breadcrumb-item active">Edit</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">Form Edit Harga</h4>
            </div>
            <div class="card-body">
                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        <strong>Error!</strong> Terdapat kesalahan pada data yang Anda masukkan.
                        <ul class="mb-0 mt-2">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('admin.prices.update', $price->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-3 row">
                        <label for="table_id" class="col-sm-3 col-form-label text-end">Meja</label>
                        <div class="col-sm-9">
                            <select class="form-select @error('table_id') is-invalid @enderror" id="table_id"
                                name="table_id" required>
                                <option value="">Pilih Meja</option>
                                @foreach($tables as $table)
                                    <option value="{{ $table->id }}" {{ old('table_id', $price->table_id) == $table->id ? 'selected' : '' }}>
                                        {{ $table->table_number }} - {{ $table->room->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('table_id')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3 row">
                        <label for="start_time" class="col-sm-3 col-form-label text-end">Waktu Mulai</label>
                        <div class="col-sm-9">
                            <input type="time" class="form-control @error('start_time') is-invalid @enderror"
                                id="start_time" name="start_time"
                                value="{{ old('start_time', \Carbon\Carbon::parse($price->start_time)->format('H:i')) }}" required>
                            @error('start_time')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3 row">
                        <label for="end_time" class="col-sm-3 col-form-label text-end">Waktu Selesai</label>
                        <div class="col-sm-9">
                            <input type="time" class="form-control @error('end_time') is-invalid @enderror"
                                id="end_time" name="end_time"
                                value="{{ old('end_time', \Carbon\Carbon::parse($price->end_time)->format('H:i')) }}" required>
                            @error('end_time')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3 row">
                        <label for="price" class="col-sm-3 col-form-label text-end">Harga (Rp)</label>
                        <div class="col-sm-9">
                            <input type="number" class="form-control @error('price') is-invalid @enderror"
                                id="price" name="price" value="{{ old('price', $price->price) }}" min="0" required>
                            @error('price')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3 row">
                        <label for="day_type" class="col-sm-3 col-form-label text-end">Tipe Hari</label>
                        <div class="col-sm-9">
                            <select class="form-select @error('day_type') is-invalid @enderror" id="day_type"
                                name="day_type" required>
                                <option value="weekday" {{ old('day_type', $price->day_type) == 'weekday' ? 'selected' : '' }}>Hari Kerja (Senin-Jumat)</option>
                                <option value="weekend" {{ old('day_type', $price->day_type) == 'weekend' ? 'selected' : '' }}>Akhir Pekan (Sabtu-Minggu)</option>
                            </select>
                            @error('day_type')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3 row">
                        <label for="valid_from" class="col-sm-3 col-form-label text-end">Berlaku Dari</label>
                        <div class="col-sm-9">
                            <input type="date" class="form-control @error('valid_from') is-invalid @enderror"
                                id="valid_from" name="valid_from"
                                value="{{ old('valid_from', $price->valid_from ? \Carbon\Carbon::parse($price->valid_from)->format('Y-m-d') : '') }}">
                            <small class="text-muted">Biarkan kosong jika berlaku selamanya</small>
                            @error('valid_from')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3 row">
                        <label for="valid_until" class="col-sm-3 col-form-label text-end">Berlaku Sampai</label>
                        <div class="col-sm-9">
                            <input type="date" class="form-control @error('valid_until') is-invalid @enderror"
                                id="valid_until" name="valid_until"
                                value="{{ old('valid_until', $price->valid_until ? \Carbon\Carbon::parse($price->valid_until)->format('Y-m-d') : '') }}">
                            <small class="text-muted">Biarkan kosong jika berlaku selamanya</small>
                            @error('valid_until')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3 row">
                        <label for="status" class="col-sm-3 col-form-label text-end">Status</label>
                        <div class="col-sm-9">
                            <div class="form-check form-switch form-switch-success">
                                <input class="form-check-input" type="checkbox" id="status" name="status"
                                    value="1" {{ old('status', $price->status) ? 'checked' : '' }}>
                                <label class="form-check-label" for="status">Aktif</label>
                            </div>
                            @error('status')
                                <div class="text-danger mt-1">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3 row">
                        <div class="col-sm-9 offset-sm-3">
                            <a href="{{ route('admin.prices.index') }}" class="btn btn-secondary me-2">
                                <i class="fas fa-arrow-left me-1"></i> Kembali
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> Simpan
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
