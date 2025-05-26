@extends('layouts.app')

@section('title', 'Sales Report')

@push('styles')
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap5.min.css">
<style>
    .summary-card {
        transition: all 0.3s;
    }
    .summary-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    }
    .chart-container {
        position: relative;
        height: 300px;
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
                        <li class="breadcrumb-item active">Sales Report</li>
                    </ol>
                </div>
                <h4 class="page-title">Sales Report</h4>
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
                    <form action="{{ route('admin.reports.sales') }}" method="GET" class="row g-3">
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
                            <label for="transaction_type" class="form-label">Transaction Type</label>
                            <select class="form-select" id="transaction_type" name="transaction_type">
                                <option value="">All Types</option>
                                <option value="walkin" {{ $transaction_type == 'walkin' ? 'selected' : '' }}>Walk-in</option>
                                <option value="reservation" {{ $transaction_type == 'reservation' ? 'selected' : '' }}>Reservation</option>
                            </select>
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <div>
                                <button type="submit" class="btn btn-primary">Apply Filters</button>
                                <a href="{{ route('admin.reports.sales') }}" class="btn btn-outline-secondary ms-1">Reset</a>
                            </div>
                        </div>
                        <div class="col-md-2 d-flex align-items-end justify-content-end">
                            <a href="{{ route('admin.reports.export-sales-csv', ['start_date' => $start_date->format('Y-m-d'), 'end_date' => $end_date->format('Y-m-d'), 'transaction_type' => $transaction_type]) }}" class="btn btn-success">
                                <i class="iconoir-file-download me-1"></i> Export CSV
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Statistics -->
    <div class="row">
        <div class="col-md-4">
            <div class="card summary-card bg-primary bg-opacity-10">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar-md bg-primary rounded-circle">
                            <i class="iconoir-receipt text-white font-22 avatar-title"></i>
                        </div>
                        <div class="ms-3">
                            <h4 class="mt-0 mb-1">{{ $totalTransactions }}</h4>
                            <p class="text-muted mb-0">Total Transactions</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card summary-card bg-success bg-opacity-10">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar-md bg-success rounded-circle">
                            <i class="iconoir-wallet text-white font-22 avatar-title"></i>
                        </div>
                        <div class="ms-3">
                            <h4 class="mt-0 mb-1">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</h4>
                            <p class="text-muted mb-0">Total Revenue</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card summary-card bg-info bg-opacity-10">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar-md bg-info rounded-circle">
                            <i class="iconoir-calculator text-white font-22 avatar-title"></i>
                        </div>
                        <div class="ms-3">
                            <h4 class="mt-0 mb-1">Rp {{ number_format($averageTransaction, 0, ',', '.') }}</h4>
                            <p class="text-muted mb-0">Average Transaction</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts -->
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Daily Sales</h4>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="dailySalesChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Transaction Breakdown</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <h5 class="text-center">Payment Methods</h5>
                            <div class="chart-container" style="height: 150px;">
                                <canvas id="paymentMethodsChart"></canvas>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <h5 class="text-center">Transaction Types</h5>
                            <div class="chart-container" style="height: 150px;">
                                <canvas id="transactionTypesChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Top Selling Tables -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Top Performing Tables</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Table</th>
                                    <th>Room</th>
                                    <th>Transactions</th>
                                    <th>Revenue</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($topTables as $table)
                                <tr>
                                    <td>Table #{{ $table['table_number'] }}</td>
                                    <td>{{ $table['room_name'] }}</td>
                                    <td>{{ $table['count'] }}</td>
                                    <td>Rp {{ number_format($table['revenue'], 0, ',', '.') }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center">No data available</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Transactions Table -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Transaction Details</h4>
                    <p class="text-muted mb-0">Showing data from {{ $start_date->format('d M Y') }} to {{ $end_date->format('d M Y') }}</p>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="transactions-table" class="table table-striped table-bordered dt-responsive nowrap">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Date</th>
                                    <th>Customer</th>
                                    <th>Table</th>
                                    <th>Duration</th>
                                    <th>Type</th>
                                    <th>Payment</th>
                                    <th>Status</th>
                                    <th>Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($transactions as $transaction)
                                <tr>
                                    <td>{{ $transaction->transaction_code }}</td>
                                    <td>{{ \Carbon\Carbon::parse($transaction->created_at)->format('d M Y H:i') }}</td>
                                    <td>{{ $transaction->customer->name }}</td>
                                    <td>{{ $transaction->table->table_number }} ({{ $transaction->table->room->name }})</td>
                                    <td>
                                        @php
                                            $start = \Carbon\Carbon::parse($transaction->start_time);
                                            $end = \Carbon\Carbon::parse($transaction->end_time ?? now());
                                            $duration = $start->diff($end);
                                            echo $duration->format('%H:%I');
                                        @endphp
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $transaction->transaction_type == 'walkin' ? 'info' : 'primary' }}">
                                            {{ ucfirst($transaction->transaction_type) }}
                                        </span>
                                    </td>
                                    <td>{{ ucfirst($transaction->payment_method) }}</td>
                                    <td>
                                        <span class="badge bg-{{ $transaction->status == 'completed' ? 'success' : 'warning' }}">
                                            {{ ucfirst($transaction->status) }}
                                        </span>
                                    </td>
                                    <td>Rp {{ number_format($transaction->total_price, 0, ',', '.') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
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
        $('#transactions-table').DataTable({
            responsive: true,
            lengthMenu: [10, 25, 50, 100],
            order: [[1, 'desc']]
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

        // Charts initialization
        const dailySalesData = @json($dailySales);
        const dates = Object.keys(dailySalesData);
        const counts = dates.map(date => dailySalesData[date].count);
        const totals = dates.map(date => dailySalesData[date].total / 1000); // Convert to thousands for better display

        // Daily sales chart
        new Chart(document.getElementById('dailySalesChart').getContext('2d'), {
            type: 'bar',
            data: {
                labels: dates,
                datasets: [
                    {
                        label: 'Revenue (thousands)',
                        data: totals,
                        backgroundColor: 'rgba(75, 192, 192, 0.5)',
                        borderColor: 'rgba(75, 192, 192, 1)',
                        borderWidth: 1,
                        yAxisID: 'y'
                    },
                    {
                        label: 'Transactions',
                        data: counts,
                        backgroundColor: 'rgba(153, 102, 255, 0.5)',
                        borderColor: 'rgba(153, 102, 255, 1)',
                        borderWidth: 1,
                        type: 'line',
                        yAxisID: 'y1'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        title: {
                            display: true,
                            text: 'Revenue (thousands)'
                        }
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        grid: {
                            drawOnChartArea: false,
                        },
                        title: {
                            display: true,
                            text: 'Transactions'
                        }
                    }
                }
            }
        });

        // Payment methods chart
        const paymentMethods = @json($paymentMethods);
        new Chart(document.getElementById('paymentMethodsChart').getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: Object.keys(paymentMethods).map(method => ucfirst(method)),
                datasets: [{
                    data: Object.values(paymentMethods),
                    backgroundColor: ['#36a2eb', '#ff6384', '#4bc0c0', '#ff9f40', '#9966ff'],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            boxWidth: 12
                        }
                    }
                }
            }
        });

        // Transaction types chart
        const transactionTypes = @json($transactionTypes);
        new Chart(document.getElementById('transactionTypesChart').getContext('2d'), {
            type: 'pie',
            data: {
                labels: Object.keys(transactionTypes).map(type => ucfirst(type)),
                datasets: [{
                    data: Object.values(transactionTypes),
                    backgroundColor: ['#ff6384', '#36a2eb'],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            boxWidth: 12
                        }
                    }
                }
            }
        });

        // Helper function
        function ucfirst(string) {
            return string.charAt(0).toUpperCase() + string.slice(1);
        }
    });
</script>
@endpush
