<div class="m-menu__overlay"></div>
<!-- main menu -->
<div class="m-menu">
    <div class="m-menu__header">
        <button class="m-menu__close">
            <img src="{{ asset('Travgo/preview/assets/svg/menu/close-white.svg') }}" alt="icon">
        </button>
        <div class="menu-user">
            <img src="{{ asset('Travgo/preview/assets/images/profile/avatar.png') }}" alt="avatar">
            <div>
                <a href="#!">{{ Auth::user()->name }}</a>
                <h3>
                    Customer · {{ Auth::user()->customer->category ?? 'Regular' }}
                </h3>
            </div>
        </div>
    </div>
    <ul>
        <li>
            <h2 class="menu-title">menu</h2>
        </li>

        <li>
            <a href="{{ route('customer.dashboard') }}">
                <div class="d-flex align-items-center gap-16">
                    <span class="icon">
                        <img src="{{ asset('Travgo/preview/assets/svg/menu/pie-white.svg') }}" alt="">
                    </span>
                    Dashboard
                </div>
                <img src="{{ asset('Travgo/preview/assets/svg/menu/chevron-right-black.svg') }}" alt="">
            </a>
        </li>

        <li>
            <a href="{{ route('customer.reservation.index') }}">
                <div class="d-flex align-items-center gap-16">
                    <span class="icon">
                        <img src="{{ asset('Travgo/preview/assets/svg/menu/page-white.svg') }}" alt="">
                    </span>
                    Reservasi
                </div>
                <img src="{{ asset('Travgo/preview/assets/svg/menu/chevron-right-black.svg') }}" alt="">
            </a>
        </li>

        <li>
            <a href="{{ route('customer.reservation.history') }}" onclick="console.log('History link clicked')">
                <div class="d-flex align-items-center gap-16">
                    <span class="icon">
                        <img src="{{ asset('Travgo/preview/assets/svg/menu/history-white.svg') }}" alt="">
                    </span>
                    Riwayat Reservasi
                </div>
                <img src="{{ asset('Travgo/preview/assets/svg/menu/chevron-right-black.svg') }}" alt="">
            </a>
        </li>

        <li>
            <a href="{{ route('customer.transaction.index') }}">
                <div class="d-flex align-items-center gap-16">
                    <span class="icon">
                        <img src="{{ asset('Travgo/preview/assets/svg/menu/grid-white.svg') }}" alt="">
                    </span>
                    Riwayat Transaksi
                </div>
                <img src="{{ asset('Travgo/preview/assets/svg/menu/chevron-right-black.svg') }}" alt="">
            </a>
        </li>

        <li>
            <a href="{{ route('customer.food-beverages.index') }}">
                <div class="d-flex align-items-center gap-16">
                    <span class="icon">
                        <img src="{{ asset('Travgo/preview/assets/svg/menu/cup-white.svg') }}" alt="">
                    </span>
                    Menu F&B
                </div>
                <img src="{{ asset('Travgo/preview/assets/svg/menu/chevron-right-black.svg') }}" alt="">
            </a>
        </li>

        <li>
            <h2 class="menu-title">account</h2>
        </li>

        <li>
            <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                <div class="d-flex align-items-center gap-16">
                    <span class="icon">
                        <img src="{{ asset('Travgo/preview/assets/svg/menu/box-white.svg') }}" alt="icon">
                    </span>
                    Logout
                </div>
            </a>
            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                @csrf
            </form>
        </li>
    </ul>
</div>
<!-- end main menu -->
