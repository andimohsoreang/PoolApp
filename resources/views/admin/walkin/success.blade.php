@extends('layouts.app')

@section('title', 'Transaksi Berhasil')

@push('links')
<style>
    .success-card {
        transition: all 0.3s ease;
        border-radius: 15px;
        border: none;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    }
    .success-icon {
        font-size: 80px;
        color: #10b981;
    }
    .timeline {
        position: relative;
        padding-left: 30px;
    }
    .timeline::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        height: 100%;
        width: 2px;
        background-color: #e5e7eb;
    }
    .timeline-item {
        position: relative;
        padding-bottom: 25px;
    }
    .timeline-item:last-child {
        padding-bottom: 0;
    }
    .timeline-item::before {
        content: '';
        position: absolute;
        left: -39px;
        top: 0;
        width: 20px;
        height: 20px;
        border-radius: 50%;
        background-color: #10b981;
        border: 4px solid #d1fae5;
    }
    .timeline-content {
        padding: 15px;
        border-radius: 10px;
        background-color: #f9fafb;
    }
    @media print {
        .no-print {
            display: none !important;
        }
        .print-only {
            display: block !important;
        }
        .card {
            border: none !important;
            box-shadow: none !important;
        }
    }
</style>
@endpush

@section('content')
    <div class="row no-print">
        <div class="col-sm-12">
            <div class="page-title-box d-md-flex justify-content-md-between align-items-center">
                <h4 class="page-title">Transaksi Berhasil</h4>
                <div class="">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.walkin.index') }}">Walk-in</a></li>
                        <li class="breadcrumb-item active">Transaksi Berhasil</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card success-card mb-4">
                <div class="card-body text-center p-5">
                    <div class="mb-4">
                        <i class="fas fa-check-circle success-icon"></i>
                    </div>
                    <h2 class="mb-3">Transaksi Berhasil!</h2>
                    <p class="text-muted mb-4">Transaksi walk-in telah berhasil dibuat dan dibayar. Customer dapat langsung menggunakan meja.</p>

                    <div class="alert alert-info mb-4">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <i class="fas fa-info-circle fa-2x me-3"></i>
                            </div>
                            <div class="flex-grow-1">
                                <strong>Kode Transaksi: {{ $transaction->transaction_code }}</strong>
                                <p class="mb-0">Gunakan kode ini untuk referensi transaksi.</p>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-center gap-3 no-print">
                        <a href="{{ route('admin.walkin.index') }}" class="btn btn-primary">
                            <i class="fas fa-home me-2"></i> Kembali ke Dashboard
                        </a>
                        <button type="button" class="btn btn-success" onclick="window.print()">
                            <i class="fas fa-print me-2"></i> Cetak Struk
                        </button>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header print-only" style="display: none;">
                    <h3 class="text-center mb-0">Struk Pembayaran</h3>
                    <p class="text-center mb-0">{{ config('app.name') }}</p>
                </div>
                <div class="card-header bg-light no-print">
                    <h5 class="card-title mb-0">Detail Transaksi</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6 class="text-muted fw-normal mb-3">Informasi Customer:</h6>
                            <h5 class="mb-2">{{ $transaction->customer->name }}</h5>
                            <p class="mb-1"><i class="fas fa-phone-alt me-2 text-muted"></i> {{ $transaction->customer->phone }}</p>
                            @if($transaction->customer->email)
                            <p class="mb-0"><i class="fas fa-envelope me-2 text-muted"></i> {{ $transaction->customer->email }}</p>
                            @endif
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted fw-normal mb-3">Informasi Meja:</h6>
                            <h5 class="mb-2">Meja #{{ $transaction->table->table_number }}</h5>
                            <p class="mb-1"><i class="fas fa-map-marker-alt me-2 text-muted"></i> {{ $transaction->table->room->name }}</p>
                            <p class="mb-0"><i class="fas fa-tag me-2 text-muted"></i> {{ $transaction->table->brand }}</p>
                        </div>
                    </div>

                    <div class="table-responsive mb-4">
                        <table class="table table-bordered table-striped">
                            <thead class="table-light">
                                <tr>
                                    <th>Deskripsi</th>
                                    <th class="text-end">Detail</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Kode Transaksi</td>
                                    <td class="text-end"><strong>{{ $transaction->transaction_code }}</strong></td>
                                </tr>
                                <tr>
                                    <td>Tanggal</td>
                                    <td class="text-end">{{ $transaction->created_at->format('d M Y H:i') }}</td>
                                </tr>
                                <tr>
                                    <td>Status</td>
                                    <td class="text-end">
                                        <span class="badge bg-success">{{ ucfirst($transaction->status) }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Waktu Mulai</td>
                                    <td class="text-end">{{ $transaction->start_time->format('d M Y H:i') }}</td>
                                </tr>
                                <tr>
                                    <td>Waktu Selesai</td>
                                    <td class="text-end">{{ $transaction->end_time->format('d M Y H:i') }}</td>
                                </tr>
                                <tr>
                                    <td>Durasi</td>
                                    <td class="text-end">{{ $transaction->details->first()->duration_hours }} jam</td>
                                </tr>
                                <tr>
                                    <td>Harga per Jam</td>
                                    <td class="text-end">Rp {{ number_format($transaction->details->first()->price_per_hour, 0, ',', '.') }}</td>
                                </tr>
                                @if($transaction->details->first()->discount > 0)
                                <tr>
                                    <td>Subtotal</td>
                                    <td class="text-end">Rp {{ number_format($transaction->details->first()->subtotal + $transaction->details->first()->discount, 0, ',', '.') }}</td>
                                </tr>
                                <tr>
                                    <td>Diskon</td>
                                    <td class="text-end text-danger">-Rp {{ number_format($transaction->details->first()->discount, 0, ',', '.') }}</td>
                                </tr>
                                @endif
                                <tr class="table-primary">
                                    <td><strong>Total</strong></td>
                                    <td class="text-end"><strong>Rp {{ number_format($transaction->total_price, 0, ',', '.') }}</strong></td>
                                </tr>
                                <tr>
                                    <td>Metode Pembayaran</td>
                                    <td class="text-end">
                                        {{ $transaction->payment_method == 'cash' ? 'Tunai' : 'E-Payment' }}
                                    </td>
                                </tr>
                                @if($transaction->payment && $transaction->payment->status == 'paid')
                                <tr>
                                    <td>Jumlah Dibayar</td>
                                    <td class="text-end">Rp {{ number_format($transaction->payment->amount_paid, 0, ',', '.') }}</td>
                                </tr>
                                @if($transaction->payment->change_amount > 0)
                                <tr>
                                    <td>Kembalian</td>
                                    <td class="text-end">Rp {{ number_format($transaction->payment->change_amount, 0, ',', '.') }}</td>
                                </tr>
                                @endif
                                @endif
                            </tbody>
                        </table>
                    </div>

                    @if($transaction->notes)
                    <div class="alert alert-info mb-4">
                        <h6 class="mb-1"><i class="fas fa-info-circle me-2"></i> Catatan:</h6>
                        <p class="mb-0">{{ $transaction->notes }}</p>
                    </div>
                    @endif

                    <div class="mt-5 mb-3 no-print">
                        <h5>Timeline Transaksi</h5>
                    </div>

                    <div class="timeline no-print">
                        <div class="timeline-item">
                            <div class="timeline-content">
                                <h6 class="mb-1">Transaksi Dibuat</h6>
                                <p class="text-muted mb-0">{{ $transaction->created_at->format('d M Y H:i') }}</p>
                            </div>
                        </div>

                        <div class="timeline-item">
                            <div class="timeline-content">
                                <h6 class="mb-1">Pembayaran Berhasil</h6>
                                @if($transaction->payment)
                                <p class="text-muted mb-1">{{ $transaction->payment->updated_at->format('d M Y H:i') }}</p>
                                <div class="badge bg-success">{{ $transaction->payment_method == 'cash' ? 'Tunai' : 'E-Payment' }}</div>
                                @else
                                <p class="text-muted mb-1">{{ $transaction->updated_at->format('d M Y H:i') }}</p>
                                <div class="badge bg-success">{{ $transaction->payment_method == 'cash' ? 'Tunai' : 'E-Payment' }}</div>
                                @endif
                            </div>
                        </div>

                        <div class="timeline-item">
                            <div class="timeline-content">
                                <h6 class="mb-1">Sesi Dimulai</h6>
                                <p class="text-muted mb-0">{{ $transaction->start_time->format('d M Y H:i') }}</p>
                            </div>
                        </div>

                        <div class="timeline-item">
                            <div class="timeline-content">
                                <h6 class="mb-1">Sesi Berakhir</h6>
                                <p class="text-muted mb-0">{{ $transaction->end_time->format('d M Y H:i') }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="text-center mt-5 mb-4 print-only" style="display: none;">
                        <p>Terima kasih telah menggunakan layanan kami!</p>
                        <p>Mohon simpan struk ini sebagai bukti pembayaran.</p>
                        <p>{{ config('app.name') }} - {{ date('Y') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
