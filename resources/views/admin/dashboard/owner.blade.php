@extends('layouts.app')

@section('title', 'Owner Dashboard')

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
    .quick-action-btn {
        border-radius: 10px;
        box-shadow: 0 0.25rem 0.5rem rgba(0, 0, 0, 0.05);
        transition: all 0.3s ease;
    }
    .quick-action-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1);
    }
    .table-responsive {
        overflow-x: auto;
    }
    .chart-container {
        position: relative;
        min-height: 300px;
    }
</style>
@endsection

@section('content')
<div class="row">
    <div class="col-sm-12">
        <div class="page-title-box d-md-flex justify-content-md-between align-items-center">
            <h4 class="page-title">Owner Dashboard</h4>
            <div class="">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
                    <li class="breadcrumb-item active">Owner Dashboard</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<!-- Stats Overview Section -->
<div class="row">
    <div class="col-md-6 col-lg-3">
        <div class="card stats-card mb-4">
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <div class="stats-icon bg-primary-subtle text-primary rounded">
                        <i class="fas fa-dollar-sign fs-4"></i>
                    </div>
                    <div class="ms-3">
                        <h5 class="mb-0">Total Revenue</h5>
                        <span class="text-muted">This Month</span>
                    </div>
                </div>
                <h3 class="mb-0">{{ 'Rp ' . number_format(App\Models\Transaction::whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->sum('total_price'), 0, ',', '.') }}</h3>
                <div class="d-flex align-items-center mt-3">
                    @php
                        $lastMonthRevenue = App\Models\Transaction::whereMonth('created_at', now()->subMonth()->month)
                            ->whereYear('created_at', now()->subMonth()->year)
                            ->sum('total_price');
                        $currentMonthRevenue = App\Models\Transaction::whereMonth('created_at', now()->month)
                            ->whereYear('created_at', now()->year)
                            ->sum('total_price');
                        $percentChange = $lastMonthRevenue > 0
                            ? (($currentMonthRevenue - $lastMonthRevenue) / $lastMonthRevenue) * 100
                            : 100;
                    @endphp
                    <span class="badge {{ $percentChange >= 0 ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }}">{{ $percentChange >= 0 ? '+' : '' }}{{ number_format($percentChange, 1) }}%</span>
                    <span class="text-muted ms-2">from last month</span>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-lg-3">
        <div class="card stats-card mb-4">
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <div class="stats-icon bg-success-subtle text-success rounded">
                        <i class="fas fa-calendar-check fs-4"></i>
                    </div>
                    <div class="ms-3">
                        <h5 class="mb-0">Reservations</h5>
                        <span class="text-muted">This Month</span>
                    </div>
                </div>
                <h3 class="mb-0">{{ App\Models\Reservation::whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count() }}</h3>
                <div class="d-flex align-items-center mt-3">
                    @php
                        $lastMonthReservations = App\Models\Reservation::whereMonth('created_at', now()->subMonth()->month)
                            ->whereYear('created_at', now()->subMonth()->year)
                            ->count();
                        $currentMonthReservations = App\Models\Reservation::whereMonth('created_at', now()->month)
                            ->whereYear('created_at', now()->year)
                            ->count();
                        $percentChange = $lastMonthReservations > 0
                            ? (($currentMonthReservations - $lastMonthReservations) / $lastMonthReservations) * 100
                            : 100;
                    @endphp
                    <span class="badge {{ $percentChange >= 0 ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }}">{{ $percentChange >= 0 ? '+' : '' }}{{ number_format($percentChange, 1) }}%</span>
                    <span class="text-muted ms-2">from last month</span>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-lg-3">
        <div class="card stats-card mb-4">
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <div class="stats-icon bg-info-subtle text-info rounded">
                        <i class="fas fa-users fs-4"></i>
                    </div>
                    <div class="ms-3">
                        <h5 class="mb-0">Staff Members</h5>
                        <span class="text-muted">Active Staff</span>
                    </div>
                </div>
                <h3 class="mb-0">{{ App\Models\User::where('role', 'admin_pool')->count() }}</h3>
                <div class="d-flex align-items-center mt-3">
                    <span class="badge bg-primary text-white">Staff Management</span>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-lg-3">
        <div class="card stats-card mb-4">
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <div class="stats-icon bg-warning-subtle text-warning rounded">
                        <i class="fas fa-table fs-4"></i>
                    </div>
                    <div class="ms-3">
                        <h5 class="mb-0">Table Utilization</h5>
                        <span class="text-muted">Today</span>
                    </div>
                </div>
                @php
                    $totalTables = App\Models\Table::count();
                    $activeTables = App\Models\Table::where('status', 'active')->count();
                    $occupancyRate = $totalTables > 0 ? ($activeTables / $totalTables) * 100 : 0;
                @endphp
                <h3 class="mb-0">{{ number_format($occupancyRate, 1) }}%</h3>
                <div class="d-flex align-items-center mt-3">
                    <span class="badge bg-info-subtle text-info">{{ $activeTables }}/{{ $totalTables }}</span>
                    <span class="text-muted ms-2">tables in use</span>
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
                    <div class="col-md-2 col-6 mb-3">
                        <a href="{{ route('admin.staff.index') }}" class="btn btn-light d-block p-3 quick-action-btn">
                            <i class="fas fa-user-tie fs-4 d-block mb-2"></i>
                            <span>Manage Staff</span>
                        </a>
                    </div>
                    <div class="col-md-2 col-6 mb-3">
                        <a href="{{ route('admin.financial.reports') }}" class="btn btn-light d-block p-3 quick-action-btn">
                            <i class="fas fa-chart-line fs-4 d-block mb-2"></i>
                            <span>Financial Reports</span>
                        </a>
                    </div>
                    <div class="col-md-2 col-6 mb-3">
                        <a href="{{ route('admin.billiard-tables.index') }}" class="btn btn-light d-block p-3 quick-action-btn">
                            <i class="fas fa-chair fs-4 d-block mb-2"></i>
                            <span>Table Management</span>
                        </a>
                    </div>
                    <div class="col-md-2 col-6 mb-3">
                        <a href="{{ route('admin.reservations.index') }}" class="btn btn-light d-block p-3 quick-action-btn">
                            <i class="fas fa-calendar-alt fs-4 d-block mb-2"></i>
                            <span>Reservations</span>
                        </a>
                    </div>
                    <div class="col-md-2 col-6 mb-3">
                        <a href="{{ route('admin.food-beverages.index') }}" class="btn btn-light d-block p-3 quick-action-btn">
                            <i class="fas fa-utensils fs-4 d-block mb-2"></i>
                            <span>F&B Management</span>
                        </a>
                    </div>
                    <div class="col-md-2 col-6 mb-3">
                        <a href="{{ route('admin.settings.index') }}" class="btn btn-light d-block p-3 quick-action-btn">
                            <i class="fas fa-cog fs-4 d-block mb-2"></i>
                            <span>Business Settings</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Main Content -->
<div class="row">
    <!-- Revenue Chart -->
    <div class="col-lg-8">
        <div class="card mb-4">
            <div class="card-header">
                <div class="row align-items-center">
                    <div class="col">
                        <h5 class="card-title mb-0">Revenue Overview</h5>
                    </div>
                    <div class="col-auto">
                        <div class="dropdown">
                            <button class="btn btn-sm btn-light dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-calendar me-1"></i> This Year
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="#">This Week</a></li>
                                <li><a class="dropdown-item" href="#">This Month</a></li>
                                <li><a class="dropdown-item" href="#">This Year</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <div id="revenueChart"></div>
                </div>
            </div>
        </div>

        <!-- Staff Performance Table -->
        <div class="card mb-4">
            <div class="card-header">
                <div class="row align-items-center">
                    <div class="col">
                        <h5 class="card-title mb-0">Staff Performance</h5>
                    </div>
                    <div class="col-auto">
                        <a href="{{ route('admin.staff.index') }}" class="btn btn-sm btn-primary">View All</a>
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th>Staff</th>
                                <th>Transactions</th>
                                <th>Reservations</th>
                                <th>Revenue Generated</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $staffMembers = App\Models\User::where('role', 'admin_pool')
                                    ->take(5)
                                    ->get();
                            @endphp
                            @foreach($staffMembers as $staff)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="flex-shrink-0">
                                            <img src="{{ asset('dist/assets/images/users/avatar-1.jpg') }}" alt="Staff" class="rounded-circle" width="40">
                                        </div>
                                        <div class="ms-3">
                                            <h6 class="mb-0">{{ $staff->name }}</h6>
                                            <small class="text-muted">{{ $staff->email }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $staff->transactions->count() }}</td>
                                <td>
                                    @php
                                        $approvedReservations = App\Models\Reservation::where('approved_by', $staff->id)->count();
                                    @endphp
                                    {{ $approvedReservations }}
                                </td>
                                <td>
                                    @php
                                        $revenue = App\Models\Transaction::where('user_id', $staff->id)->sum('total_price');
                                    @endphp
                                    Rp {{ number_format($revenue, 0, ',', '.') }}
                                </td>
                                <td>
                                    <span class="badge bg-{{ $staff->status ? 'success' : 'danger' }}">
                                        {{ $staff->status ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <!-- Today's Reservations -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Today's Reservations</h5>
            </div>
            <div class="card-body">
                @php
                    $todayReservations = App\Models\Reservation::whereDate('start_time', today())
                        ->with(['customer', 'table'])
                        ->orderBy('start_time')
                        ->take(5)
                        ->get();
                @endphp

                @if($todayReservations->count() > 0)
                    <ul class="list-group list-group-flush">
                        @foreach($todayReservations as $reservation)
                        <li class="list-group-item px-0">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-1">{{ $reservation->customer->name }}</h6>
                                    <p class="text-muted mb-0">{{ $reservation->table->table_number }} • {{ $reservation->start_time->format('H:i') }} - {{ $reservation->end_time->format('H:i') }}</p>
                                </div>
                                <span class="badge bg-{{
                                    $reservation->status == 'pending' ? 'warning' :
                                    ($reservation->status == 'approved' ? 'info' :
                                    ($reservation->status == 'paid' ? 'success' : 'secondary'))
                                }}">
                                    {{ ucfirst($reservation->status) }}
                                </span>
                            </div>
                        </li>
                        @endforeach
                    </ul>
                    <div class="text-center mt-3">
                        <a href="{{ route('admin.reservations.index') }}" class="btn btn-sm btn-primary">View All Reservations</a>
                    </div>
                @else
                    <div class="text-center py-3">
                        <i class="fas fa-calendar-day fs-2 text-muted mb-2"></i>
                        <p class="mb-0">No reservations for today</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- F&B Performance -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Top F&B Items</h5>
            </div>
            <div class="card-body p-0">
                @php
                    $topFoodBeverages = App\Models\FoodBeverage::orderBy('average_rating', 'desc')
                        ->take(5)
                        ->get();
                @endphp

                <ul class="list-group list-group-flush">
                    @foreach($topFoodBeverages as $item)
                    <li class="list-group-item">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-0">{{ $item->name }}</h6>
                                <small class="text-muted">{{ ucfirst($item->category) }}</small>
                            </div>
                            <div class="text-end">
                                <div class="d-flex align-items-center">
                                    <span class="me-2">{{ number_format($item->average_rating, 1) }}</span>
                                    <div>
                                        @for($i = 1; $i <= 5; $i++)
                                            <i class="fas fa-star {{ $i <= $item->average_rating ? 'text-warning' : 'text-muted' }} small"></i>
                                        @endfor
                                    </div>
                                </div>
                                <small class="text-success">Rp {{ number_format($item->price, 0, ',', '.') }}</small>
                            </div>
                        </div>
                    </li>
                    @endforeach
                </ul>
                <div class="text-center py-3">
                    <a href="{{ route('admin.food-beverages.index') }}" class="btn btn-sm btn-primary">Manage F&B</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="{{ asset('dist/assets/libs/apexcharts/apexcharts.min.js') }}"></script>
<script>
    // Revenue Chart
    var revenueOptions = {
        series: [{
            name: 'Revenue',
            data: [
                @php
                    $revenueData = [];
                    for ($i = 0; $i < 12; $i++) {
                        $month = now()->startOfYear()->addMonths($i);
                        $revenue = App\Models\Transaction::whereMonth('created_at', $month->month)
                            ->whereYear('created_at', $month->year)
                            ->sum('total_price');
                        $revenueData[] = $revenue;
                    }
                    echo implode(', ', $revenueData);
                @endphp
            ]
        }],
        chart: {
            type: 'area',
            height: 350,
            zoom: {
                enabled: false
            },
            toolbar: {
                show: false
            }
        },
        dataLabels: {
            enabled: false
        },
        stroke: {
            curve: 'smooth',
            width: 3
        },
        colors: ['#4361ee'],
        fill: {
            type: 'gradient',
            gradient: {
                shadeIntensity: 1,
                opacityFrom: 0.7,
                opacityTo: 0.2,
                stops: [0, 90, 100]
            }
        },
        xaxis: {
            categories: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
        },
        yaxis: {
            labels: {
                formatter: function(value) {
                    return 'Rp ' + (value / 1000000).toFixed(0) + ' jt';
                }
            }
        },
        tooltip: {
            y: {
                formatter: function(value) {
                    return 'Rp ' + value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
                }
            }
        }
    };

    var revenueChart = new ApexCharts(document.querySelector("#revenueChart"), revenueOptions);
    revenueChart.render();
</script>
@endsection
