@extends('layouts.app')

@section('title', 'Detail Customer')

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
                        <li class="breadcrumb-item active">Detail Customer</li>
                    </ol>
                </div>
                <h4 class="page-title">Detail Customer</h4>
            </div>
        </div>
    </div>

    <!-- Alert Messages -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Main Content -->
    <div class="row">
        <!-- Customer Info Card -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <div class="row align-items-center">
                        <div class="col">
                            <h4 class="card-title">Informasi Customer</h4>
                        </div>
                        <div class="col-auto">
                            <div class="dropdown">
                                <a href="#" class="btn btn-sm btn-outline-light dropdown-toggle" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="iconoir-settings"></i>
                                </a>
                                <div class="dropdown-menu dropdown-menu-end">
                                    <a class="dropdown-item" href="{{ route('admin.customers.edit', $customer->id) }}">
                                        <i class="iconoir-edit-pencil me-2"></i>Edit Customer
                                    </a>
                                    <a class="dropdown-item" href="{{ route('admin.customers.transactions', $customer->id) }}">
                                        <i class="iconoir-receipt me-2"></i>Riwayat Transaksi
                                    </a>
                                    @if($customer->status)
                                        <a class="dropdown-item text-danger" href="javascript:void(0);" onclick="event.preventDefault(); document.getElementById('toggleStatus-{{ $customer->id }}').submit();">
                                            <i class="iconoir-lock me-2"></i>Nonaktifkan Customer
                                        </a>
                                    @else
                                        <a class="dropdown-item text-success" href="javascript:void(0);" onclick="event.preventDefault(); document.getElementById('toggleStatus-{{ $customer->id }}').submit();">
                                            <i class="iconoir-unlock me-2"></i>Aktifkan Customer
                                        </a>
                                    @endif
                                    <form id="toggleStatus-{{ $customer->id }}" action="{{ route('admin.customers.toggle-status', $customer->id) }}" method="POST" style="display: none;">
                                        @csrf
                                        @method('PUT')
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <img src="{{ asset('dist/assets/images/users/default-user.png') }}" alt="customer-avatar" class="rounded-circle img-thumbnail avatar-xl">
                        <div class="mt-3">
                            <h5>{{ $customer->name }}</h5>
                            <p class="text-muted mb-1">{{ $customer->email }}</p>
                            <span class="badge {{ $customer->category == 'member' ? 'bg-success' : 'bg-info' }}">
                                {{ $customer->category == 'member' ? 'Member' : 'Non-Member' }}
                            </span>
                            <span class="badge {{ $customer->status ? 'bg-success' : 'bg-danger' }}">
                                {{ $customer->status ? 'Aktif' : 'Non-Aktif' }}
                            </span>
                        </div>
                    </div>

                    <div class="mt-4">
                        <div class="row">
                            <div class="col-6 text-end border-end">
                                <div>
                                    <p class="text-muted mb-1">Transaksi Total</p>
                                    <h5>{{ $stats['total_transactions'] }}</h5>
                                </div>
                            </div>
                            <div class="col-6">
                                <div>
                                    <p class="text-muted mb-1">Total Pengeluaran</p>
                                    <h5>Rp {{ number_format($stats['total_spent'], 0, ',', '.') }}</h5>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4">
                        <h5 class="font-size-14">Kontak Informasi</h5>
                        <div class="table-responsive">
                            <table class="table table-borderless mb-0">
                                <tbody>
                                    <tr>
                                        <th scope="row">Telepon :</th>
                                        <td>{{ $customer->phone ?: '-' }}</td>
                                    </tr>
                                    <tr>
                                        <th scope="row">WhatsApp :</th>
                                        <td>{{ $customer->whatsapp ?: '-' }}</td>
                                    </tr>
                                    <tr>
                                        <th scope="row">Jenis Kelamin :</th>
                                        <td>{{ $customer->gender == 'male' ? 'Laki-laki' : ($customer->gender == 'female' ? 'Perempuan' : '-') }}</td>
                                    </tr>
                                    <tr>
                                        <th scope="row">Umur :</th>
                                        <td>{{ $customer->age ?: '-' }}</td>
                                    </tr>
                                    <tr>
                                        <th scope="row">Alamat Saat Ini :</th>
                                        <td>{{ $customer->current_address ?: '-' }}</td>
                                    </tr>
                                    <tr>
                                        <th scope="row">Alamat Asal :</th>
                                        <td>{{ $customer->origin_address ?: '-' }}</td>
                                    </tr>
                                    <tr>
                                        <th scope="row">Tanggal Daftar :</th>
                                        <td>{{ $customer->created_at->format('d M Y') }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Customer Statistics and Recent Transactions -->
        <div class="col-lg-8">
            <!-- Statistics Cards -->
            <div class="row">
                <div class="col-md-3">
                    <div class="card mini-stats-wid">
                        <div class="card-body">
                            <div class="d-flex">
                                <div class="flex-grow-1">
                                    <p class="text-muted fw-medium">Total Transaksi</p>
                                    <h4 class="mb-0">{{ $stats['total_transactions'] }}</h4>
                                </div>
                                <div class="avatar-sm rounded-circle bg-primary align-self-center">
                                    <span class="avatar-title rounded-circle bg-primary">
                                        <i class="iconoir-receipt icon-sm"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card mini-stats-wid">
                        <div class="card-body">
                            <div class="d-flex">
                                <div class="flex-grow-1">
                                    <p class="text-muted fw-medium">Transaksi Selesai</p>
                                    <h4 class="mb-0">{{ $stats['completed_transactions'] }}</h4>
                                </div>
                                <div class="avatar-sm rounded-circle bg-success align-self-center">
                                    <span class="avatar-title rounded-circle bg-success">
                                        <i class="iconoir-check-circle icon-sm"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card mini-stats-wid">
                        <div class="card-body">
                            <div class="d-flex">
                                <div class="flex-grow-1">
                                    <p class="text-muted fw-medium">Reservasi Pending</p>
                                    <h4 class="mb-0">{{ $stats['pending'] }}</h4>
                                </div>
                                <div class="avatar-sm rounded-circle bg-warning align-self-center">
                                    <span class="avatar-title rounded-circle bg-warning">
                                        <i class="iconoir-clock icon-sm"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card mini-stats-wid">
                        <div class="card-body">
                            <div class="d-flex">
                                <div class="flex-grow-1">
                                    <p class="text-muted fw-medium">Total Pengeluaran</p>
                                    <h4 class="mb-0">Rp {{ number_format($stats['total_spent'], 0, ',', '.') }}</h4>
                                </div>
                                <div class="avatar-sm rounded-circle bg-info align-self-center">
                                    <span class="avatar-title rounded-circle bg-info">
                                        <i class="iconoir-wallet icon-sm"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Transactions -->
            <div class="card">
                <div class="card-header">
                    <div class="row align-items-center">
                        <div class="col">
                            <h4 class="card-title">Transaksi Terbaru</h4>
                        </div>
                        <div class="col-auto">
                            <a href="{{ route('admin.customers.transactions', $customer->id) }}" class="btn btn-sm btn-outline-primary">
                                Lihat Semua
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>ID Transaksi</th>
                                    <th>Tanggal</th>
                                    <th>Meja</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentTransactions as $transaction)
                                    <tr>
                                        <td>#{{ $transaction->id }}</td>
                                        <td>{{ $transaction->created_at->format('d M Y H:i') }}</td>
                                        <td>{{ $transaction->table->name }} ({{ $transaction->table->room->name }})</td>
                                        <td>Rp {{ number_format($transaction->total_price, 0, ',', '.') }}</td>
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
                                        <td colspan="6" class="text-center">Belum ada transaksi</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
