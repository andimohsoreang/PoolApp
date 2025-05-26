@extends('layouts.app')

@section('title', 'Test Promo')

@section('content')
<div class="row">
    <div class="col-sm-12">
        <div class="page-title-box d-md-flex justify-content-md-between align-items-center">
            <h4 class="page-title">Test Promo</h4>
            <div class="">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="#">Master</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.promos.index') }}">Promo</a></li>
                    <li class="breadcrumb-item active">Test Promo</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">Cek Validitas & Perhitungan Promo</h4>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.promos.test') }}" method="GET" id="promoTestForm">
                    <div class="mb-3 row">
                        <label for="promo_code" class="col-sm-3 col-form-label text-end">Kode Promo</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" id="promo_code" name="promo_code"
                                value="{{ request('promo_code') }}" required placeholder="Masukkan kode promo">
                        </div>
                    </div>

                    <div class="mb-3 row">
                        <label for="amount" class="col-sm-3 col-form-label text-end">Jumlah (Rp)</label>
                        <div class="col-sm-9">
                            <input type="number" class="form-control" id="amount" name="amount"
                                value="{{ request('amount', 100000) }}" min="0" required>
                        </div>
                    </div>

                    <div class="mb-3 row">
                        <label for="table_id" class="col-sm-3 col-form-label text-end">Meja</label>
                        <div class="col-sm-9">
                            <select class="form-select" id="table_id" name="table_id">
                                <option value="">Pilih Meja (Opsional)</option>
                                @foreach($tables as $table)
                                    <option value="{{ $table->id }}" {{ request('table_id') == $table->id ? 'selected' : '' }}>
                                        {{ $table->table_number }} - {{ $table->room->name }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">Pilih jika promo berlaku untuk meja tertentu</small>
                        </div>
                    </div>

                    <div class="mb-3 row">
                        <label for="room_id" class="col-sm-3 col-form-label text-end">Ruangan</label>
                        <div class="col-sm-9">
                            <select class="form-select" id="room_id" name="room_id">
                                <option value="">Pilih Ruangan (Opsional)</option>
                                @foreach($rooms as $room)
                                    <option value="{{ $room->id }}" {{ request('room_id') == $room->id ? 'selected' : '' }}>
                                        {{ $room->name }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">Pilih jika promo berlaku untuk ruangan tertentu</small>
                        </div>
                    </div>

                    <div class="mb-3 row">
                        <div class="col-sm-9 offset-sm-3">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-check-circle me-1"></i> Cek Promo
                            </button>
                            <a href="{{ route('admin.promos.index') }}" class="btn btn-secondary ms-2">
                                <i class="fas fa-arrow-left me-1"></i> Kembali
                            </a>
                        </div>
                    </div>
                </form>

                @if($result)
                    <hr>
                    <div class="mt-4">
                        <h5 class="mb-3">Hasil Pengujian:</h5>

                        @if(isset($result['error']))
                            <div class="alert alert-danger">
                                <strong>Error:</strong> {{ $result['error'] }}
                            </div>
                        @elseif($result['promo'])
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <tbody>
                                        <tr>
                                            <th width="30%">Kode Promo</th>
                                            <td><span class="badge bg-dark">{{ $result['promo']->code }}</span></td>
                                        </tr>
                                        <tr>
                                            <th>Nama Promo</th>
                                            <td>{{ $result['promo']->name }}</td>
                                        </tr>
                                        <tr>
                                            <th>Status Aktivasi</th>
                                            <td>
                                                @if($result['promo']->status)
                                                    <span class="badge bg-success">Aktif</span>
                                                @else
                                                    <span class="badge bg-danger">Tidak Aktif</span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Status Validitas</th>
                                            <td>
                                                @if($result['valid'])
                                                    <span class="badge bg-success">Valid</span>
                                                @else
                                                    <span class="badge bg-danger">Tidak Valid</span>
                                                    <div class="mt-2">
                                                        <small class="text-muted">
                                                            Alasan:
                                                            @if(!$result['promo']->status)
                                                                Promo tidak aktif
                                                            @elseif($result['promo']->valid_from && now() < $result['promo']->valid_from)
                                                                Belum memasuki periode promo (mulai {{ $result['promo']->valid_from->format('d/m/Y H:i') }})
                                                            @elseif($result['promo']->valid_until && now() > $result['promo']->valid_until)
                                                                Promo sudah berakhir (berakhir {{ $result['promo']->valid_until->format('d/m/Y H:i') }})
                                                            @elseif($result['promo']->usage_limit && $result['promo']->usage_count >= $result['promo']->usage_limit)
                                                                Kuota promo sudah habis
                                                            @elseif($result['promo']->day_restriction)
                                                                Tidak berlaku untuk hari ini
                                                            @elseif($result['promo']->time_restriction_start && $result['promo']->time_restriction_end)
                                                                Tidak berlaku untuk waktu saat ini
                                                            @else
                                                                Alasan tidak diketahui
                                                            @endif
                                                        </small>
                                                    </div>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Dapat Diterapkan</th>
                                            <td>
                                                @if($result['applicable'])
                                                    <span class="badge bg-success">Ya</span>
                                                @else
                                                    <span class="badge bg-danger">Tidak</span>
                                                    <div class="mt-2">
                                                        <small class="text-muted">
                                                            Alasan: Promo ini hanya berlaku untuk
                                                            @if($result['promo']->applies_to == 'table')
                                                                meja {{ $result['promo']->table->table_number }} ({{ $result['promo']->table->room->name }})
                                                            @elseif($result['promo']->applies_to == 'room')
                                                                ruangan {{ $result['promo']->room->name }}
                                                            @endif
                                                        </small>
                                                    </div>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Perhitungan</th>
                                            <td>
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <p class="mb-1">Jumlah: <strong>Rp {{ number_format(request('amount'), 0, ',', '.') }}</strong></p>
                                                        <p class="mb-1">Diskon:
                                                            @if($result['discount'] > 0)
                                                                <strong class="text-success">Rp {{ number_format($result['discount'], 0, ',', '.') }}</strong>
                                                            @else
                                                                <strong class="text-danger">Rp 0</strong>
                                                            @endif
                                                        </p>
                                                        <hr>
                                                        <p class="mb-0">Total: <strong>Rp {{ number_format($result['final_amount'], 0, ',', '.') }}</strong></p>
                                                    </div>
                                                    <div class="col-md-6">
                                                        @if($result['promo']->minimum_price > 0 && request('amount') < $result['promo']->minimum_price)
                                                            <div class="alert alert-warning mb-0">
                                                                <small>Jumlah minimum tidak terpenuhi. Min: Rp {{ number_format($result['promo']->minimum_price, 0, ',', '.') }}</small>
                                                            </div>
                                                        @elseif($result['promo']->discount_type == 'percentage')
                                                            <div class="alert alert-info mb-0">
                                                                <small>
                                                                    Diskon {{ $result['promo']->discount_value }}% dari Rp {{ number_format(request('amount'), 0, ',', '.') }}
                                                                    @if($result['promo']->maximum_discount && $result['discount'] == $result['promo']->maximum_discount)
                                                                        <br>(Dibatasi maksimum Rp {{ number_format($result['promo']->maximum_discount, 0, ',', '.') }})
                                                                    @endif
                                                                </small>
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
