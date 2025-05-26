<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>@yield('title') - Pool Billiard System</title>

    <!-- favicon -->
    <link rel="shortcut icon" href="{{ asset('Travgo/preview/assets/images/favicon.png') }}" type="image/x-icon">

    <!-- bootstrap -->
    <link rel="stylesheet" href="{{ asset('Travgo/preview/assets/css/bootstrap.min.css') }}">

    <!-- swiper -->
    <link rel="stylesheet" href="{{ asset('Travgo/preview/assets/css/swiper-bundle.min.css') }}">

    <!-- datepicker -->
    <link rel="stylesheet" href="{{ asset('Travgo/preview/assets/css/jquery.datetimepicker.css') }}">

    <!-- jquery ui -->
    <link rel="stylesheet" href="{{ asset('Travgo/preview/assets/css/jquery-ui.min.css') }}">

    <!-- common -->
    <link rel="stylesheet" href="{{ asset('Travgo/preview/assets/css/common.css') }}">

    <!-- animations -->
    <link rel="stylesheet" href="{{ asset('Travgo/preview/assets/css/animations.css') }}">

    <!-- welcome -->
    <link rel="stylesheet" href="{{ asset('Travgo/preview/assets/css/welcome.css') }}">

    <!-- food-beverages -->
    <link rel="stylesheet" href="{{ asset('css/food-beverages.css') }}">

    <!-- Custom styles -->
    @yield('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.css" />
</head>

<body class="scrollbar-hidden">
    <!-- Splash Screen -->
    @include('components.customer.splash-screen')

    <main class="home">
        <!-- menu, side-menu start -->
        <section class="wrapper dz-mode">
            <!-- Topbar -->
            @include('components.customer.topbar')

            <!-- Sidebar -->
            @include('components.customer.sidebar')
        </section>
        <!-- menu, side-menu end -->

        <!-- Content -->
        @yield('content')
    </main>

    <!-- Footer -->
    @include('components.customer.footer')

    <!-- jquery -->
    <script src="{{ asset('Travgo/preview/assets/js/jquery-3.6.1.min.js') }}"></script>

    <!-- bootstrap -->
    <script src="{{ asset('Travgo/preview/assets/js/bootstrap.bundle.min.js') }}"></script>

    <!-- jquery ui -->
    <script src="{{ asset('Travgo/preview/assets/js/jquery-ui.js') }}"></script>

    <!-- mixitup -->
    <script src="{{ asset('Travgo/preview/assets/js/mixitup.min.js') }}"></script>

    <!-- gasp -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.3/gsap.min.js"></script>

    <!-- draggable -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.3/Draggable.min.js"></script>

    <!-- swiper -->
    <script src="{{ asset('Travgo/preview/assets/js/swiper-bundle.min.js') }}"></script>

    <!-- datepicker -->
    <script src="{{ asset('Travgo/preview/assets/js/jquery.datetimepicker.full.js') }}"></script>

    <!-- script -->
    <script src="{{ asset('Travgo/preview/assets/js/script.js') }}"></script>

    <!-- Custom scripts -->
    @yield('scripts')
    @stack('scripts')
    <script src="https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.js"></script>
</body>

</html>
