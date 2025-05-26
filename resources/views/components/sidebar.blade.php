<!-- leftbar-tab-menu -->
<div class="startbar d-print-none">
    <!--start brand-->
    <div class="brand">
        <a href="/" class="logo">
            <span>
                <img src="{{ asset('dist/assets/images/logo-sm.png') }}" alt="logo-small" class="logo-sm">
            </span>
            <span class="">
                <img src="{{ asset('dist/assets/images/logo-light.png') }}" alt="logo-large" class="logo-lg logo-light">
                <img src="{{ asset('dist/assets/images/logo-dark.png') }}" alt="logo-large" class="logo-lg logo-dark">
            </span>
        </a>
    </div>
    <!--end brand-->
    <!--start startbar-menu-->
    <div class="startbar-menu">
        <div class="startbar-collapse" id="startbarCollapse" data-simplebar>
            <div class="d-flex align-items-start flex-column w-100">
                <!-- Navigation -->
                <ul class="navbar-nav mb-auto w-100">
                    <li class="menu-label mt-2">
                        <span>Navigation</span>
                    </li>

                    <!-- Role-specific Dashboards -->
                    @if(auth()->user()->role === 'super_admin')
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('admin.dashboard.super') }}">
                            <i class="iconoir-stats-up-square menu-icon"></i>
                            <span>Super Admin Dashboard</span>
                        </a>
                    </li><!--end nav-item-->
                    @endif

                    @if(auth()->user()->role === 'owner')
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('admin.dashboard.owner') }}">
                            <i class="iconoir-building menu-icon"></i>
                            <span>Owner Dashboard</span>
                        </a>
                    </li><!--end nav-item-->
                    @endif

                    @if(auth()->user()->role === 'admin_pool')
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('admin.dashboard.admin_pool') }}">
                            <i class="iconoir-gym menu-icon"></i>
                            <span>Pool Admin Dashboard</span>
                        </a>
                    </li><!--end nav-item-->
                    @endif

                    <!-- Master Data -->
                    @if(auth()->user()->role !== 'owner')
                    <li class="nav-item">
                        <a class="nav-link" href="#sidebarMasterData" data-bs-toggle="collapse" role="button"
                            aria-expanded="false" aria-controls="sidebarMasterData">
                            <i class="iconoir-reports menu-icon"></i>
                            <span>Master Data</span>
                        </a>
                        <div class="collapse" id="sidebarMasterData">
                            <ul class="nav flex-column">
                                @if(auth()->user()->role === 'super_admin')
                                <li class="nav-item">
                                    <a href="/admin/rooms" class="nav-link">Master Ruangan</a>
                                </li><!--end nav-item-->
                                <li class="nav-item">
                                    <a href="/admin/billiard-tables" class="nav-link">Master Meja</a>
                                </li><!--end nav-item-->
                                <li class="nav-item">
                                    <a href="/admin/prices" class="nav-link">Master Harga</a>
                                </li><!--end nav-item-->
                                {{-- <li class="nav-item">
                                    <a href="/admin/promos" class="nav-link">Master Promo</a>
                                </li><!--end nav-item--> --}}
                                @endif
                                <li class="nav-item">
                                    <a href="/admin/food-beverages" class="nav-link">Menu F&B</a>
                                </li><!--end nav-item-->
                            </ul><!--end nav-->
                        </div>
                    </li><!--end nav-item-->
                    @endif

                    <!-- Transaction -->
                    <li class="nav-item">
                        <a class="nav-link" href="#sidebarTransaction" data-bs-toggle="collapse" role="button"
                            aria-expanded="false" aria-controls="sidebarTransaction">
                            <i class="iconoir-cart-alt menu-icon"></i>
                            <span>Transaksi</span>
                        </a>
                        <div class="collapse" id="sidebarTransaction">
                            <ul class="nav flex-column">
                                <li class="nav-item">
                                    <a class="nav-link" href="/admin/transactions">Transaksi</a>
                                </li><!--end nav-item-->
                                <li class="nav-item">
                                    <a class="nav-link" href="/admin/walkin">Walk-In</a>
                                </li><!--end nav-item-->
                                <li class="nav-item">
                                    <a class="nav-link" href="/admin/reservations">Reservasi</a>
                                </li><!--end nav-item-->
                            </ul><!--end nav-->
                        </div>
                    </li><!--end nav-item-->

                    <!-- User Management -->
                    @if(auth()->user()->role === 'super_admin')
                    <li class="nav-item">
                        <a class="nav-link" href="#sidebarUsers" data-bs-toggle="collapse" role="button"
                            aria-expanded="false" aria-controls="sidebarUsers">
                            <i class="iconoir-cube-hole menu-icon"></i>
                            <span>User Management</span>
                        </a>
                        <div class="collapse" id="sidebarUsers">
                            <ul class="nav flex-column">
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('admin.users.index') }}">User Account</a>
                                </li><!--end nav-item-->
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('admin.customers.index') }}">Customer</a>
                                </li><!--end nav-item-->
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('admin.profile.index') }}">Admin Profile</a>
                                </li><!--end nav-item-->
                            </ul><!--end nav-->
                        </div>
                    </li><!--end nav-item-->
                    @endif

                    <!-- Notifications -->
                    @if(auth()->user()->role !== 'owner')
                    @php
                        $unreadNotifCount = 0;
                        $reservationNotifCount = 0;
                        if (auth()->check() && (auth()->user()->role === 'super_admin' || auth()->user()->role === 'admin_pool')) {
                            $unreadNotifCount = \App\Models\Notification::where('status', 'unread')->count();
                            $reservationNotifCount = \App\Models\Notification::where('type', 'reservation')->count();
                        }
                    @endphp
                    <li class="nav-item">
                        <a class="nav-link" href="#sidebarNotifications" data-bs-toggle="collapse" role="button"
                            aria-expanded="false" aria-controls="sidebarNotifications">
                            <i class="iconoir-notification-bell menu-icon"></i>
                            <span>Notifications</span>
                            @if($unreadNotifCount > 0)
                                <span class="badge bg-danger rounded-circle ms-auto">{{ $unreadNotifCount }}</span>
                            @endif
                        </a>
                        <div class="collapse" id="sidebarNotifications">
                            <ul class="nav flex-column">
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('admin.notifications.index') }}">
                                        All Notifications
                                        @if($unreadNotifCount > 0)
                                            <span class="badge bg-danger float-end">{{ $unreadNotifCount }}</span>
                                        @endif
                                    </a>
                                </li><!--end nav-item-->
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('admin.notifications.reservations') }}">
                                        Reservation Notifications
                                        @if($reservationNotifCount > 0)
                                            <span class="badge bg-info float-end">{{ $reservationNotifCount }}</span>
                                        @endif
                                    </a>
                                </li><!--end nav-item-->
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('admin.notifications.create') }}">Send Manual Notification</a>
                                </li><!--end nav-item-->
                            </ul><!--end nav-->
                        </div>
                    </li><!--end nav-item-->
                    @endif

                    <!-- Reports -->
                    <li class="nav-item">
                        <a class="nav-link" href="#sidebarReports" data-bs-toggle="collapse" role="button"
                            aria-expanded="false" aria-controls="sidebarReports">
                            <i class="iconoir-document-report menu-icon"></i>
                            <span>Reports</span>
                        </a>
                        <div class="collapse" id="sidebarReports">
                            <ul class="nav flex-column">
                                <li class="nav-item">
                                    <a class="nav-link" href="/admin/reports/sales">Sales Report</a>
                                </li><!--end nav-item-->
                                <li class="nav-item">
                                    <a class="nav-link" href="/admin/reports/tables">Table Usage</a>
                                </li><!--end nav-item-->
                                <li class="nav-item">
                                    <a class="nav-link" href="/admin/reports/customers">Customer Report</a>
                                </li><!--end nav-item-->
                            </ul><!--end nav-->
                        </div>
                    </li><!--end nav-item-->



                    <!-- Developer Tools -->
                    @if(auth()->user()->role === 'super_admin')
                    <li class="menu-label mt-2">
                        <span>Developer Tools</span>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('admin.socket-test') }}">
                            <i class="iconoir-network menu-icon"></i>
                            <span>Socket.IO Test</span>
                        </a>
                    </li><!--end nav-item-->
                    @endif

                    <!-- Logout -->
                    <li class="menu-label mt-2">
                        <span>Account</span>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="javascript:void(0);" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                            <i class="iconoir-logout menu-icon"></i>
                            <span>Logout</span>
                        </a>
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                            @csrf
                        </form>
                    </li><!--end nav-item-->
                </ul><!--end navbar-nav--->
            </div>
        </div><!--end startbar-collapse-->
    </div><!--end startbar-menu-->
</div><!--end startbar-->
<div class="startbar-overlay d-print-none"></div>
<!-- end leftbar-tab-menu-->
