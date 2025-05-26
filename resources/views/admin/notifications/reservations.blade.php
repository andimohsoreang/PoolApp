@extends('layouts.app')

@section('title', 'Reservation Notifications')

@push('styles')
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap5.min.css">
<style>
    .notification-item {
        border-left: 4px solid transparent;
        transition: all 0.2s;
    }
    .notification-item:hover {
        background-color: rgba(0, 0, 0, 0.03);
    }
    .notification-item.unread {
        border-left-color: #4e73df;
        background-color: rgba(78, 115, 223, 0.05);
    }
    .notification-item.unread:hover {
        background-color: rgba(78, 115, 223, 0.1);
    }
    .notification-type {
        font-size: 0.75rem;
        padding: 0.25rem 0.5rem;
        border-radius: 50rem;
    }
    .badge-count {
        display: inline-flex;
        justify-content: center;
        align-items: center;
        min-width: 20px;
        height: 20px;
        padding: 0 6px;
        font-size: 0.7rem;
        font-weight: 600;
        border-radius: 10px;
    }
    /* Animation untuk notification baru */
    @keyframes highlightNew {
        0% { background-color: rgba(78, 115, 223, 0.2); }
        100% { background-color: rgba(78, 115, 223, 0.05); }
    }
    .notification-item.highlight-new {
        animation: highlightNew 3s;
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
                        <li class="breadcrumb-item"><a href="{{ route('admin.notifications.index') }}">Notifications</a></li>
                        <li class="breadcrumb-item active">Reservations</li>
                    </ol>
                </div>
                <h4 class="page-title">Reservation Notifications</h4>
            </div>
        </div>
    </div>

    <!-- Notification Management -->
    <div class="row">
        <!-- Sidebar -->
        <div class="col-xl-3 col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Filter Notifications</h5>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        <a href="{{ route('admin.notifications.index') }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            <span><i class="iconoir-bell me-2"></i> All Notifications</span>
                            <span class="badge bg-primary badge-count">{{ $notifications->total() }}</span>
                        </a>
                        <a href="{{ route('admin.notifications.index', ['status' => 'unread']) }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            <span><i class="iconoir-eye-empty me-2"></i> Unread</span>
                            <span class="badge bg-danger badge-count">{{ $unreadCount }}</span>
                        </a>
                        <a href="{{ route('admin.notifications.reservations') }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center active">
                            <span><i class="iconoir-calendar me-2"></i> Reservations</span>
                            <span class="badge bg-info badge-count">{{ $reservationCount }}</span>
                        </a>
                        <a href="{{ route('admin.notifications.index', ['manual' => '1']) }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            <span><i class="iconoir-edit me-2"></i> Manual Notifications</span>
                            <span class="badge bg-warning badge-count">{{ $manualCount }}</span>
                        </a>
                    </div>

                    <div class="mt-4">
                        <a href="{{ route('admin.notifications.create') }}" class="btn btn-primary w-100">
                            <i class="iconoir-plus me-1"></i> New Manual Notification
                        </a>
                    </div>

                    @if($unreadCount > 0)
                    <div class="mt-3">
                        <form action="{{ route('admin.notifications.mark-all-read') }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-outline-secondary w-100">
                                <i class="iconoir-check-circle me-1"></i> Mark All as Read
                            </button>
                        </form>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Send Reservation Notification -->
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Send Reservation Notification</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.notifications.send-reservation') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="reservation_id" class="form-label">Reservation</label>
                            <select name="reservation_id" id="reservation_id" class="form-select" required>
                                <option value="">Select a reservation</option>
                                @php
                                    $reservations = \App\Models\Reservation::with('customer')
                                        ->orderBy('created_at', 'desc')
                                        ->where('status', 'pending')
                                        ->orWhere('status', 'approved')
                                        ->orWhere('status', 'confirmed')
                                        ->limit(10)
                                        ->get();
                                @endphp
                                @foreach($reservations as $reservation)
                                    <option value="{{ $reservation->id }}">
                                        #{{ $reservation->reservation_code }} - {{ $reservation->customer->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="message" class="form-label">Message</label>
                            <textarea name="message" id="message" class="form-control" rows="3" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="iconoir-send me-1"></i> Send Notification
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="col-xl-9 col-lg-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Reservation Notifications</h5>
                    <div>
                        <form action="{{ route('admin.notifications.reservations') }}" method="GET" class="d-flex">
                            <select name="status" class="form-select form-select-sm me-2" style="width: 140px;">
                                <option value="">All Status</option>
                                <option value="read" {{ request()->status == 'read' ? 'selected' : '' }}>Read</option>
                                <option value="unread" {{ request()->status == 'unread' ? 'selected' : '' }}>Unread</option>
                            </select>
                            <button type="submit" class="btn btn-sm btn-outline-primary">Filter</button>
                        </form>
                    </div>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    @if(count($notifications) > 0)
                        <div class="list-group" id="notifications-list">
                            @foreach($notifications as $notification)
                                <div class="list-group-item notification-item {{ $notification->status === 'unread' ? 'unread' : '' }}" data-id="{{ $notification->id }}">
                                    <div class="d-flex w-100 justify-content-between align-items-center mb-2">
                                        <div>
                                            <span class="notification-type bg-info text-white">
                                                Reservation
                                            </span>
                                            @if($notification->is_manual)
                                                <span class="badge bg-warning ms-1">Manual</span>
                                            @endif
                                        </div>
                                        <small class="text-muted">{{ $notification->created_at->diffForHumans() }}</small>
                                    </div>
                                    <div class="d-flex w-100">
                                        <div class="flex-grow-1">
                                            <a href="{{ route('admin.notifications.show', $notification->id) }}" class="text-decoration-none text-dark">
                                                <h6 class="mb-1">{{ Str::limit($notification->message, 100) }}</h6>
                                            </a>
                                            @if($notification->user)
                                                <small class="text-muted">To: {{ $notification->user->name }}</small>
                                            @endif
                                        </div>
                                        <div class="ms-3 d-flex">
                                            @if($notification->status === 'unread')
                                                <form action="{{ route('admin.notifications.mark-read', $notification->id) }}" method="POST" class="me-1">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-outline-primary" title="Mark as read">
                                                        <i class="iconoir-check"></i>
                                                    </button>
                                                </form>
                                            @else
                                                <form action="{{ route('admin.notifications.mark-unread', $notification->id) }}" method="POST" class="me-1">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-outline-secondary" title="Mark as unread">
                                                        <i class="iconoir-eye-closed"></i>
                                                    </button>
                                                </form>
                                            @endif

                                            @if($notification->is_manual)
                                                <a href="{{ route('admin.notifications.edit', $notification->id) }}" class="btn btn-sm btn-outline-info me-1" title="Edit">
                                                    <i class="iconoir-edit-pencil"></i>
                                                </a>
                                            @endif

                                            <form action="{{ route('admin.notifications.destroy', $notification->id) }}" method="POST" class="delete-form">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                                    <i class="iconoir-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                    @if($notification->reservation)
                                        <div class="mt-2">
                                            <small class="text-muted">
                                                Reservation: <a href="{{ route('admin.reservations.show', $notification->reservation->id) }}" class="text-primary">
                                                    #{{ $notification->reservation->reservation_code }}
                                                </a>
                                                @if($notification->reservation->status)
                                                    <span class="badge bg-{{
                                                        $notification->reservation->status == 'pending' ? 'warning' :
                                                        ($notification->reservation->status == 'approved' ? 'info' :
                                                        ($notification->reservation->status == 'confirmed' ? 'success' :
                                                        ($notification->reservation->status == 'cancelled' ? 'danger' : 'secondary')))
                                                    }}">
                                                        {{ ucfirst($notification->reservation->status) }}
                                                    </span>
                                                @endif
                                            </small>
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>

                        <div class="mt-4 d-flex justify-content-center">
                            {{ $notifications->appends(request()->except('page'))->links() }}
                        </div>
                    @else
                        <div class="text-center p-4" id="empty-notification-message">
                            <div class="avatar-lg mx-auto mb-3">
                                <div class="avatar-title bg-light text-primary rounded-circle">
                                    <i class="iconoir-inbox-empty display-6"></i>
                                </div>
                            </div>
                            <h5>No reservation notifications found</h5>
                            <p class="text-muted">There are no reservation notifications matching your criteria.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script type="text/javascript" src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/responsive/2.2.9/js/responsive.bootstrap5.min.js"></script>

<script>
    $(document).ready(function() {
        // Confirm delete
        $('.delete-form').on('submit', function(e) {
            e.preventDefault();
            if (confirm('Are you sure you want to delete this notification?')) {
                this.submit();
            }
        });

        // Socket.io realtime listener
        if (typeof io !== 'undefined') {
            console.log('Setting up real-time notification listener for reservation notifications page');
            
            // Create our own listener for this page specifically
            const socket = io(window.location.protocol + '//' + window.location.hostname + ':6002', {
                transports: ['websocket', 'polling'],
                reconnection: true,
                withCredentials: true,
                extraHeaders: {
                    "Access-Control-Allow-Origin": "*"
                }
            });

            socket.on('connect', function() {
                console.log('Reservation notifications page connected to Socket.IO');
                socket.emit('subscribe', 'admin-notifications');
            });

            socket.on('new-reservation', function(data) {
                console.log('New reservation notification received in reservations page:', data);
                
                // Add new notification to the top of the list if it's a reservation type
                if (data.type === 'reservation') {
                    addNotificationToList(data);
                }
                
                // Update counters
                updateCounters();
            });

            socket.on('disconnect', function() {
                console.log('Disconnected from notification socket on reservations page');
            });

            socket.on('error', function(error) {
                console.error('Socket error on reservations page:', error);
            });
            
            socket.on('connect_error', function(error) {
                console.error('Connection error on reservations page:', error);
            });
        }

        // Function to add a new notification to the list
        function addNotificationToList(data) {
            // Check if the notification list container exists
            const notificationsContainer = $('#notifications-list');
            
            if (!notificationsContainer.length) {
                // Create the container if it doesn't exist (no notifications before)
                const cardBody = $('.card-body');
                const emptyMessage = $('#empty-notification-message');
                
                if (emptyMessage.length) {
                    emptyMessage.remove();
                    cardBody.append('<div class="list-group" id="notifications-list"></div>');
                    notificationsContainer = $('#notifications-list');
                }
            }
            
            // Get CSRF token
            const csrfToken = $('meta[name="csrf-token"]').attr('content');
            
            // Build reservation info if applicable
            const reservationInfo = data.reservation ? `
                <div class="mt-2">
                    <small class="text-muted">
                        Reservation: <a href="/admin/reservations/show/${data.reservation.id}" class="text-primary">
                            #${data.reservation.code}
                        </a>
                        <span class="badge bg-warning">
                            Pending
                        </span>
                    </small>
                </div>
            ` : '';
            
            // Create notification HTML
            const notificationHtml = `
                <div class="list-group-item notification-item unread highlight-new" data-id="${data.id}">
                    <div class="d-flex w-100 justify-content-between align-items-center mb-2">
                        <div>
                            <span class="notification-type bg-info text-white">
                                Reservation
                            </span>
                        </div>
                        <small class="text-muted">${data.created_at}</small>
                    </div>
                    <div class="d-flex w-100">
                        <div class="flex-grow-1">
                            <a href="/admin/notifications/${data.id}" class="text-decoration-none text-dark">
                                <h6 class="mb-1">${data.message}</h6>
                            </a>
                        </div>
                        <div class="ms-3 d-flex">
                            <form action="/admin/notifications/${data.id}/mark-read" method="POST" class="me-1">
                                <input type="hidden" name="_token" value="${csrfToken}">
                                <button type="submit" class="btn btn-sm btn-outline-primary" title="Mark as read">
                                    <i class="iconoir-check"></i>
                                </button>
                            </form>
                            <form action="/admin/notifications/${data.id}/destroy" method="POST" class="delete-form">
                                <input type="hidden" name="_token" value="${csrfToken}">
                                <input type="hidden" name="_method" value="DELETE">
                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                    <i class="iconoir-trash"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                    ${reservationInfo}
                </div>
            `;
            
            // Add to the top of the list
            notificationsContainer.prepend(notificationHtml);
            
            // Bind delete confirmation to the new form
            notificationsContainer.find('.delete-form').first().on('submit', function(e) {
                e.preventDefault();
                if (confirm('Are you sure you want to delete this notification?')) {
                    this.submit();
                }
            });
        }

        // Function to update notification counters
        function updateCounters() {
            fetch('/admin/notifications/count')
                .then(response => response.json())
                .then(data => {
                    // Update sidebar badges
                    $('.list-group-item:nth-child(1) .badge-count').text(data.total);
                    $('.list-group-item:nth-child(2) .badge-count').text(data.unread);
                    $('.list-group-item:nth-child(3) .badge-count').text(data.reservation);
                })
                .catch(error => console.error('Error fetching notification count:', error));
        }
    });
</script>
@endpush
