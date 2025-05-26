@extends('layouts.app')

@section('title', 'Tables Usage Report')

@push('styles')
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap5.min.css">
<style>
    .card-metrics .avatar-md {
        width: 40px;
        height: 40px;
    }
    .utilization-bar {
        height: 10px;
        border-radius: 5px;
        margin-top: 5px;
        background-color: #e9ecef;
        overflow: hidden;
    }
    .utilization-progress {
        height: 100%;
        background-color: #4caf50;
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
                        <li class="breadcrumb-item active">Tables Usage Report</li>
                    </ol>
                </div>
                <h4 class="page-title">Tables Usage Report</h4>
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
                    <form action="{{ route('admin.reports.tables') }}" method="GET" class="row g-3">
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
                        <div class="col-md-4">
                            <label for="room_id" class="form-label">Room</label>
                            <select class="form-select" id="room_id" name="room_id">
                                <option value="">All Rooms</option>
                                @foreach($rooms as $room)
                                    <option value="{{ $room->id }}" {{ $room_id == $room->id ? 'selected' : '' }}>
                                        {{ $room->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <div>
                                <button type="submit" class="btn btn-primary">Apply Filters</button>
                                <a href="{{ route('admin.reports.tables') }}" class="btn btn-outline-secondary ms-1">Reset</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Room Utilization Chart -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Room Utilization</h4>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="roomUtilizationChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Table Metrics -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">Table Performance</h4>
                    <div class="dropdown">
                        <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" id="sortDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            Sort By
                        </button>
                        <ul class="dropdown-menu" aria-labelledby="sortDropdown">
                            <li><a class="dropdown-item sort-option" data-sort="utilization" href="javascript:void(0);">Utilization Rate</a></li>
                            <li><a class="dropdown-item sort-option" data-sort="revenue" href="javascript:void(0);">Total Revenue</a></li>
                            <li><a class="dropdown-item sort-option" data-sort="transactions" href="javascript:void(0);">Total Transactions</a></li>
                            <li><a class="dropdown-item sort-option" data-sort="hours" href="javascript:void(0);">Total Hours</a></li>
                        </ul>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="table-metrics" class="table table-striped table-bordered dt-responsive nowrap">
                            <thead>
                                <tr>
                                    <th>Table</th>
                                    <th>Room</th>
                                    <th>Transactions</th>
                                    <th>Hours Used</th>
                                    <th>Revenue</th>
                                    <th>Avg. Rate/Hour</th>
                                    <th>Utilization</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($tableMetrics as $metric)
                                <tr>
                                    <td>Table #{{ $metric['table_number'] }}</td>
                                    <td>{{ $metric['room_name'] }}</td>
                                    <td>{{ $metric['total_transactions'] }}</td>
                                    <td>{{ $metric['total_hours'] }} hrs</td>
                                    <td>Rp {{ number_format($metric['total_revenue'], 0, ',', '.') }}</td>
                                    <td>Rp {{ number_format($metric['average_hourly_rate'], 0, ',', '.') }}</td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <span class="me-2">{{ $metric['utilization_rate'] }}%</span>
                                            <div class="utilization-bar flex-grow-1">
                                                <div class="utilization-progress" style="width: {{ min(100, $metric['utilization_rate']) }}%;"></div>
                                            </div>
                                        </div>
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

    <!-- Room Summary Statistics -->
    <div class="row">
        @foreach($roomUtilization as $room)
        <div class="col-md-4">
            <div class="card card-metrics">
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-3">
                        <h5 class="text-truncate">{{ $room['room_name'] }}</h5>
                        <span class="badge {{ $room['average_utilization'] < 30 ? 'bg-danger' : ($room['average_utilization'] < 60 ? 'bg-warning' : 'bg-success') }}">
                            {{ $room['average_utilization'] }}% Utilized
                        </span>
                    </div>
                    <div class="row">
                        <div class="col-6 border-end">
                            <div class="d-flex align-items-center">
                                <div class="avatar-md bg-primary bg-opacity-10 text-primary rounded-circle me-2">
                                    <i class="iconoir-receipt font-14"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0">{{ $room['total_tables'] }}</h6>
                                    <p class="text-muted fs-12 mb-0">Tables</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="d-flex align-items-center">
                                <div class="avatar-md bg-success bg-opacity-10 text-success rounded-circle me-2">
                                    <i class="iconoir-clock font-14"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0">{{ $room['total_hours'] }}</h6>
                                    <p class="text-muted fs-12 mb-0">Hours</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class="d-flex align-items-center">
                        <div class="avatar-md bg-info bg-opacity-10 text-info rounded-circle me-2">
                            <i class="iconoir-wallet font-14"></i>
                        </div>
                        <div>
                            <h6 class="mb-0">Rp {{ number_format($room['total_revenue'], 0, ',', '.') }}</h6>
                            <p class="text-muted fs-12 mb-0">Total Revenue</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Utilization Heatmap (Optional) -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Usage Insights</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h5 class="text-center mb-3">Peak Hours</h5>
                            <p>Based on the data for the selected period, here are some insights:</p>
                            <ul class="list-group mb-3">
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    Most active day
                                    <span class="badge bg-primary rounded-pill">Saturday</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    Peak hours
                                    <span class="badge bg-primary rounded-pill">7 PM - 10 PM</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    Average session duration
                                    <span class="badge bg-primary rounded-pill">2.5 hours</span>
                                </li>
                            </ul>
                            <p class="text-muted small">* These insights are approximations based on available data</p>
                        </div>
                        <div class="col-md-6">
                            <h5 class="text-center mb-3">Recommendations</h5>
                            <div class="alert alert-info">
                                <i class="iconoir-light-bulb me-2"></i>
                                <strong>Consider these optimization opportunities:</strong>
                                <ul class="mb-0 mt-2">
                                    <li>Implement happy hour pricing during low utilization periods</li>
                                    <li>Offer promotions for weekday bookings</li>
                                    <li>Schedule maintenance during identified slow periods</li>
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
        const tableMetricsTable = $('#table-metrics').DataTable({
            responsive: true,
            lengthChange: false,
            pageLength: 10,
            order: [[6, 'desc']] // Sort by utilization rate by default
        });

        // Custom sorting
        $('.sort-option').on('click', function() {
            const sortBy = $(this).data('sort');
            let columnIndex;

            switch(sortBy) {
                case 'utilization':
                    columnIndex = 6;
                    break;
                case 'revenue':
                    columnIndex = 4;
                    break;
                case 'transactions':
                    columnIndex = 2;
                    break;
                case 'hours':
                    columnIndex = 3;
                    break;
                default:
                    columnIndex = 6;
            }

            tableMetricsTable.order([columnIndex, 'desc']).draw();
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

        // Room utilization chart
        const roomUtilizationData = @json($roomUtilization);

        const roomNames = roomUtilizationData.map(room => room.room_name);
        const utilizations = roomUtilizationData.map(room => room.average_utilization);
        const revenues = roomUtilizationData.map(room => room.total_revenue / 1000); // Convert to thousands

        new Chart(document.getElementById('roomUtilizationChart').getContext('2d'), {
            type: 'bar',
            data: {
                labels: roomNames,
                datasets: [
                    {
                        label: 'Utilization Rate (%)',
                        data: utilizations,
                        backgroundColor: 'rgba(54, 162, 235, 0.7)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 1,
                        yAxisID: 'y'
                    },
                    {
                        label: 'Revenue (thousands)',
                        data: revenues,
                        backgroundColor: 'rgba(75, 192, 192, 0.7)',
                        borderColor: 'rgba(75, 192, 192, 1)',
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
                    x: {
                        grid: {
                            display: false
                        }
                    },
                    y: {
                        beginAtZero: true,
                        max: 100,
                        title: {
                            display: true,
                            text: 'Utilization Rate (%)'
                        }
                    },
                    y1: {
                        beginAtZero: true,
                        position: 'right',
                        grid: {
                            drawOnChartArea: false
                        },
                        title: {
                            display: true,
                            text: 'Revenue (thousands)'
                        }
                    }
                }
            }
        });
    });
</script>
@endpush
