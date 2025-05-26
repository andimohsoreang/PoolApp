<!-- Top Bar Start -->
<div class="topbar d-print-none">
    <div class="container-fluid">
        <nav class="topbar-custom d-flex justify-content-between" id="topbar-custom">

            <ul class="topbar-item list-unstyled d-inline-flex align-items-center mb-0">
                <li>
                    <button class="nav-link mobile-menu-btn nav-icon" id="togglemenu">
                        <i class="iconoir-menu"></i>
                    </button>
                </li>
                <li class="mx-2 welcome-text">
                    <h5 class="mb-0 fw-semibold text-truncate">Pool Billiard System - Admin Panel</h5>
                </li>
            </ul>

            <ul class="topbar-item list-unstyled d-inline-flex align-items-center mb-0">
                <li class="topbar-item">
                    <a class="nav-link nav-icon" href="javascript:void(0);" id="light-dark-mode">
                        <i class="iconoir-half-moon dark-mode"></i>
                        <i class="iconoir-sun-light light-mode"></i>
                    </a>
                </li>

                <li class="dropdown">
                    @php
                        // Get unread notification count
                        $unreadCount = 0;
                        if (auth()->check() && (auth()->user()->role === 'super_admin' || auth()->user()->role === 'owner' || auth()->user()->role === 'admin_pool')) {
                            $unreadCount = \App\Models\Notification::where('status', 'unread')->count();
                        }

                        // Get latest notifications
                        $latestNotifications = [];
                        if (auth()->check() && (auth()->user()->role === 'super_admin' || auth()->user()->role === 'owner' || auth()->user()->role === 'admin_pool')) {
                            $latestNotifications = \App\Models\Notification::with(['user', 'reservation' => function($query) {
                                    $query->withTrashed(); // Include soft deleted reservations
                                }])
                                ->orderBy('created_at', 'desc')
                                ->limit(3)
                                ->get();
                        }
                    @endphp
                    <a class="nav-link dropdown-toggle arrow-none nav-icon" data-bs-toggle="dropdown" href="#"
                        role="button" aria-haspopup="false" aria-expanded="false">
                        <i class="iconoir-notification-box"></i>
                        @if($unreadCount > 0)
                            <span class="notification-icon bg-danger position-absolute"></span>
                        @endif
                    </a>
                    <div class="dropdown-menu dropdown-menu-end" style="min-width: 300px;">
                        <h6 class="dropdown-item-text fw-semibold py-2 bg-light">
                            Notifications {{ $unreadCount > 0 ? "($unreadCount)" : "" }}
                        </h6>
                        <div class="notification-menu" data-simplebar style="max-height: 300px;">
                            @if(count($latestNotifications) > 0)
                                @foreach($latestNotifications as $notification)
                                    <a href="{{ route('admin.notifications.show', $notification->id) }}" class="dropdown-item py-3 {{ $notification->status === 'unread' ? 'bg-light' : '' }}">
                                        <small class="float-end text-muted ps-2">{{ $notification->created_at->diffForHumans() }}</small>
                                        <div class="media">
                                            <div class="avatar-md bg-{{ $notification->type === 'reservation' ? 'info' : ($notification->type === 'payment' ? 'success' : 'primary') }}-subtle">
                                                <i class="iconoir-{{ $notification->type === 'reservation' ? 'calendar' : ($notification->type === 'payment' ? 'wallet' : 'bell') }} font-22 text-{{ $notification->type === 'reservation' ? 'info' : ($notification->type === 'payment' ? 'success' : 'primary') }}"></i>
                                            </div>
                                            <div class="media-body align-self-center ms-2 text-truncate">
                                                <h6 class="my-0 fw-semibold text-dark">{{ ucfirst($notification->type) }}</h6>
                                                <small class="text-muted mb-0">{{ Str::limit($notification->message, 40) }}</small>
                                            </div>
                                        </div>
                                    </a>
                                @endforeach
                            @else
                                <div class="dropdown-item py-3 text-center">
                                    <span class="text-muted">No notifications available</span>
                                </div>
                            @endif
                        </div>
                        <a href="{{ route('admin.notifications.index') }}" class="dropdown-item text-center text-primary bg-light py-2">
                            View All <i class="iconoir-arrow-right"></i>
                        </a>
                    </div>
                </li>

                <li class="dropdown topbar-item">
                    <a class="nav-link dropdown-toggle arrow-none nav-icon" data-bs-toggle="dropdown" href="#"
                        role="button" aria-haspopup="false" aria-expanded="false" data-bs-offset="0,19">
                        <img src="{{ asset('dist/assets/images/users/avatar-1.jpg') }}" alt=""
                            class="thumb-md rounded-circle">
                    </a>
                    <div class="dropdown-menu dropdown-menu-end py-0">
                        <div class="d-flex align-items-center dropdown-item py-2 bg-secondary-subtle">
                            <div class="flex-shrink-0">
                                <img src="{{ asset('dist/assets/images/users/avatar-1.jpg') }}" alt=""
                                    class="thumb-md rounded-circle">
                            </div>
                            <div class="flex-grow-1 ms-2 text-truncate align-self-center">
                                <h6 class="my-0 fw-medium text-dark fs-13">Admin User</h6>
                                <small class="text-muted mb-0">administrator</small>
                            </div><!--end media-body-->
                        </div>
                        <div class="dropdown-divider mt-0"></div>
                        <a class="dropdown-item" href=""><i
                                class="las la-user fs-18 me-1 align-text-bottom"></i> Profile</a>
                        <a class="dropdown-item" href=""><i
                                class="las la-cog fs-18 me-1 align-text-bottom"></i> Settings</a>
                        <div class="dropdown-divider mb-0"></div>
                        <a class="dropdown-item text-danger" href="javascript:void(0);" onclick="event.preventDefault(); document.getElementById('topbar-logout-form').submit();">
                            <i class="las la-power-off fs-18 me-1 align-text-bottom"></i> Logout
                        </a>
                        <form id="topbar-logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                            @csrf
                        </form>
                    </div>
                </li>
            </ul><!--end topbar-nav-->
        </nav>
        <!-- end navbar-->
    </div>
</div>
<!-- Top Bar End -->
