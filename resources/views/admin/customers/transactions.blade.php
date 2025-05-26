@extends('layouts.app')

@section('title', 'Riwayat Transaksi Customer')

@section('content')
<div class="container-fluid">
    <!-- Page-Title -->
    <div class="row">
        <div class="col-sm-12">
            <div class="page-title-box">
                <div class="float-end">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="/admin/dashboard">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.customers.index') }}">Daftar Customer</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.customers.show', $customer->id) }}">Detail Customer</a></li>
                        <li class="breadcrumb-item active">Riwayat Transaksi</li>
                    </ol>
                </div>
                <h4 class="page-title">Riwayat Transaksi Customer</h4>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="row align-items-center">
                        <div class="col">
                            <h4 class="card-title">Riwayat Transaksi - {{ $customer->name }}</h4>
                        </div>
                        <div class="col-auto">
                            <a href="{{ route('admin.customers.show', $customer->id) }}" class="btn btn-sm btn-outline-secondary">
                                <i class="iconoir-arrow-left me-1"></i>Kembali ke Detail
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Customer Info Summary -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="d-flex">
                                <div class="avatar-md me-3">
                                    <div class="avatar-title bg-light rounded-circle text-primary">
                                        <i class="iconoir-user-circle text-primary font-size-24"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <h5 class="mb-1">{{ $customer->name }}</h5>
                                    <p class="text-muted mb-0">
                                        <i class="iconoir-mail me-1 text-muted"></i>{{ $customer->email }}
                                    </p>
                                    <p class="text-muted mb-0">
                                        <i class="iconoir-phone me-1 text-muted"></i>{{ $customer->phone }}
                                    </p>
                                    <span class="badge {{ $customer->category == 'member' ? 'bg-success' : 'bg-info' }} mt-1">
                                        {{ $customer->category == 'member' ? 'Member' : 'Non-Member' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Transactions Table -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Tanggal</th>
                                    <th>Ruangan</th>
                                    <th>Meja</th>
                                    <th>Durasi</th>
                                    <th>Total</th>
                                    <th>Metode Pembayaran</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($transactions as $transaction)
                                    <tr>
                                        <td>#{{ $transaction->id }}</td>
                                        <td>{{ $transaction->created_at->format('d M Y H:i') }}</td>
                                        <td>{{ $transaction->table->room->name }}</td>
                                        <td>{{ $transaction->table->name }}</td>
                                        <td>
                                            {{ \Carbon\Carbon::parse($transaction->start_time)->format('H:i') }} -
                                            {{ \Carbon\Carbon::parse($transaction->end_time)->format('H:i') }}
                                            <small class="d-block text-muted">
                                                ({{ \Carbon\Carbon::parse($transaction->start_time)->diffInHours($transaction->end_time) }} jam)
                                            </small>
                                        </td>
                                        <td>Rp {{ number_format($transaction->total_price, 0, ',', '.') }}</td>
                                        <td>
                                            @if($transaction->payment)
                                                {{ ucfirst($transaction->payment->payment_method) }}
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge
                                                @if(in_array($transaction->status, ['completed', 'paid'])) bg-success
                                                @elseif(in_array($transaction->status, ['cancelled'])) bg-danger
                                                @elseif(in_array($transaction->status, ['pending', 'approved'])) bg-warning
                                                @else bg-info @endif
                                            ">
                                                {{ ucfirst($transaction->status) }}
                                            </span>
                                        </td>
                                        <td>
                                            <a href="/admin/transactions/{{ $transaction->id }}" class="btn btn-sm btn-info" title="Detail">
                                                <i class="iconoir-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center">Belum ada transaksi</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-center mt-3">
                        {{ $transactions->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
