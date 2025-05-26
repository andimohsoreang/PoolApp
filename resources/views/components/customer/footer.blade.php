<!-- bottom navigation start -->
<footer class="bottom-nav">
    <ul class="d-flex align-items-center justify-content-around w-100 h-100">
        <li>
            <a href="{{ route('customer.dashboard') }}">
                <img src="{{ asset('Travgo/preview/assets/svg/bottom-nav/home-active.svg') }}" alt="home">
            </a>
        </li>
        <li>
            <a href="{{ route('customer.book') }}">
                <img src="{{ asset('Travgo/preview/assets/svg/bottom-nav/category.svg') }}" alt="category">
            </a>
        </li>
        <li>
            <a href="{{ route('customer.transactions') }}">
                <img src="{{ asset('Travgo/preview/assets/svg/bottom-nav/ticket.svg') }}" alt="ticket">
            </a>
        </li>
        <li>
            <a href="#">
                <img src="{{ asset('Travgo/preview/assets/svg/bottom-nav/heart.svg') }}" alt="heart">
            </a>
        </li>
        <li>
            <a href="#">
                <img src="{{ asset('Travgo/preview/assets/svg/bottom-nav/profile.svg') }}" alt="profile">
            </a>
        </li>
    </ul>
</footer>
<!-- bottom navigation end -->
