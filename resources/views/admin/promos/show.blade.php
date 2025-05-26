@extends('layouts.app')

@section('title', 'Detail Promo')

@section('content')
<div class="row">
    <div class="col-sm-12">
        <div class="page-title-box d-md-flex justify-content-md-between align-items-center">
            <h4 class="page-title">Detail Promo</h4>
            <div class="">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="#">Master</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.promos.index') }}">Promo</a></li>
                    <li class="breadcrumb-item active">Detail Promo</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Flash Message -->
    <div class="col-12">
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                <strong>Berhasil!</strong> {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                <strong>Error!</strong> {{ session('error') }}
            </div>
        @endif
    </div>

    <div class="col-lg-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="card-title mb-0">Informasi Promo</h4>
                <div>
                    <a href="{{ route('admin.promos.edit', $promo->id) }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-edit me-1"></i> Edit
                    </a>
                    <a href="{{ route('admin.promos.index') }}" class="btn btn-secondary btn-sm ms-1">
                        <i class="fas fa-arrow-left me-1"></i> Kembali
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <th width="35%">Kode Promo</th>
                                <td><span class="badge bg-dark">{{ $promo->code }}</span></td>
                            </tr>
                            <tr>
                                <th>Nama Promo</th>
                                <td>{{ $promo->name }}</td>
                            </tr>
                            <tr>
                                <th>Deskripsi</th>
                                <td>{{ $promo->description ?? 'Tidak ada deskripsi' }}</td>
                            </tr>
                            <tr>
                                <th>Diskon</th>
                                <td>
                                    @if($promo->discount_type == 'percentage')
                                        <span class="badge bg-primary">{{ $promo->discount_value }}%</span>
                                    @else
                                        <span class="badge bg-info">Rp {{ number_format($promo->discount_value, 0, ',', '.') }}</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Minimum Transaksi</th>
                                <td>
                                    @if($promo->minimum_price > 0)
                                        Rp {{ number_format($promo->minimum_price, 0, ',', '.') }}
                                    @else
                                        <span class="text-muted">Tidak ada minimum</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Maksimum Diskon</th>
                                <td>
                                    @if($promo->maximum_discount)
                                        Rp {{ number_format($promo->maximum_discount, 0, ',', '.') }}
                                    @else
                                        <span class="text-muted">Tidak ada maksimum</span>
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <th width="35%">Periode Berlaku</th>
                                <td>
                                    @if($promo->valid_from && $promo->valid_until)
                                        {{ \Carbon\Carbon::parse($promo->valid_from)->format('d/m/Y H:i') }} - {{ \Carbon\Carbon::parse($promo->valid_until)->format('d/m/Y H:i') }}
                                    @elseif($promo->valid_from)
                                        Dari {{ \Carbon\Carbon::parse($promo->valid_from)->format('d/m/Y H:i') }}
                                    @elseif($promo->valid_until)
                                        Sampai {{ \Carbon\Carbon::parse($promo->valid_until)->format('d/m/Y H:i') }}
                                    @else
                                        <span class="text-muted">Tidak ada batas waktu</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Kuota Penggunaan</th>
                                <td>
                                    @if($promo->usage_limit)
                                        <div class="d-flex align-items-center">
                                            <div class="progress flex-grow-1 me-2" style="height: 8px;">
                                                <div class="progress-bar bg-primary" role="progressbar"
                                                    style="width: {{ ($promo->usage_count / $promo->usage_limit) * 100 }}%;"
                                                    aria-valuenow="{{ $promo->usage_count }}" aria-valuemin="0" aria-valuemax="{{ $promo->usage_limit }}">
                                                </div>
                                            </div>
                                            <div>{{ $promo->usage_count }}/{{ $promo->usage_limit }}</div>
                                        </div>
                                    @else
                                        <span class="text-muted">Tidak ada batas penggunaan</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Berlaku Untuk</th>
                                <td>
                                    @if($promo->applies_to == 'all')
                                        <span class="badge bg-success">Semua Meja</span>
                                    @elseif($promo->applies_to == 'table' && $promo->table)
                                        <span class="badge bg-primary">Meja {{ $promo->table->table_number }}</span>
                                        @if($promo->table->room)
                                            <small class="d-block">{{ $promo->table->room->name }}</small>
                                        @endif
                                    @elseif($promo->applies_to == 'room' && $promo->room)
                                        <span class="badge bg-info">{{ $promo->room->name }}</span>
                                    @else
                                        <span class="badge bg-secondary">Tidak tersedia</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Pembatasan Hari</th>
                                <td>
                                    @if($promo->day_restriction)
                                        {{ str_replace(['monday','tuesday','wednesday','thursday','friday','saturday','sunday'],
                                            ['Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu'], $promo->day_restriction) }}
                                    @else
                                        <span class="text-muted">Semua hari</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Pembatasan Waktu</th>
                                <td>
                                    @if($promo->time_restriction_start && $promo->time_restriction_end)
                                        {{ \Carbon\Carbon::parse($promo->time_restriction_start)->format('H:i') }} -
                                        {{ \Carbon\Carbon::parse($promo->time_restriction_end)->format('H:i') }}
                                    @else
                                        <span class="text-muted">Semua waktu</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Status</th>
                                <td>
                                    @if($promo->status)
                                        @if($promo->isValid())
                                            <span class="badge bg-success">Aktif</span>
                                        @else
                                            <span class="badge bg-warning">Tidak Berlaku</span>
                                            <small class="d-block text-muted mt-1">
                                                @if(!$promo->status)
                                                    Promo tidak aktif
                                                @elseif($promo->valid_from && now() < $promo->valid_from)
                                                    Belum memasuki periode promo (mulai {{ $promo->valid_from->format('d/m/Y H:i') }})
                                                @elseif($promo->valid_until && now() > $promo->valid_until)
                                                    Promo sudah berakhir (berakhir {{ $promo->valid_until->format('d/m/Y H:i') }})
                                                @elseif($promo->usage_limit && $promo->usage_count >= $promo->usage_limit)
                                                    Kuota promo sudah habis
                                                @endif
                                            </small>
                                        @endif
                                    @else
                                        <span class="badge bg-danger">Tidak Aktif</span>
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if($promo->transactionDetails && $promo->transactionDetails->count() > 0)
    <div class="col-lg-12 mt-4">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">Riwayat Penggunaan</h4>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table" id="datatable_1">
                        <thead class="table-light">
                            <tr>
                                <th>No</th>
                                <th>Tanggal</th>
                                <th>No. Transaksi</th>
                                <th>Customer</th>
                                <th>Meja</th>
                                <th>Total</th>
                                <th>Diskon</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($promo->transactionDetails as $key => $detail)
                            <tr>
                                <td>{{ $key + 1 }}</td>
                                <td>{{ $detail->created_at->format('d/m/Y H:i') }}</td>
                                <td>{{ $detail->transaction->transaction_number ?? '-' }}</td>
                                <td>{{ $detail->transaction->customer->name ?? 'Customer Umum' }}</td>
                                <td>{{ $detail->table->table_number ?? '-' }}</td>
                                <td>Rp {{ number_format($detail->amount, 0, ',', '.') }}</td>
                                <td>Rp {{ number_format($detail->discount, 0, ',', '.') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script src="{{ asset('dist/assets/libs/simple-datatables/umd/simple-datatables.js') }}"></script>
<script src="{{ asset('dist/assets/js/pages/datatable.init.js') }}"></script>
@endpush
