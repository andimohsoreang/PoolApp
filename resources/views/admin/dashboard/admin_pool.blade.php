@extends('layouts.app')

@section('title', 'Admin Pool Dashboard')

@section('styles')
<link href="{{ asset('dist/assets/libs/apexcharts/apexcharts.css') }}" rel="stylesheet" type="text/css">
<style>
    .stats-card {
        transition: all 0.3s ease;
        border: none;
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.05);
    }
    .stats-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 1rem 2rem rgba(0, 0, 0, 0.1);
    }
    .stats-icon {
        width: 50px;
        height: 50px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 12px;
    }
    .table-responsive {
        overflow-x: auto;
    }
    .quick-action-btn {
        border-radius: 10px;
        box-shadow: 0 0.25rem 0.5rem rgba(0, 0, 0, 0.05);
        transition: all 0.3s ease;
    }
    .quick-action-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1);
    }
    .status-badge {
        width: 12px;
        height: 12px;
        border-radius: 50%;
        display: inline-block;
        margin-right: 5px;
    }
    .status-active {
        background-color: #38cb89;
    }
    .status-inactive {
        background-color: #ef4444;
    }
    .status-pending {
        background-color: #ffba00;
    }
    .table-status {
        width: 100%;
        height: 100%;
        min-height: 180px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-direction: column;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    .table-status:hover {
        transform: translateY(-5px);
    }
    .table-available {
        background-color: rgba(56, 203, 137, 0.1);
        border: 1px solid rgba(56, 203, 137, 0.2);
    }
    .table-available:hover {
        background-color: rgba(56, 203, 137, 0.2);
    }
    .table-occupied {
        background-color: rgba(239, 68, 68, 0.1);
        border: 1px solid rgba(239, 68, 68, 0.2);
    }
    .table-occupied:hover {
        background-color: rgba(239, 68, 68, 0.2);
    }
    .table-reserved {
        background-color: rgba(255, 186, 0, 0.1);
        border: 1px solid rgba(255, 186, 0, 0.2);
    }
    .table-reserved:hover {
        background-color: rgba(255, 186, 0, 0.2);
    }
    .table-maintenance {
        background-color: rgba(100, 116, 139, 0.1);
        border: 1px solid rgba(100, 116, 139, 0.2);
    }
    .table-maintenance:hover {
        background-color: rgba(100, 116, 139, 0.2);
    }
</style>
@endsection

@section('content')
<div class="row">
    <div class="col-sm-12">
        <div class="page-title-box d-md-flex justify-content-md-between align-items-center">
            <h4 class="page-title">Admin Pool Dashboard</h4>
            <div class="">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
                    <li class="breadcrumb-item active">Pool Dashboard</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<!-- Stats Overview -->
<div class="row">
    <div class="col-md-6 col-lg-3">
        <div class="card stats-card mb-4">
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <div class="stats-icon bg-primary-subtle text-primary rounded">
                        <i class="fas fa-clipboard-list fs-4"></i>
                    </div>
                    <div class="ms-3">
                        <h5 class="mb-0">Today's Reservations</h5>
                        <span class="text-muted">Active bookings</span>
                    </div>
                </div>
                @php
                    $todayReservationsCount = App\Models\Reservation::whereDate('start_time', today())
                        ->whereIn('status', ['pending', 'approved', 'paid'])
                        ->count();
                @endphp
                <h3 class="mb-0">{{ $todayReservationsCount }}</h3>
                <div class="d-flex align-items-center mt-3">
                    <a href="{{ route('admin.reservations.index') }}" class="btn btn-sm btn-primary">Manage Reservations</a>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-lg-3">
        <div class="card stats-card mb-4">
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <div class="stats-icon bg-warning-subtle text-warning rounded">
                        <i class="fas fa-user-clock fs-4"></i>
                    </div>
                    <div class="ms-3">
                        <h5 class="mb-0">Pending Approvals</h5>
                        <span class="text-muted">Awaiting action</span>
                    </div>
                </div>
                @php
                    $pendingReservations = App\Models\Reservation::where('status', 'pending')
                        ->count();
                @endphp
                <h3 class="mb-0">{{ $pendingReservations }}</h3>
                <div class="d-flex align-items-center mt-3">
                    <a href="{{ route('admin.reservations.pending') }}" class="btn btn-sm btn-warning">Review Pending</a>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-lg-3">
        <div class="card stats-card mb-4">
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <div class="stats-icon bg-success-subtle text-success rounded">
                        <i class="fas fa-table fs-4"></i>
                    </div>
                    <div class="ms-3">
                        <h5 class="mb-0">Tables Available</h5>
                        <span class="text-muted">Ready for use</span>
                    </div>
                </div>
                @php
                    $availableTables = App\Models\Table::where('status', 'normal')->count();
                    $totalTables = App\Models\Table::count();
                @endphp
                <h3 class="mb-0">{{ $availableTables }} / {{ $totalTables }}</h3>
                <div class="d-flex align-items-center mt-3">
                    <a href="{{ route('admin.billiard-tables.index') }}" class="btn btn-sm btn-success">View Tables</a>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-lg-3">
        <div class="card stats-card mb-4">
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <div class="stats-icon bg-info-subtle text-info rounded">
                        <i class="fas fa-calendar-day fs-4"></i>
                    </div>
                    <div class="ms-3">
                        <h5 class="mb-0">Today's Sales</h5>
                        <span class="text-muted">Revenue today</span>
                    </div>
                </div>
                @php
                    $todaySales = App\Models\Transaction::whereDate('created_at', today())
                        ->sum('total_price');
                @endphp
                <h3 class="mb-0">Rp {{ number_format($todaySales, 0, ',', '.') }}</h3>
                <div class="d-flex align-items-center mt-3">
                    <a href="{{ route('admin.transactions.index') }}" class="btn btn-sm btn-info">View Transactions</a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Action Buttons -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title mb-3">Quick Actions</h5>
                <div class="row">
                    <div class="col-md-3 col-6 mb-3">
                        <a href="{{ route('admin.reservations.create') }}" class="btn btn-primary d-block p-3 quick-action-btn">
                            <i class="fas fa-calendar-plus fs-4 d-block mb-2"></i>
                            <span>New Reservation</span>
                        </a>
                    </div>
                    <div class="col-md-3 col-6 mb-3">
                        <a href="{{ route('admin.transactions.create') }}" class="btn btn-success d-block p-3 quick-action-btn">
                            <i class="fas fa-cash-register fs-4 d-block mb-2"></i>
                            <span>New Transaction</span>
                        </a>
                    </div>
                    <div class="col-md-3 col-6 mb-3">
                        <a href="{{ route('admin.customers.index') }}" class="btn btn-info d-block p-3 quick-action-btn">
                            <i class="fas fa-users fs-4 d-block mb-2"></i>
                            <span>Manage Customers</span>
                        </a>
                    </div>
                    <div class="col-md-3 col-6 mb-3">
                        <a href="{{ route('admin.food-beverages.orders') }}" class="btn btn-warning d-block p-3 quick-action-btn">
                            <i class="fas fa-utensils fs-4 d-block mb-2"></i>
                            <span>F&B Orders</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Main Content -->
<div class="row">
    <!-- Table Status Overview -->
    <div class="col-lg-8">
        <div class="card mb-4">
            <div class="card-header">
                <div class="row align-items-center">
                    <div class="col">
                        <h5 class="card-title mb-0">Table Status Overview</h5>
                    </div>
                    <div class="col-auto">
                        <a href="{{ route('admin.billiard-tables.index') }}" class="btn btn-sm btn-primary">View All Tables</a>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    @php
                        $rooms = App\Models\Room::with('tables')->get();
                    @endphp

                    @foreach($rooms as $room)
                        <div class="col-12 mb-4">
                            <h6 class="text-uppercase text-muted mb-3">{{ $room->name }}</h6>
                            <div class="row">
                                @foreach($room->tables as $table)
                                    <div class="col-md-3 col-6 mb-3">
                                        @php
                                            $tableClass = 'table-available';
                                            $statusText = 'Available';
                                            $statusIcon = 'check-circle';
                                            $statusColor = 'success';

                                            if ($table->status === 'active') {
                                                $tableClass = 'table-occupied';
                                                $statusText = 'Occupied';
                                                $statusIcon = 'user';
                                                $statusColor = 'danger';
                                            } elseif ($table->status === 'reserved') {
                                                $tableClass = 'table-reserved';
                                                $statusText = 'Reserved';
                                                $statusIcon = 'clock';
                                                $statusColor = 'warning';
                                            } elseif ($table->status === 'maintenance') {
                                                $tableClass = 'table-maintenance';
                                                $statusText = 'Maintenance';
                                                $statusIcon = 'tools';
                                                $statusColor = 'secondary';
                                            }

                                            // Check if there's an active reservation for this table
                                            $hasReservation = App\Models\Reservation::where('table_id', $table->id)
                                                ->whereDate('start_time', today())
                                                ->whereIn('status', ['approved', 'paid'])
                                                ->exists();

                                            if ($hasReservation && $table->status === 'normal') {
                                                $tableClass = 'table-reserved';
                                                $statusText = 'Reserved Today';
                                                $statusIcon = 'calendar-check';
                                                $statusColor = 'warning';
                                            }
                                        @endphp

                                        <a href="{{ route('admin.billiard-tables.show', $table->id) }}" class="text-decoration-none">
                                            <div class="table-status {{ $tableClass }}">
                                                <div class="text-{{ $statusColor }} mb-2">
                                                    <i class="fas fa-{{ $statusIcon }} fa-2x"></i>
                                                </div>
                                                <h5 class="mb-0">{{ $table->table_number }}</h5>
                                                <p class="text-muted mb-0">{{ $statusText }}</p>
                                            </div>
                                        </a>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <!-- Today's Reservations List -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Today's Reservations</h5>
            </div>
            <div class="card-body">
                @php
                    $todayReservations = App\Models\Reservation::whereDate('start_time', today())
                        ->with(['customer', 'table'])
                        ->orderBy('start_time')
                        ->get();
                @endphp

                @if($todayReservations->count() > 0)
                    <ul class="list-group list-group-flush">
                        @foreach($todayReservations as $reservation)
                        <li class="list-group-item">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-1">{{ $reservation->start_time->format('H:i') }} - {{ $reservation->end_time->format('H:i') }}</h6>
                                    <p class="text-muted mb-0">{{ $reservation->customer->name }} • {{ $reservation->table->table_number }}</p>
                                </div>
                                <div>
                                    <span class="badge bg-{{
                                        $reservation->status == 'pending' ? 'warning' :
                                        ($reservation->status == 'approved' ? 'info' :
                                        ($reservation->status == 'paid' ? 'success' :
                                        ($reservation->status == 'completed' ? 'primary' :
                                        ($reservation->status == 'cancelled' ? 'danger' : 'secondary'))))
                                    }}">
                                        {{ ucfirst($reservation->status) }}
                                    </span>
                                    <a href="{{ route('admin.reservations.show', $reservation->id) }}" class="btn btn-sm btn-light ms-2">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </div>
                            </div>
                        </li>
                        @endforeach
                    </ul>
                @else
                    <div class="text-center py-5">
                        <i class="fas fa-calendar-alt fa-3x text-muted mb-3"></i>
                        <p class="mb-0">No reservations for today</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Recent Transactions -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Recent Transactions</h5>
            </div>
            <div class="card-body p-0">
                @php
                    $recentTransactions = App\Models\Transaction::orderBy('created_at', 'desc')
                        ->with(['customer', 'table'])
                        ->take(5)
                        ->get();
                @endphp

                <ul class="list-group list-group-flush">
                    @foreach($recentTransactions as $transaction)
                    <li class="list-group-item">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-1">{{ $transaction->transaction_code }}</h6>
                                <p class="mb-0 text-muted">
                                    {{ $transaction->customer ? $transaction->customer->name : 'Guest' }} •
                                    {{ $transaction->table ? $transaction->table->table_number : 'N/A' }}
                                </p>
                                <small class="text-muted">{{ $transaction->created_at->format('d M H:i') }}</small>
                            </div>
                            <div class="text-end">
                                <span class="d-block text-success fw-bold">{{ $transaction->formatted_total_price }}</span>
                                <a href="{{ route('admin.transactions.show', $transaction->id) }}" class="btn btn-sm btn-light mt-1">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </div>
                        </div>
                    </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="{{ asset('dist/assets/libs/apexcharts/apexcharts.min.js') }}"></script>
<script>
    // Any additional JavaScript can go here
</script>
@endsection
