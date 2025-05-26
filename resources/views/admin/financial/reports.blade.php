@extends('layouts.app')

@section('title', 'Financial Reports')

@section('content')
<div class="container-fluid">
    <!-- Page-Title -->
    <div class="row">
        <div class="col-sm-12">
            <div class="page-title-box">
                <div class="float-end">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Financial Reports</li>
                    </ol>
                </div>
                <h4 class="page-title">Financial Reports</h4>
            </div>
        </div>
    </div>

    <!-- Financial Summary Cards -->
    <div class="row">
        <div class="col-lg-3">
            <div class="card">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <h4 class="text-muted fw-normal mt-0 mb-1">Total Revenue</h4>
                            <h2 class="mt-2 mb-0">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</h2>
                            <p class="mb-0 text-muted">{{ $startDate->format('d M Y') }} - {{ $endDate->format('d M Y') }}</p>
                        </div>
                        <div class="col-auto">
                            <div class="bg-soft-primary rounded-circle avatar-lg text-center">
                                <i class="fas fa-money-bill-wave font-22 avatar-title text-primary"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3">
            <div class="card">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <h4 class="text-muted fw-normal mt-0 mb-1">Total Expenses</h4>
                            <h2 class="mt-2 mb-0">Rp 45,000,000</h2>
                            <p class="mb-0 text-muted"><span class="text-danger"><i class="mdi mdi-arrow-up"></i> 5.4%</span> vs previous period</p>
                        </div>
                        <div class="col-auto">
                            <div class="bg-soft-danger rounded-circle avatar-lg text-center">
                                <i class="fas fa-file-invoice font-22 avatar-title text-danger"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3">
            <div class="card">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <h4 class="text-muted fw-normal mt-0 mb-1">Net Profit</h4>
                            <h2 class="mt-2 mb-0">Rp 80,000,000</h2>
                            <p class="mb-0 text-muted"><span class="text-success"><i class="mdi mdi-arrow-up"></i> 12.7%</span> vs previous period</p>
                        </div>
                        <div class="col-auto">
                            <div class="bg-soft-success rounded-circle avatar-lg text-center">
                                <i class="fas fa-chart-line font-22 avatar-title text-success"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3">
            <div class="card">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <h4 class="text-muted fw-normal mt-0 mb-1">Profit Margin</h4>
                            <h2 class="mt-2 mb-0">64%</h2>
                            <p class="mb-0 text-muted"><span class="text-success"><i class="mdi mdi-arrow-up"></i> 1.2%</span> vs previous period</p>
                        </div>
                        <div class="col-auto">
                            <div class="bg-soft-info rounded-circle avatar-lg text-center">
                                <i class="fas fa-percent font-22 avatar-title text-info"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Controls -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form class="row g-3">
                        <div class="col-md-3">
                            <label for="dateRange" class="form-label">Date Range</label>
                            <select class="form-select" id="dateRange">
                                <option selected>Last 30 days</option>
                                <option>This Month</option>
                                <option>Last Month</option>
                                <option>Last Quarter</option>
                                <option>Year to Date</option>
                                <option>Custom Range</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="reportType" class="form-label">Report Type</label>
                            <select class="form-select" id="reportType">
                                <option selected>All Reports</option>
                                <option>Revenue Report</option>
                                <option>Expense Report</option>
                                <option>Profit & Loss</option>
                                <option>Table Usage</option>
                            </select>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="button" class="btn btn-primary w-100">Apply Filters</button>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="button" class="btn btn-outline-secondary w-100">Export PDF</button>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="button" class="btn btn-outline-success w-100">Export Excel</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts -->
    <div class="row">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Revenue Trend</h4>
                </div>
                <div class="card-body">
                    <div id="revenue-chart" class="apex-charts" style="height: 380px;"></div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Revenue Distribution</h4>
                </div>
                <div class="card-body">
                    <div id="revenue-distribution-chart" class="apex-charts" style="height: 380px;"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Detailed Tables -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Revenue Breakdown</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Month</th>
                                    <th>Table Rentals</th>
                                    <th>Food & Beverages</th>
                                    <th>Membership Fees</th>
                                    <th>Other Income</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>January</td>
                                    <td>Rp 25,500,000</td>
                                    <td>Rp 8,300,000</td>
                                    <td>Rp 2,000,000</td>
                                    <td>Rp 1,200,000</td>
                                    <td>Rp 37,000,000</td>
                                </tr>
                                <tr>
                                    <td>February</td>
                                    <td>Rp 23,800,000</td>
                                    <td>Rp 7,900,000</td>
                                    <td>Rp 2,000,000</td>
                                    <td>Rp 900,000</td>
                                    <td>Rp 34,600,000</td>
                                </tr>
                                <tr>
                                    <td>March</td>
                                    <td>Rp 29,700,000</td>
                                    <td>Rp 9,200,000</td>
                                    <td>Rp 2,000,000</td>
                                    <td>Rp 1,500,000</td>
                                    <td>Rp 42,400,000</td>
                                </tr>
                            </tbody>
                            <tfoot>
                                <tr class="table-dark">
                                    <th>Total</th>
                                    <th>Rp 79,000,000</th>
                                    <th>Rp 25,400,000</th>
                                    <th>Rp 6,000,000</th>
                                    <th>Rp 3,600,000</th>
                                    <th>Rp 114,000,000</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
    $(document).ready(function() {
        // Revenue Chart
        var revenueOptions = {
            series: [{
                name: 'Revenue',
                data: [30, 40, 45, 50, 49, 60, 70, 91, 125, 105, 110, 120]
            }],
            chart: {
                type: 'area',
                height: 350,
                toolbar: {
                    show: false
                }
            },
            dataLabels: {
                enabled: false
            },
            stroke: {
                curve: 'smooth'
            },
            xaxis: {
                categories: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
            },
            tooltip: {
                y: {
                    formatter: function (val) {
                        return "Rp " + val.toLocaleString();
                    }
                }
            },
            colors: ['#4fc6e1']
        };
        var revenueChart = new ApexCharts(document.querySelector("#revenue-chart"), revenueOptions);
        revenueChart.render();

        // Revenue Distribution Chart
        var distributionOptions = {
            series: [42, 26, 15, 17],
            chart: {
                type: 'pie',
                height: 350
            },
            labels: ['Table Rentals', 'Food & Beverages', 'Membership', 'Other'],
            colors: ['#5b73e8', '#50a5f1', '#34c38f', '#f1b44c'],
            legend: {
                position: 'bottom'
            },
            responsive: [{
                breakpoint: 480,
                options: {
                    chart: {
                        width: 200
                    },
                    legend: {
                        position: 'bottom'
                    }
                }
            }]
        };
        var distributionChart = new ApexCharts(document.querySelector("#revenue-distribution-chart"), distributionOptions);
        distributionChart.render();
    });
</script>
@endpush
