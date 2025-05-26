<!DOCTYPE html>
<html lang="en" dir="ltr" data-startbar="light" data-bs-theme="light">

<head>
    <meta charset="utf-8" />
    <title>@yield('title', 'Admin Dashboard') | Pool Billiard System</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta content="Pool Billiard Management System" name="description" />
    <meta content="" name="author" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- App favicon -->
    <link rel="shortcut icon" href="{{ asset('dist/assets/images/favicon.ico') }}">

    @stack('links')
    <!-- App css -->
    <link href="{{ asset('dist/assets/css/bootstrap.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('dist/assets/css/icons.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('dist/assets/css/app.min.css') }}" rel="stylesheet" type="text/css" />

    <!-- Socket.IO -->
    <script src="https://cdn.socket.io/4.6.0/socket.io.min.js" integrity="sha384-c79GN5VsunZvi+Q/WObgk2in0CbZsHnjEqvFxC5DxHn9lTfNce2WW6h2pH6u/kF+" crossorigin="anonymous"></script>

    @stack('styles')
</head>

<body>

    <!-- Top Bar Start -->
    @include('components.topbar')
    <!-- Top Bar End -->

    <!-- leftbar-tab-menu -->
    @include('components.sidebar')
    <!-- end leftbar-tab-menu-->

    <div class="page-wrapper">

        <!-- Page Content-->
        <div class="page-content">
            <div class="container-fluid">
                @yield('content')

                <!--Start Footer-->
                @include('components.footer')
                <!--end footer-->
            </div>
            <!-- end page content -->
        </div>
        <!-- end page-wrapper -->

        <!-- Javascript  -->
        <!-- vendor js -->
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

        <!-- Optional: Moment.js CDN -->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>

        <script src="{{ asset('dist/assets/libs/apexcharts/apexcharts.min.js') }}"></script>

        @stack('scripts')
        <script src="{{ asset('dist/assets/libs/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
        <script src="{{ asset('dist/assets/libs/simplebar/simplebar.min.js') }}"></script>
        <script src="{{ asset('dist/assets/js/app.js') }}"></script>

        <!-- Socket.IO and Notification Scripts -->
        <script src="{{ asset('js/notification-socket.js') }}"></script>
        <script>
            // Initialize notification socket when document is ready
            document.addEventListener('DOMContentLoaded', function() {
                console.log('DOM fully loaded, initializing notification socket');
                if (typeof initNotificationSocket === 'function') {
                    try {
                        initNotificationSocket();
                        console.log('Notification socket initialized successfully');
                    } catch (error) {
                        console.error('Error initializing notification socket:', error);
                    }
                } else {
                    console.error('initNotificationSocket function not found');
                }

                // Check if script loaded properly
                setTimeout(function() {
                    if (typeof io === 'undefined') {
                        console.error('Socket.IO library not loaded properly');
                    }
                }, 1000);
            });
        </script>

        <script>
            $(document).ready(function() {
                // Initialize charts and other components here
                if ($("#monthly_income").length) {
                    var options = {
                        chart: {
                            height: 350,
                            type: 'area',
                            toolbar: {
                                show: false
                            }
                        },
                        dataLabels: {
                            enabled: false
                        },
                        stroke: {
                            curve: 'smooth',
                            width: 2
                        },
                        series: [{
                            name: 'Revenue',
                            data: [31, 40, 28, 51, 42, 109, 100, 120, 80, 42, 90, 140]
                        }],
                        xaxis: {
                            categories: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                        },
                        colors: ['#5156be']
                    }
                    var chart = new ApexCharts(
                        document.querySelector("#monthly_income"),
                        options
                    );
                    chart.render();
                }

                if ($("#customers").length) {
                    var options = {
                        chart: {
                            height: 280,
                            type: 'donut',
                        },
                        series: [40, 35, 25],
                        labels: ["Regular", "VIP", "VVIP"],
                        colors: ["#5c9ce4", "#ff9f43", "#e54446"],
                        legend: {
                            show: false
                        },
                        dataLabels: {
                            enabled: false
                        }
                    }
                    var chart = new ApexCharts(
                        document.querySelector("#customers"),
                        options
                    );
                    chart.render();
                }
            });
        </script>

</body>
<!--end body-->

</html>
