@extends('layouts.app')

@section('title', 'Super Admin Dashboard')

@section('styles')
<link href="{{ asset('dist/assets/libs/apexcharts/apexcharts.css') }}" rel="stylesheet" type="text/css">
<link href="{{ asset('dist/assets/libs/jsvectormap/jsvectormap.min.css') }}" rel="stylesheet" type="text/css">
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
    .system-status-badge {
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
    .status-warning {
        background-color: #ffba00;
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
    .recent-activity-item {
        border-left: 2px solid #e0e0e0;
        padding-left: 20px;
        position: relative;
        padding-bottom: 20px;
    }
    .recent-activity-item:before {
        content: '';
        width: 12px;
        height: 12px;
        border-radius: 50%;
        background: #4361ee;
        position: absolute;
        left: -7px;
        top: 0;
    }
    .recent-activity-item:last-child {
        padding-bottom: 0;
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
            <h4 class="page-title">Super Admin Dashboard</h4>
            <div class="">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
                    <li class="breadcrumb-item active">Super Dashboard</li>
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
                        <span class="text-muted">All Time</span>
                    </div>
                </div>
                <h3 class="mb-0">{{ 'Rp ' . number_format(App\Models\Transaction::sum('total_price'), 0, ',', '.') }}</h3>
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
                    <div class="stats-icon bg-info-subtle text-info rounded">
                        <i class="fas fa-users fs-4"></i>
                    </div>
                    <div class="ms-3">
                        <h5 class="mb-0">Total Customers</h5>
                        <span class="text-muted">Active Users</span>
                    </div>
                </div>
                <h3 class="mb-0">{{ App\Models\Customer::count() }}</h3>
                <div class="d-flex align-items-center mt-3">
                    @php
                        $lastMonthCustomers = App\Models\Customer::whereMonth('created_at', now()->subMonth()->month)
                            ->whereYear('created_at', now()->subMonth()->year)
                            ->count();
                        $currentMonthCustomers = App\Models\Customer::whereMonth('created_at', now()->month)
                            ->whereYear('created_at', now()->year)
                            ->count();
                        $percentChange = $lastMonthCustomers > 0
                            ? (($currentMonthCustomers - $lastMonthCustomers) / $lastMonthCustomers) * 100
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
                    <div class="stats-icon bg-warning-subtle text-warning rounded">
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
                    <div class="stats-icon bg-danger-subtle text-danger rounded">
                        <i class="fas fa-table fs-4"></i>
                    </div>
                    <div class="ms-3">
                        <h5 class="mb-0">Table Usage</h5>
                        <span class="text-muted">Occupancy Rate</span>
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
                        <a href="{{ route('admin.reservations.index') }}" class="btn btn-light d-block p-3 quick-action-btn">
                            <i class="fas fa-calendar-alt fs-4 d-block mb-2"></i>
                            <span>Manage Reservations</span>
                        </a>
                    </div>
                    <div class="col-md-2 col-6 mb-3">
                        <a href="{{ route('admin.customers.index') }}" class="btn btn-light d-block p-3 quick-action-btn">
                            <i class="fas fa-user fs-4 d-block mb-2"></i>
                            <span>Manage Customers</span>
                        </a>
                    </div>
                    <div class="col-md-2 col-6 mb-3">
                        <a href="{{ route('admin.billiard-tables.index') }}" class="btn btn-light d-block p-3 quick-action-btn">
                            <i class="fas fa-chair fs-4 d-block mb-2"></i>
                            <span>Manage Tables</span>
                        </a>
                    </div>
                    <div class="col-md-2 col-6 mb-3">
                        <a href="{{ route('admin.food-beverages.index') }}" class="btn btn-light d-block p-3 quick-action-btn">
                            <i class="fas fa-utensils fs-4 d-block mb-2"></i>
                            <span>Manage F&B</span>
                        </a>
                    </div>
                    <div class="col-md-2 col-6 mb-3">
                        <a href="{{ route('admin.transactions.index') }}" class="btn btn-light d-block p-3 quick-action-btn">
                            <i class="fas fa-file-invoice-dollar fs-4 d-block mb-2"></i>
                            <span>Transactions</span>
                        </a>
                    </div>
                    <div class="col-md-2 col-6 mb-3">
                        <a href="{{ route('admin.settings.index') }}" class="btn btn-light d-block p-3 quick-action-btn">
                            <i class="fas fa-cog fs-4 d-block mb-2"></i>
                            <span>System Settings</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Reservation and Revenue Charts -->
<div class="row">
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
                                <li><a class="dropdown-item" href="#">All Time</a></li>
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

        <div class="card mb-4">
            <div class="card-header">
                <div class="row align-items-center">
                    <div class="col">
                        <h5 class="card-title mb-0">Recent Transactions</h5>
                    </div>
                    <div class="col-auto">
                        <a href="{{ route('admin.transactions.index') }}" class="btn btn-sm btn-primary">View All</a>
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th>ID</th>
                                <th>Customer</th>
                                <th>Table</th>
                                <th>Date</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $recentTransactions = App\Models\Transaction::with(['customer', 'table'])
                                    ->latest()
                                    ->take(5)
                                    ->get();
                            @endphp
                            @foreach($recentTransactions as $transaction)
                            <tr>
                                <td>#{{ $transaction->transaction_code }}</td>
                                <td>{{ $transaction->customer ? $transaction->customer->name : 'Guest' }}</td>
                                <td>{{ $transaction->table ? $transaction->table->table_number : 'N/A' }}</td>
                                <td>{{ $transaction->created_at->format('d M Y, H:i') }}</td>
                                <td>{{ $transaction->formatted_total_price }}</td>
                                <td><span class="badge bg-{{ $transaction->status == 'completed' ? 'success' : ($transaction->status == 'pending' ? 'warning' : 'danger') }}">{{ ucfirst($transaction->status) }}</span></td>
                                <td><a href="{{ route('admin.transactions.show', $transaction->id) }}" class="btn btn-sm btn-light"><i class="fas fa-eye"></i></a></td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Reservation Breakdown</h5>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <div id="reservationChart"></div>
                </div>
                @php
                    $rooms = App\Models\Room::with('tables.reservations')->get();
                    $roomStats = [];
                    $totalReservations = App\Models\Reservation::count();

                    foreach ($rooms as $room) {
                        $reservationCount = 0;
                        foreach ($room->tables as $table) {
                            $reservationCount += $table->reservations->count();
                        }
                        $percentage = $totalReservations > 0 ? ($reservationCount / $totalReservations) * 100 : 0;
                        $roomStats[] = [
                            'name' => $room->name,
                            'count' => $reservationCount,
                            'percentage' => $percentage
                        ];
                    }
                @endphp
                <div class="mt-4">
                    @foreach($roomStats as $index => $stat)
                    <div class="d-flex align-items-center mb-3">
                        <div class="flex-shrink-0">
                            <span class="badge rounded-circle p-2" style="background-color: {{ ['#4361ee', '#ff6b6b', '#10b981'][$index % 3] }};"></span>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-0">{{ $stat['name'] }}</h6>
                            <small class="text-muted">{{ number_format($stat['percentage'], 1) }}% of total reservations</small>
                        </div>
                        <div class="flex-shrink-0">
                            <span class="fw-bold">{{ $stat['count'] }}</span>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">
                <div class="row align-items-center">
                    <div class="col">
                        <h5 class="card-title mb-0">Recent Activity</h5>
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="p-3">
                    @php
                        $activities = App\Models\ActivityLog::latest()->take(5)->get();
                    @endphp
                    @foreach($activities as $activity)
                    <div class="recent-activity-item">
                        <div class="mb-1">
                            <span class="badge bg-{{ $activity->log_type == 'reservation' ? 'primary' : ($activity->log_type == 'payment' ? 'success' : ($activity->log_type == 'user' ? 'info' : 'warning')) }} text-white">{{ ucfirst($activity->log_type) }}</span>
                            <small class="text-muted float-end">{{ $activity->created_at->diffForHumans() }}</small>
                        </div>
                        <p class="mb-0">{{ $activity->description }}</p>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

<!-- System Status Section -->
<div class="row">
    <div class="col-lg-8">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Food & Beverage Performance</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th>Item</th>
                                <th>Category</th>
                                <th>Price</th>
                                <th>Rating</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $topFoodBeverages = App\Models\FoodBeverage::orderBy('average_rating', 'desc')
                                    ->take(5)
                                    ->get();
                            @endphp
                            @foreach($topFoodBeverages as $item)
                            <tr>
                                <td>{{ $item->name }}</td>
                                <td>{{ ucfirst($item->category) }}</td>
                                <td>Rp {{ number_format($item->price, 0, ',', '.') }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <span class="me-2">{{ number_format($item->average_rating, 1) }}</span>
                                        <div>
                                            @for($i = 1; $i <= 5; $i++)
                                                <i class="fas fa-star {{ $i <= $item->average_rating ? 'text-warning' : 'text-muted' }}"></i>
                                            @endfor
                                        </div>
                                        <span class="ms-2 text-muted small">({{ $item->rating_count }})</span>
                                    </div>
                                </td>
                                <td><span class="badge bg-{{ $item->is_available ? 'success' : 'danger' }}">{{ $item->is_available ? 'Available' : 'Unavailable' }}</span></td>
                                <td><a href="{{ route('admin.food-beverages.edit', $item->id) }}" class="btn btn-sm btn-light"><i class="fas fa-edit"></i></a></td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">User Statistics</h5>
            </div>
            <div class="card-body">
                <div class="mb-4">
                    <div class="d-flex justify-content-between mb-1">
                        <span>Super Admins</span>
                        <span>{{ App\Models\User::where('role', 'super_admin')->count() }}</span>
                    </div>
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar bg-primary" role="progressbar" style="width: {{ (App\Models\User::where('role', 'super_admin')->count() / App\Models\User::count()) * 100 }}%"></div>
                    </div>
                </div>

                <div class="mb-4">
                    <div class="d-flex justify-content-between mb-1">
                        <span>Owners</span>
                        <span>{{ App\Models\User::where('role', 'owner')->count() }}</span>
                    </div>
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar bg-success" role="progressbar" style="width: {{ (App\Models\User::where('role', 'owner')->count() / App\Models\User::count()) * 100 }}%"></div>
                    </div>
                </div>

                <div class="mb-4">
                    <div class="d-flex justify-content-between mb-1">
                        <span>Admin Pool</span>
                        <span>{{ App\Models\User::where('role', 'admin_pool')->count() }}</span>
                    </div>
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar bg-warning" role="progressbar" style="width: {{ (App\Models\User::where('role', 'admin_pool')->count() / App\Models\User::count()) * 100 }}%"></div>
                    </div>
                </div>

                <div class="mb-4">
                    <div class="d-flex justify-content-between mb-1">
                        <span>Customers</span>
                        <span>{{ App\Models\Customer::count() }}</span>
                    </div>
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar bg-info" role="progressbar" style="width: 100%"></div>
                    </div>
                </div>

                <hr>

                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span>New Users This Month</span>
                    <span class="badge bg-success">+{{ App\Models\Customer::whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count() }}</span>
                </div>

                <div class="d-flex justify-content-between align-items-center">
                    <span>Active Customers</span>
                    <span class="badge bg-primary">{{ App\Models\Customer::count() }}</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="{{ asset('dist/assets/libs/apexcharts/apexcharts.min.js') }}"></script>
<script src="{{ asset('dist/assets/libs/jsvectormap/jsvectormap.min.js') }}"></script>
<script src="{{ asset('dist/assets/libs/jsvectormap/maps/world.js') }}"></script>

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
                    // Format to millions with Rp
                    return 'Rp ' + (value / 1000000).toFixed(0) + ' jt';
                }
            }
        },
        tooltip: {
            y: {
                formatter: function(value) {
                    // Format with full number and Rp
                    return 'Rp ' + value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
                }
            }
        }
    };

    var revenueChart = new ApexCharts(document.querySelector("#revenueChart"), revenueOptions);
    revenueChart.render();

    // Reservation Pie Chart
    var reservationOptions = {
        series: [
            @php
                $roomPercentages = array_map(function($stat) {
                    return round($stat['percentage'], 1);
                }, $roomStats);
                echo implode(', ', $roomPercentages);
            @endphp
        ],
        chart: {
            type: 'donut',
            height: 300
        },
        colors: ['#4361ee', '#ff6b6b', '#10b981'],
        labels: [
            @php
                $roomNames = array_map(function($stat) {
                    return "'".$stat['name']."'";
                }, $roomStats);
                echo implode(', ', $roomNames);
            @endphp
        ],
        legend: {
            show: false
        },
        plotOptions: {
            pie: {
                donut: {
                    size: '65%'
                }
            }
        },
        dataLabels: {
            enabled: false
        },
        responsive: [{
            breakpoint: 480,
            options: {
                chart: {
                    height: 250
                }
            }
        }],
        tooltip: {
            y: {
                formatter: function(value) {
                    return value + '%';
                }
            }
        }
    };

    var reservationChart = new ApexCharts(document.querySelector("#reservationChart"), reservationOptions);
    reservationChart.render();
</script>
@endsection
