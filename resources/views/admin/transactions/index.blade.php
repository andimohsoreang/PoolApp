@extends('layouts.app')

@section('title', 'Daftar Transaksi')

@push('links')
    <link href="{{ asset('dist/assets/libs/simple-datatables/style.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('dist/assets/libs/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
@endpush

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="page-title-box d-md-flex justify-content-md-between align-items-center">
                <h4 class="page-title">Manajemen Transaksi</h4>
                <div class="">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Transaksi</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Flash Message -->
    <div class="row">
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
    </div>

    <!-- Statistics Cards -->
    <div class="row">
        <div class="col-xl-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar-md flex-shrink-0">
                            <span class="avatar-title bg-subtle-primary text-primary rounded-circle fs-2">
                                <i class="fas fa-calendar-day"></i>
                            </span>
                        </div>
                        <div class="flex-grow-1 overflow-hidden ms-4">
                            <p class="text-muted font-size-15 mb-2"> Transaksi Hari Ini</p>
                            <h3 class="fs-4 flex-grow-1 mb-3">{{ $totalToday }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar-md flex-shrink-0">
                            <span class="avatar-title bg-subtle-warning text-warning rounded-circle fs-2">
                                <i class="fas fa-clock"></i>
                            </span>
                        </div>
                        <div class="flex-grow-1 overflow-hidden ms-4">
                            <p class="text-muted font-size-15 mb-2"> Menunggu Pembayaran</p>
                            <h3 class="fs-4 flex-grow-1 mb-3">{{ $totalPendingPayment }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar-md flex-shrink-0">
                            <span class="avatar-title bg-subtle-info text-info rounded-circle fs-2">
                                <i class="fas fa-money-bill-wave"></i>
                            </span>
                        </div>
                        <div class="flex-grow-1 overflow-hidden ms-4">
                            <p class="text-muted font-size-15 mb-2"> Pendapatan Hari Ini</p>
                            <h3 class="fs-4 flex-grow-1 mb-3">Rp {{ number_format($totalRevenueToday, 0, ',', '.') }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar-md flex-shrink-0">
                            <span class="avatar-title bg-subtle-success text-success rounded-circle fs-2">
                                <i class="fas fa-chart-line"></i>
                            </span>
                        </div>
                        <div class="flex-grow-1 overflow-hidden ms-4">
                            <p class="text-muted font-size-15 mb-2"> Total Pendapatan</p>
                            <h3 class="fs-4 flex-grow-1 mb-3">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="row align-items-center">
                        <div class="col">
                            <h4 class="card-title">Daftar Transaksi</h4>
                        </div>
                        <div class="col-auto">
                            <a href="{{ route('admin.transactions.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus me-1"></i> Buat Transaksi Baru
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Filter Form -->
                <div class="card-body pb-0">
                    <form id="filterForm" method="GET" action="{{ route('admin.transactions.index') }}">
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <label for="filter_search" class="form-label">Cari</label>
                                <input type="text" class="form-control" id="filter_search" name="search"
                                    value="{{ request('search') }}" placeholder="Kode/Nama Customer">
                            </div>
                            <div class="col-md-2 mb-3">
                                <label for="filter_status" class="form-label">Status</label>
                                <select class="form-select" id="filter_status" name="status">
                                    <option value="">Semua Status</option>
                                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="confirmed" {{ request('status') == 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                                    <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Paid</option>
                                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                </select>
                            </div>
                            <div class="col-md-2 mb-3">
                                <label for="filter_transaction_type" class="form-label">Tipe</label>
                                <select class="form-select" id="filter_transaction_type" name="transaction_type">
                                    <option value="">Semua Tipe</option>
                                    <option value="walk_in" {{ request('transaction_type') == 'walk_in' ? 'selected' : '' }}>Walk In</option>
                                    <option value="reservation" {{ request('transaction_type') == 'reservation' ? 'selected' : '' }}>Reservation</option>
                                </select>
                            </div>
                            <div class="col-md-2 mb-3">
                                <label for="filter_room_id" class="form-label">Ruangan</label>
                                <select class="form-select" id="filter_room_id" name="room_id">
                                    <option value="">Semua Ruangan</option>
                                    @foreach($rooms as $room)
                                        <option value="{{ $room->id }}" {{ request('room_id') == $room->id ? 'selected' : '' }}>
                                            {{ $room->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="filter_date_range" class="form-label">Rentang Tanggal</label>
                                <div class="input-group">
                                    <input type="text" class="form-control flatpickr-date" id="filter_date_from" name="date_from"
                                        value="{{ request('date_from') }}" placeholder="Dari">
                                    <span class="input-group-text">hingga</span>
                                    <input type="text" class="form-control flatpickr-date" id="filter_date_to" name="date_to"
                                        value="{{ request('date_to') }}" placeholder="Sampai">
                                </div>
                            </div>
                            <div class="col-md-12 mb-3 d-flex justify-content-end">
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search me-1"></i> Filter
                                    </button>
                                    <a href="{{ route('admin.transactions.index') }}" class="btn btn-secondary">
                                        <i class="fas fa-sync-alt me-1"></i> Reset
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Kode Transaksi</th>
                                    <th>Customer</th>
                                    <th>Tipe</th>
                                    <th>Meja</th>
                                    <th>Mulai</th>
                                    <th>Selesai</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($transactions as $transaction)
                                    <tr>
                                        <td>
                                            <span class="badge bg-dark">{{ $transaction->transaction_code }}</span>
                                        </td>
                                        <td>{{ $transaction->customer->name }}</td>
                                        <td>
                                            @if($transaction->transaction_type == 'walk_in')
                                                <span class="badge bg-primary">Walk In</span>
                                            @else
                                                <span class="badge bg-info">Reservation</span>
                                            @endif
                                        </td>
                                        <td>
                                            {{ $transaction->table->table_number }}
                                            <small class="d-block text-muted">{{ $transaction->table->room->name }}</small>
                                        </td>
                                        <td>{{ $transaction->start_time->format('d/m/Y H:i') }}</td>
                                        <td>{{ $transaction->end_time->format('d/m/Y H:i') }}</td>
                                        <td>Rp {{ number_format($transaction->total_price, 0, ',', '.') }}</td>
                                        <td>
                                            @if($transaction->status == 'pending')
                                                <span class="badge bg-warning">Pending</span>
                                            @elseif($transaction->status == 'confirmed')
                                                <span class="badge bg-info">Confirmed</span>
                                            @elseif($transaction->status == 'paid')
                                                <span class="badge bg-success">Paid</span>
                                            @elseif($transaction->status == 'completed')
                                                <span class="badge bg-primary">Completed</span>
                                            @elseif($transaction->status == 'cancelled')
                                                <span class="badge bg-danger">Cancelled</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group">
                                                <a href="{{ route('admin.transactions.show', $transaction->id) }}" class="btn btn-outline-info btn-sm">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                @if($transaction->status == 'pending')
                                                <a href="{{ route('admin.transactions.edit', $transaction->id) }}" class="btn btn-outline-primary btn-sm">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                @endif
                                                @if($transaction->status == 'paid' || $transaction->status == 'completed')
                                                <a href="{{ route('admin.transactions.generate-invoice', $transaction->id) }}" class="btn btn-outline-dark btn-sm">
                                                    <i class="fas fa-file-invoice"></i>
                                                </a>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center">Tidak ada data transaksi.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="d-flex justify-content-center mt-3">
                        {{ $transactions->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('dist/assets/libs/simple-datatables/umd/simple-datatables.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="{{ asset('dist/assets/libs/sweetalert2/sweetalert2.min.js') }}"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize date pickers
            flatpickr('.flatpickr-date', {
                dateFormat: "Y-m-d",
                allowInput: true
            });

            // Handle room change to filter tables
            document.getElementById('filter_room_id').addEventListener('change', function() {
                const roomId = this.value;
                const tableSelect = document.getElementById('filter_table_id');

                // Clear current options
                tableSelect.innerHTML = '<option value="">Semua Meja</option>';

                if (roomId) {
                    // Fetch tables for selected room
                    fetch(`/admin/tables/by-room/${roomId}`)
                        .then(response => response.json())
                        .then(data => {
                            data.tables.forEach(table => {
                                const option = document.createElement('option');
                                option.value = table.id;
                                option.textContent = `${table.table_number}`;
                                tableSelect.appendChild(option);
                            });
                        });
                }
            });
        });
    </script>
@endpush
