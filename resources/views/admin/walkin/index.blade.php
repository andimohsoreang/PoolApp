@extends('layouts.app')

@section('title', 'Walk-in Management')

@push('links')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link href="{{ asset('dist/assets/libs/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css">
    <style>
        .table-card {
            border-radius: 15px;
            transition: all 0.3s ease;
            border: none;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }

        .table-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        }

        .table-available {
            border-left: 5px solid #10b981;
        }

        .table-in-use {
            border-left: 5px solid #f59e0b;
        }

        .table-maintenance {
            border-left: 5px solid #ef4444;
        }

        .timeline-container {
            position: relative;
            width: 100%;
            height: 100px;
            margin: 20px 0;
            border-radius: 8px;
            background-color: #f8f9fa;
            overflow: hidden;
            box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .timeline-hours {
            display: flex;
            height: 25px;
            border-bottom: 1px solid #dee2e6;
            background-color: #fff;
        }

        .timeline-hour {
            flex: 1;
            text-align: center;
            font-size: 11px;
            padding-top: 4px;
            border-right: 1px dashed #dee2e6;
            color: #6b7280;
        }

        .timeline-slots {
            position: relative;
            height: 75px;
            padding: 10px 0;
        }

        .timeline-slot {
            position: absolute;
            height: 55px;
            top: 10px;
            border-radius: 6px;
            padding: 4px 8px;
            font-size: 11px;
            font-weight: 500;
            color: white;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .timeline-slot-booked {
            background-color: #ef4444;
        }

        .timeline-slot-selected {
            background-color: #2563eb;
        }

        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
        }

        .status-available {
            background-color: #d1fae5;
            color: #065f46;
        }

        .status-in-use {
            background-color: #fef3c7;
            color: #92400e;
        }

        .status-maintenance {
            background-color: #fee2e2;
            color: #991b1b;
        }

        .room-header {
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            color: white;
            padding: 20px;
            border-radius: 15px 15px 0 0;
            margin-bottom: 20px;
        }

        .room-header h5 {
            margin: 0;
            font-size: 1.25rem;
            font-weight: 600;
        }

        .room-stats {
            display: flex;
            gap: 20px;
            margin-top: 10px;
        }

        .stat-item {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.875rem;
        }

        .stat-item i {
            font-size: 1rem;
        }

        .table-info {
            padding: 15px;
            background-color: #f8fafc;
            border-radius: 10px;
            margin-bottom: 15px;
        }

        .table-info h6 {
            color: #64748b;
            font-size: 0.875rem;
            margin-bottom: 8px;
        }

        .table-info p {
            margin-bottom: 5px;
            font-size: 0.875rem;
        }

        .table-info i {
            width: 20px;
            color: #94a3b8;
        }

        .action-buttons {
            display: flex;
            gap: 8px;
            margin-top: 15px;
        }

        .action-buttons .btn {
            padding: 6px 12px;
            font-size: 0.875rem;
            border-radius: 6px;
        }

        .date-selector {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: white;
            padding: 20px;
            border-radius: 15px;
            margin-bottom: 20px;
        }

        .date-selector h5 {
            margin: 0;
            font-size: 1.25rem;
            font-weight: 600;
        }

        .date-selector .form-control {
            border: none;
            border-radius: 8px;
            padding: 10px 15px;
            font-size: 0.875rem;
        }

        .date-selector .btn {
            border: none;
            border-radius: 8px;
            padding: 10px 20px;
            font-weight: 500;
        }

        .status-legend {
            background: white;
            padding: 15px;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .status-legend .legend-item {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 8px;
        }

        .status-legend .legend-color {
            width: 12px;
            height: 12px;
            border-radius: 3px;
        }

        .legend-available {
            background-color: #10b981;
        }

        .legend-in-use {
            background-color: #f59e0b;
        }

        .legend-maintenance {
            background-color: #ef4444;
        }

        .icon-box {
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .table-info-item,
        .transaction-info-item {
            padding: 10px;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .table-info-item:hover,
        .transaction-info-item:hover {
            background-color: #f8f9fa;
        }

        .empty-state {
            padding: 40px 20px;
        }

        .nav-tabs .nav-link {
            color: #6c757d;
            border: none;
            padding: 10px 20px;
            font-weight: 500;
        }

        .nav-tabs .nav-link.active {
            color: #3b82f6;
            border-bottom: 2px solid #3b82f6;
            background: none;
        }

        .table> :not(caption)>*>* {
            padding: 1rem;
        }

        .table tbody tr {
            transition: all 0.3s ease;
        }

        .table tbody tr:hover {
            background-color: #f8f9fa;
        }
    </style>
@endpush

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="page-title-box d-md-flex justify-content-md-between align-items-center">
                <h4 class="page-title">Walk-in Management</h4>
                <div class="">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Walk-in</li>
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

    <!-- Date Selection and Status Legend -->
    <div class="row mb-4">
        <div class="col-md-8">
            <div class="date-selector">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <h5 class="mb-3">Pilih Tanggal</h5>
                        <form id="dateForm" action="{{ route('admin.walkin.index') }}" method="GET">
                            <div class="input-group">
                                <input type="text" class="form-control flatpickr-date" id="selectedDate" name="date"
                                    value="{{ $selectedDate->format('Y-m-d') }}" placeholder="Pilih tanggal">
                                <button class="btn btn-light" type="submit">
                                    <i class="fas fa-search me-1"></i> Tampilkan
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="status-legend">
                <h6 class="mb-3">Status Meja</h6>
                <div class="legend-item">
                    <div class="legend-color legend-available"></div>
                    <span>Tersedia</span>
                </div>
                <div class="legend-item">
                    <div class="legend-color legend-in-use"></div>
                    <span>Digunakan</span>
                </div>
                <div class="legend-item">
                    <div class="legend-color legend-maintenance"></div>
                    <span>Maintenance</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Rooms and Tables -->
    @foreach ($tablesByRoom as $roomData)
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="room-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5>{{ $roomData['room']->name }}</h5>
                            <div class="room-stats">
                                <div class="stat-item">
                                    <i class="fas fa-table"></i>
                                    <span>{{ $roomData['tables']->count() }} Meja</span>
                                </div>
                                <div class="stat-item">
                                    <i class="fas fa-check-circle"></i>
                                    <span>{{ $roomData['tables']->where('current_status', 'available')->count() }}
                                        Tersedia</span>
                                </div>
                                <div class="stat-item">
                                    <i class="fas fa-clock"></i>
                                    <span>{{ $roomData['tables']->where('current_status', 'in_use')->count() }}
                                        Digunakan</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @foreach ($roomData['tables'] as $table)
                                <div class="col-md-4 mb-4">
                                    <div class="card table-card
                                @if ($table->current_status == 'available') table-available
                                @elseif($table->current_status == 'in_use') table-in-use
                                @else table-maintenance @endif"
                                        data-table-id="{{ $table->id }}">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-center mb-3">
                                                <h5 class="card-title mb-0">Meja #{{ $table->table_number }}</h5>
                                                <span
                                                    class="status-badge
                                            @if ($table->current_status == 'available') status-available
                                            @elseif($table->current_status == 'in_use') status-in-use
                                            @else status-maintenance @endif">
                                                    @if ($table->current_status == 'available')
                                                        Tersedia
                                                    @elseif($table->current_status == 'in_use')
                                                        Digunakan
                                                    @else
                                                        Maintenance
                                                    @endif
                                                </span>
                                            </div>

                                            <div class="table-info">
                                                <h6><i class="fas fa-info-circle"></i> Informasi Meja</h6>
                                                <p><i class="fas fa-tag"></i> Brand: {{ $table->brand }}</p>
                                                <p><i class="fas fa-map-marker-alt"></i> Ruangan:
                                                    {{ $roomData['room']->name }}</p>

                                                @if ($table->current_status == 'in_use' && $table->current_transaction)
                                                    <div class="mt-3">
                                                        <h6><i class="fas fa-user"></i> Informasi Transaksi</h6>
                                                        <p><i class="fas fa-user"></i> Customer:
                                                            {{ $table->current_transaction->customer->name }}</p>
                                                        <p><i class="fas fa-clock"></i> Waktu:
                                                            {{ $table->current_transaction->start_time->format('H:i') }} -
                                                            {{ $table->current_transaction->end_time->format('H:i') }}</p>
                                                        <p><i class="fas fa-hourglass-half"></i> Sisa:
                                                            {{ $table->current_transaction->start_time->diffForHumans(null, true) }}
                                                            lagi</p>
                                                    </div>
                                                @endif
                                            </div>

                                            <div class="action-buttons">
                                                @if ($table->current_status != 'maintenance')
                                                    <button type="button" class="btn btn-primary view-table-details"
                                                        data-table-id="{{ $table->id }}">
                                                        <i class="fas fa-eye me-1"></i> Detail
                                                    </button>

                                                    <a href="{{ route('admin.walkin.timeline', ['table' => $table->id, 'date' => $selectedDate->format('Y-m-d')]) }}"
                                                        class="btn btn-info">
                                                        <i class="fas fa-calendar-alt me-1"></i> Timeline
                                                    </a>

                                                    @if ($table->current_status == 'available')
                                                        <a href="{{ route('admin.walkin.create', ['table' => $table->id]) }}"
                                                            class="btn btn-success">
                                                            <i class="fas fa-plus me-1"></i> Buat Transaksi
                                                        </a>
                                                    @endif

                                                    @if ($table->current_status == 'in_use')
                                                        <button type="button" class="btn btn-warning extend-transaction"
                                                            data-transaction-id="{{ $table->current_transaction->id }}">
                                                            <i class="fas fa-clock me-1"></i> Perpanjang
                                                        </button>
                                                        <button type="button" class="btn btn-danger stop-transaction"
                                                            data-transaction-id="{{ $table->current_transaction->id }}">
                                                            <i class="fas fa-stop-circle me-1"></i> Stop
                                                        </button>
                                                    @endif
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endforeach

    <!-- Table Details Modal -->
    <div class="modal fade" id="tableDetailsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-gradient-primary text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-table me-2"></i>
                        Detail Meja
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="text-center" id="tableDetailsLoader">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2">Memuat detail meja...</p>
                    </div>
                    <div id="tableDetailsContent" style="display: none;">
                        <!-- Table Information -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="card h-100">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Informasi Meja</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-info-item d-flex align-items-center mb-3">
                                            <div class="icon-box bg-primary bg-opacity-10 p-2 rounded me-3">
                                                <i class="fas fa-hashtag text-primary"></i>
                                            </div>
                                            <div>
                                                <small class="text-muted d-block">Nomor Meja</small>
                                                <strong id="tableNumber"></strong>
                                            </div>
                                        </div>
                                        <div class="table-info-item d-flex align-items-center mb-3">
                                            <div class="icon-box bg-success bg-opacity-10 p-2 rounded me-3">
                                                <i class="fas fa-door-open text-success"></i>
                                            </div>
                                            <div>
                                                <small class="text-muted d-block">Ruangan</small>
                                                <strong id="tableRoom"></strong>
                                            </div>
                                        </div>
                                        <div class="table-info-item d-flex align-items-center mb-3">
                                            <div class="icon-box bg-info bg-opacity-10 p-2 rounded me-3">
                                                <i class="fas fa-tag text-info"></i>
                                            </div>
                                            <div>
                                                <small class="text-muted d-block">Brand</small>
                                                <strong id="tableBrand"></strong>
                                            </div>
                                        </div>
                                        <div class="table-info-item d-flex align-items-center">
                                            <div class="icon-box bg-warning bg-opacity-10 p-2 rounded me-3">
                                                <i class="fas fa-clock text-warning"></i>
                                            </div>
                                            <div>
                                                <small class="text-muted d-block">Total Jam Terpakai</small>
                                                <strong id="tableHoursUsed"></strong>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6" id="currentTransactionSection" style="display: none;">
                                <div class="card h-100">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0"><i class="fas fa-user-clock me-2"></i>Transaksi Aktif</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="transaction-info-item d-flex align-items-center mb-3">
                                            <div class="icon-box bg-primary bg-opacity-10 p-2 rounded me-3">
                                                <i class="fas fa-user text-primary"></i>
                                            </div>
                                            <div>
                                                <small class="text-muted d-block">Customer</small>
                                                <strong id="currentCustomer"></strong>
                                            </div>
                                        </div>
                                        <div class="transaction-info-item d-flex align-items-center mb-3">
                                            <div class="icon-box bg-success bg-opacity-10 p-2 rounded me-3">
                                                <i class="fas fa-receipt text-success"></i>
                                            </div>
                                            <div>
                                                <small class="text-muted d-block">Kode Transaksi</small>
                                                <strong id="currentTransactionCode"></strong>
                                            </div>
                                        </div>
                                        <div class="transaction-info-item d-flex align-items-center mb-3">
                                            <div class="icon-box bg-info bg-opacity-10 p-2 rounded me-3">
                                                <i class="fas fa-clock text-info"></i>
                                            </div>
                                            <div>
                                                <small class="text-muted d-block">Waktu</small>
                                                <strong id="currentTime"></strong>
                                            </div>
                                        </div>
                                        <div class="transaction-info-item d-flex align-items-center">
                                            <div class="icon-box bg-warning bg-opacity-10 p-2 rounded me-3">
                                                <i class="fas fa-hourglass-half text-warning"></i>
                                            </div>
                                            <div>
                                                <small class="text-muted d-block">Sisa Waktu</small>
                                                <strong id="remainingTime"></strong>
                                            </div>
                                        </div>
                                        <div class="mt-3">
                                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                                <button type="button" class="btn btn-warning extend-transaction"
                                                    id="modalExtendBtn" data-transaction-id="">
                                                    <i class="fas fa-clock me-1"></i> Perpanjang
                                                </button>
                                                <button type="button" class="btn btn-danger stop-transaction"
                                                    id="modalStopBtn" data-transaction-id="">
                                                    <i class="fas fa-stop-circle me-1"></i> Stop
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Tabs -->
                        <div class="card">
                            <div class="card-header bg-light">
                                <ul class="nav nav-tabs card-header-tabs" id="tableDetailTabs" role="tablist">
                                    <li class="nav-item">
                                        <a class="nav-link active" id="bookings-tab" data-bs-toggle="tab"
                                            href="#bookings" role="tab">
                                            <i class="fas fa-calendar-check me-2"></i>Reservasi Hari Ini
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" id="available-tab" data-bs-toggle="tab" href="#available"
                                            role="tab">
                                            <i class="fas fa-clock me-2"></i>Slot Tersedia
                                        </a>
                                    </li>
                                </ul>
                            </div>
                            <div class="card-body">
                                <div class="tab-content">
                                    <div class="tab-pane fade show active" id="bookings" role="tabpanel">
                                        <div class="table-responsive">
                                            <table class="table table-hover">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>Customer</th>
                                                        <th>Waktu</th>
                                                        <th>Durasi</th>
                                                        <th>Status</th>
                                                        <th>Harga</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="bookingsTableBody">
                                                    <!-- Bookings will be loaded here -->
                                                </tbody>
                                            </table>
                                        </div>
                                        <div id="noBookings" class="text-center py-4" style="display: none;">
                                            <div class="empty-state">
                                                <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                                                <p class="text-muted mb-0">Tidak ada reservasi untuk hari ini</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="tab-pane fade" id="available" role="tabpanel">
                                        <div class="table-responsive">
                                            <table class="table table-hover">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>Waktu Mulai</th>
                                                        <th>Waktu Selesai</th>
                                                        <th>Durasi</th>
                                                        <th>Aksi</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="availableSlotsTableBody">
                                                    <!-- Available slots will be loaded here -->
                                                </tbody>
                                            </table>
                                        </div>
                                        <div id="noSlots" class="text-center py-4" style="display: none;">
                                            <div class="empty-state">
                                                <i class="fas fa-clock fa-3x text-muted mb-3"></i>
                                                <p class="text-muted mb-0">Tidak ada slot tersedia untuk hari ini</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> Tutup
                    </button>
                    <a href="#" id="createTransactionBtn" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i> Buat Transaksi
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Extend Transaction Modal -->
    <div class="modal fade" id="extendTransactionModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Perpanjang Transaksi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="extendTransactionForm" action="" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="extension_hours" class="form-label">Durasi Perpanjangan (jam)</label>
                            <input type="number" class="form-control" id="extension_hours" name="extension_hours"
                                min="0.5" max="12" step="0.5" value="1">
                            <div class="form-text">Masukkan durasi perpanjangan dalam jam (minimal 0.5, maksimal 12)</div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Perpanjang</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Stop Transaction Modal -->
    <div class="modal fade" id="stopTransactionModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Hentikan Transaksi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="stopTransactionForm" action="" method="POST">
                    @csrf
                    <div class="modal-body">
                        <p>Apakah Anda yakin ingin menghentikan transaksi ini?</p>
                        <p class="text-danger">Perhatian: Transaksi akan ditandai sebagai selesai dan waktu akhir akan
                            diperbarui ke waktu saat ini.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-danger">Hentikan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="{{ asset('dist/assets/libs/sweetalert2/sweetalert2.min.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize flatpickr for date picker
            flatpickr(".flatpickr-date", {
                dateFormat: "Y-m-d",
                allowInput: true
            });

            // Handle table details modal
            $('.view-table-details').on('click', function() {
                const tableId = $(this).data('table-id');
                const selectedDate = $('#selectedDate').val();

                // Show modal and loading indicator
                $('#tableDetailsModal').modal('show');
                $('#tableDetailsLoader').show();
                $('#tableDetailsContent').hide();

                // Fetch table details
                $.ajax({
                    url: "{{ route('admin.walkin.table-details') }}",
                    type: "GET",
                    data: {
                        table_id: tableId,
                        date: selectedDate
                    },
                    success: function(response) {
                        // Update table info
                        $('#tableNumber').text(response.table.table_number);
                        $('#tableRoom').text(response.table.room);
                        $('#tableBrand').text(response.table.brand);
                        $('#tableHoursUsed').text(response.table.total_hours_used + ' jam');

                        // Handle current transaction if exists
                        if (response.current_transaction) {
                            $('#currentTransactionSection').show();
                            $('#currentCustomer').text(response.current_transaction
                                .customer_name);
                            $('#currentTransactionCode').text(response.current_transaction
                                .transaction_code);
                            $('#currentTime').text(response.current_transaction.start_time +
                                ' - ' + response.current_transaction.end_time);
                            $('#remainingTime').text(response.current_transaction
                                .remaining_minutes + ' menit');

                            // Set transaction ID for action buttons
                            $('#modalExtendBtn, #modalStopBtn').data('transaction-id', response
                                .current_transaction.id);
                        } else {
                            $('#currentTransactionSection').hide();
                        }

                        // Update bookings tab
                        const bookingsBody = $('#bookingsTableBody');
                        bookingsBody.empty();

                        if (response.transactions && response.transactions.length > 0) {
                            $('#noBookings').hide();

                            response.transactions.forEach(function(transaction) {
                                const duration = calculateDuration(transaction
                                    .start_time, transaction.end_time);
                                bookingsBody.append(`
                                <tr>
                                    <td>${transaction.customer_name}</td>
                                    <td>${transaction.start_time} - ${transaction.end_time}</td>
                                    <td>${duration}</td>
                                    <td><span class="badge ${
                                        transaction.status === 'paid' ? 'bg-success' :
                                        transaction.status === 'pending' ? 'bg-warning' : 'bg-secondary'
                                    }">${transaction.status}</span></td>
                                    <td>Rp ${new Intl.NumberFormat('id-ID').format(transaction.total_price)}</td>
                                </tr>
                            `);
                            });
                        } else {
                            $('#noBookings').show();
                        }

                        // Update available slots tab
                        const slotsBody = $('#availableSlotsTableBody');
                        slotsBody.empty();

                        if (response.available_slots && response.available_slots.length > 0) {
                            $('#noSlots').hide();

                            response.available_slots.forEach(function(slot) {
                                const duration = formatDuration(slot.duration_hours);
                                slotsBody.append(`
                                <tr>
                                    <td>${slot.start}</td>
                                    <td>${slot.end}</td>
                                    <td>${duration}</td>
                                    <td>
                                        <a href="{{ route('admin.walkin.create', ['table' => ':id']) }}?start_time=${selectedDate} ${slot.start}"
                                           class="btn btn-sm btn-primary">
                                            <i class="fas fa-plus me-1"></i> Buat Transaksi
                                        </a>
                                    </td>
                                </tr>
                            `.replace(':id', tableId));
                            });
                        } else {
                            $('#noSlots').show();
                        }

                        // Hide loader and show content
                        $('#tableDetailsLoader').hide();
                        $('#tableDetailsContent').show();
                    },
                    error: function(xhr) {
                        console.error(xhr);
                        Swal.fire({
                            title: 'Error!',
                            text: 'Gagal memuat detail meja',
                            icon: 'error'
                        });
                        $('#tableDetailsModal').modal('hide');
                    }
                });
            });

            // Handle extend transaction
            $('.extend-transaction').on('click', function() {
                const transactionId = $(this).data('transaction-id');
                $('#extendTransactionForm').attr('action',
                    "{{ route('admin.walkin.extendTransaction', ['id' => ':id']) }}".replace(':id',
                        transactionId));
                $('#extendTransactionModal').modal('show');
            });

            // Handle stop transaction
            $('.stop-transaction').on('click', function() {
                const transactionId = $(this).data('transaction-id');
                $('#stopTransactionForm').attr('action',
                    "{{ route('admin.walkin.stopTransaction', ['id' => ':id']) }}".replace(':id',
                        transactionId));
                $('#stopTransactionModal').modal('show');
            });

            // Process expired sessions every minute
            setInterval(function() {
                $.ajax({
                    url: "{{ route('admin.walkin.processExpiredSessions') }}",
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(response) {
                        if (response.count > 0) {
                            console.log(`${response.count} expired sessions processed`);
                            location.reload();
                        }
                    }
                });
            }, 60000); // Check every minute

            // Auto refresh page every 5 minutes
            setTimeout(function() {
                window.location.reload();
            }, 5 * 60 * 1000);
        });

        // Helper function to calculate duration between two times
        function calculateDuration(startTime, endTime) {
            const [startHour, startMinute] = startTime.split(':').map(Number);
            const [endHour, endMinute] = endTime.split(':').map(Number);

            // Convert to minutes since midnight
            let startMinutes = startHour * 60 + startMinute;
            let endMinutes = endHour * 60 + endMinute;

            // If end time is earlier than start time, it means it's the next day
            if (endMinutes < startMinutes) {
                endMinutes += 24 * 60; // Add 24 hours worth of minutes
            }

            const durationMinutes = endMinutes - startMinutes;
            const hours = Math.floor(durationMinutes / 60);
            const minutes = durationMinutes % 60;

            return formatDuration(hours + (minutes / 60));
        }

        // Helper function to format duration
        function formatDuration(hours) {
            const wholeHours = Math.floor(hours);
            const minutes = Math.round((hours - wholeHours) * 60);

            if (wholeHours === 0) {
                return `${minutes} menit`;
            } else if (minutes === 0) {
                return `${wholeHours} jam`;
            } else {
                return `${wholeHours} jam ${minutes} menit`;
            }
        }
    </script>
@endpush
