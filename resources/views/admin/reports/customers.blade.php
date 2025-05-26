@extends('layouts.app')

@section('title', 'Customer Report')

@push('styles')
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap5.min.css">
<style>
    .chart-container {
        position: relative;
        height: 250px;
    }
    .customer-card {
        transition: all 0.3s;
    }
    .customer-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    }
    .stat-card {
        border-radius: 0.75rem;
    }
    .category-badge {
        font-size: 0.75rem;
        padding: 0.35em 0.65em;
        border-radius: 50rem;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Page-Title -->
    <div class="row">
        <div class="col-sm-12">
            <div class="page-title-box">
                <div class="float-end">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="/admin/dashboard">Dashboard</a></li>
                        <li class="breadcrumb-item active">Customer Report</li>
                    </ol>
                </div>
                <h4 class="page-title">Customer Report</h4>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Filters</h4>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.reports.customers') }}" method="GET" class="row g-3">
                        <div class="col-md-4">
                            <label for="daterange" class="form-label">Date Range</label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="daterange" name="daterange"
                                    value="{{ $start_date->format('m/d/Y') }} - {{ $end_date->format('m/d/Y') }}">
                                <input type="hidden" name="start_date" id="start_date" value="{{ $start_date->format('Y-m-d') }}">
                                <input type="hidden" name="end_date" id="end_date" value="{{ $end_date->format('Y-m-d') }}">
                                <span class="input-group-text"><i class="iconoir-calendar"></i></span>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label for="category" class="form-label">Customer Category</label>
                            <select class="form-select" id="category" name="category">
                                <option value="">All Categories</option>
                                <option value="member" {{ $category == 'member' ? 'selected' : '' }}>Member</option>
                                <option value="non_member" {{ $category == 'non_member' ? 'selected' : '' }}>Non-Member</option>
                            </select>
                        </div>
                        <div class="col-md-5 d-flex align-items-end">
                            <div>
                                <button type="submit" class="btn btn-primary">Apply Filters</button>
                                <a href="{{ route('admin.reports.customers') }}" class="btn btn-outline-secondary ms-1">Reset</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Statistics -->
    <div class="row">
        <div class="col-xl-3 col-md-6">
            <div class="card stat-card bg-primary bg-opacity-15">
                <div class="card-body">
                    <div class="d-flex">
                        <div class="flex-grow-1">
                            <span class="text-muted text-uppercase fs-12 fw-bold">Total Customers</span>
                            <h3 class="mb-0">{{ count($customerMetrics) }}</h3>
                        </div>
                        <div class="align-self-center flex-shrink-0">
                            <div class="stat-icon bg-primary rounded-circle">
                                <i class="iconoir-user-circle text-white font-22"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card stat-card bg-success bg-opacity-15">
                <div class="card-body">
                    <div class="d-flex">
                        <div class="flex-grow-1">
                            <span class="text-muted text-uppercase fs-12 fw-bold">New Customers</span>
                            <h3 class="mb-0">{{ $newCustomers }}</h3>
                        </div>
                        <div class="align-self-center flex-shrink-0">
                            <div class="stat-icon bg-success rounded-circle">
                                <i class="iconoir-user-plus text-white font-22"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card stat-card bg-info bg-opacity-15">
                <div class="card-body">
                    <div class="d-flex">
                        <div class="flex-grow-1">
                            <span class="text-muted text-uppercase fs-12 fw-bold">Returning Customers</span>
                            <h3 class="mb-0">{{ $returningCustomers }}</h3>
                        </div>
                        <div class="align-self-center flex-shrink-0">
                            <div class="stat-icon bg-info rounded-circle">
                                <i class="iconoir-refresh text-white font-22"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card stat-card bg-warning bg-opacity-15">
                <div class="card-body">
                    <div class="d-flex">
                        <div class="flex-grow-1">
                            <span class="text-muted text-uppercase fs-12 fw-bold">Avg Spend per Customer</span>
                            <h3 class="mb-0">Rp {{ number_format(array_sum(array_column($customerMetrics->toArray(), 'total_spent')) / (count($customerMetrics) ?: 1), 0, ',', '.') }}</h3>
                        </div>
                        <div class="align-self-center flex-shrink-0">
                            <div class="stat-icon bg-warning rounded-circle">
                                <i class="iconoir-wallet text-white font-22"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts -->
    <div class="row">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Customer Distribution</h4>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="categoryDistributionChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Customer Engagement</h4>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="customerEngagementChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Top Spending Customers -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Top Spending Customers</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach($topCustomers as $customer)
                        <div class="col-md-3">
                            <div class="card customer-card mb-3">
                                <div class="card-body">
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="avatar-sm bg-{{ $customer['category'] == 'member' ? 'primary' : 'info' }} bg-opacity-20 text-{{ $customer['category'] == 'member' ? 'primary' : 'info' }} rounded-circle me-3">
                                            <i class="iconoir-user font-16"></i>
                                        </div>
                                        <div>
                                            <h5 class="mb-0 text-truncate" style="max-width: 150px;">{{ $customer['name'] }}</h5>
                                            <span class="category-badge bg-{{ $customer['category'] == 'member' ? 'primary' : 'info' }}">
                                                {{ ucfirst($customer['category']) }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="border-top border-bottom py-2 mb-2">
                                        <div class="row text-center">
                                            <div class="col-6">
                                                <h5 class="mb-0">{{ $customer['total_transactions'] }}</h5>
                                                <p class="text-muted mb-0 font-12">Transactions</p>
                                            </div>
                                            <div class="col-6">
                                                <h5 class="mb-0">Rp {{ number_format($customer['average_spent'], 0, ',', '.') }}</h5>
                                                <p class="text-muted mb-0 font-12">Avg. Spent</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h6 class="mb-0">Rp {{ number_format($customer['total_spent'], 0, ',', '.') }}</h6>
                                            <small class="text-muted">Total Spent</small>
                                        </div>
                                        <div class="text-end">
                                            <small class="d-block">Last Visit</small>
                                            <small class="text-primary">{{ $customer['last_visit'] }}</small>
                                        </div>
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

    <!-- Customer Data Table -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Customer Details</h4>
                    <p class="text-muted mb-0">Showing data from {{ $start_date->format('d M Y') }} to {{ $end_date->format('d M Y') }}</p>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="customer-table" class="table table-striped table-bordered dt-responsive nowrap">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Category</th>
                                    <th>Transactions</th>
                                    <th>Total Spent</th>
                                    <th>Avg. Spent</th>
                                    <th>Last Visit</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($customerMetrics as $customer)
                                <tr>
                                    <td>{{ $customer['name'] }}</td>
                                    <td>{{ $customer['email'] }}</td>
                                    <td>{{ $customer['phone'] }}</td>
                                    <td>
                                        <span class="badge bg-{{ $customer['category'] == 'member' ? 'primary' : 'info' }}">
                                            {{ ucfirst($customer['category']) }}
                                        </span>
                                    </td>
                                    <td>{{ $customer['total_transactions'] }}</td>
                                    <td>Rp {{ number_format($customer['total_spent'], 0, ',', '.') }}</td>
                                    <td>Rp {{ number_format($customer['average_spent'], 0, ',', '.') }}</td>
                                    <td>{{ $customer['last_visit'] }}</td>
                                    <td>
                                        <span class="badge bg-{{ $customer['status'] ? 'success' : 'danger' }}">
                                            {{ $customer['status'] ? 'Active' : 'Inactive' }}
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
    </div>

    <!-- Customer Insights -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Customer Insights</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h5 class="mt-0 mb-3">Key Observations</h5>
                            <div class="card card-body">
                                <h6><i class="iconoir-user-circle me-2 text-primary"></i> Customer Base</h6>
                                <p>Your customer base is composed of {{ round((isset($categoryDistribution['member']) ? $categoryDistribution['member'] : 0) / (count($customerMetrics) ?: 1) * 100) }}% members and {{ round((isset($categoryDistribution['non_member']) ? $categoryDistribution['non_member'] : 0) / (count($customerMetrics) ?: 1) * 100) }}% non-members.</p>

                                <h6 class="mt-3"><i class="iconoir-wallet me-2 text-success"></i> Spending Patterns</h6>
                                <p>Members spend on average {{ round($customerMetrics->where('category', 'member')->avg('average_spent') / ($customerMetrics->where('category', 'non_member')->avg('average_spent') ?: 1) * 100) }}% more per transaction than non-members.</p>

                                <h6 class="mt-3"><i class="iconoir-refresh me-2 text-info"></i> Retention</h6>
                                <p>{{ round($returningCustomers / (count($customerMetrics) ?: 1) * 100) }}% of your customers are returning customers who have made multiple transactions.</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h5 class="mt-0 mb-3">Recommendations</h5>
                            <div class="alert alert-info">
                                <h6><i class="iconoir-light-bulb me-2"></i> Growth Opportunities</h6>
                                <ul class="mb-0">
                                    <li>Implement a referral program to encourage members to bring in new customers</li>
                                    <li>Create special promotions to convert non-members to members</li>
                                    <li>Re-engage inactive customers with targeted special offers</li>
                                </ul>
                            </div>
                            <div class="alert alert-success mt-3">
                                <h6><i class="iconoir-check-circled me-2"></i> Retention Strategies</h6>
                                <ul class="mb-0">
                                    <li>Develop a loyalty program to reward frequent visitors</li>
                                    <li>Send personalized offers based on customer playing preferences</li>
                                    <li>Create special events or tournaments for high-value customers</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/responsive/2.2.9/js/responsive.bootstrap5.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/chart.js@3.7.0/dist/chart.min.js"></script>

<script>
    $(document).ready(function() {
        // Initialize datatable
        $('#customer-table').DataTable({
            responsive: true,
            lengthMenu: [10, 25, 50, 100],
            order: [[5, 'desc']] // Sort by total spent by default
        });

        // Initialize daterangepicker
        $('#daterange').daterangepicker({
            startDate: moment('{{ $start_date->format("Y-m-d") }}'),
            endDate: moment('{{ $end_date->format("Y-m-d") }}'),
            ranges: {
                'Today': [moment(), moment()],
                'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                'This Month': [moment().startOf('month'), moment().endOf('month')],
                'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
            }
        }, function(start, end) {
            $('#start_date').val(start.format('YYYY-MM-DD'));
            $('#end_date').val(end.format('YYYY-MM-DD'));
        });

        // Category distribution chart
        const categoryData = @json($categoryDistribution);
        const categories = Object.keys(categoryData).map(cat => ucfirst(cat));
        const categoryCounts = Object.values(categoryData);

        new Chart(document.getElementById('categoryDistributionChart').getContext('2d'), {
            type: 'pie',
            data: {
                labels: categories,
                datasets: [{
                    data: categoryCounts,
                    backgroundColor: ['#4e73df', '#36b9cc', '#1cc88a'],
                    hoverBackgroundColor: ['#2e59d9', '#2c9faf', '#17a673'],
                    hoverBorderColor: "rgba(234, 236, 244, 1)",
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                tooltips: {
                    callbacks: {
                        label: function(tooltipItem, data) {
                            var dataset = data.datasets[tooltipItem.datasetIndex];
                            var total = dataset.data.reduce((acc, curr) => acc + curr, 0);
                            var currentValue = dataset.data[tooltipItem.index];
                            var percentage = Math.round((currentValue / total) * 100);
                            return data.labels[tooltipItem.index] + ': ' + percentage + '%';
                        }
                    }
                },
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                },
                cutout: '55%'
            }
        });

        // Customer engagement chart
        const customerMetrics = @json($customerMetrics);

        // Group customers by transaction count
        const transactionRanges = {
            '1': 0,
            '2-3': 0,
            '4-6': 0,
            '7+': 0
        };

        customerMetrics.forEach(customer => {
            const txCount = customer.total_transactions;
            if (txCount === 1) {
                transactionRanges['1']++;
            } else if (txCount >= 2 && txCount <= 3) {
                transactionRanges['2-3']++;
            } else if (txCount >= 4 && txCount <= 6) {
                transactionRanges['4-6']++;
            } else {
                transactionRanges['7+']++;
            }
        });

        new Chart(document.getElementById('customerEngagementChart').getContext('2d'), {
            type: 'bar',
            data: {
                labels: Object.keys(transactionRanges),
                datasets: [{
                    label: 'Customer Count',
                    data: Object.values(transactionRanges),
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.7)',
                        'rgba(54, 162, 235, 0.7)',
                        'rgba(255, 206, 86, 0.7)',
                        'rgba(75, 192, 192, 0.7)'
                    ],
                    borderColor: [
                        'rgba(255, 99, 132, 1)',
                        'rgba(54, 162, 235, 1)',
                        'rgba(255, 206, 86, 1)',
                        'rgba(75, 192, 192, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Number of Customers'
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Transaction Count Range'
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    title: {
                        display: true,
                        text: 'Customer Distribution by Transaction Count'
                    }
                }
            }
        });

        // Helper function
        function ucfirst(string) {
            if (typeof string !== 'string' || string.length === 0) return string;
            return string.charAt(0).toUpperCase() + string.slice(1);
        }
    });
</script>
@endpush