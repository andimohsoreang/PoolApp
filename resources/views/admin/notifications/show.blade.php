@extends('layouts.app')

@section('title', 'Notification Details')

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
                        <li class="breadcrumb-item active">Details</li>
                    </ol>
                </div>
                <h4 class="page-title">Notification Details</h4>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-10 mx-auto">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <span class="badge bg-{{ $notification->type === 'reservation' ? 'info' : ($notification->type === 'payment' ? 'success' : 'secondary') }} me-2">
                            {{ ucfirst($notification->type) }}
                        </span>
                        @if($notification->is_manual)
                            <span class="badge bg-warning">Manual</span>
                        @endif
                    </h5>
                    <small class="text-muted">{{ $notification->created_at->format('d M Y, H:i') }}</small>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <h5 class="card-subtitle mb-2 text-muted">Message</h5>
                        <p class="card-text">{{ $notification->message }}</p>
                    </div>

                    @if($notification->user)
                    <div class="mb-4">
                        <h5 class="card-subtitle mb-2 text-muted">Recipient</h5>
                        <div class="d-flex align-items-center">
                            <div class="avatar-sm me-3">
                                <div class="avatar-title bg-soft-primary text-primary rounded">
                                    <i class="iconoir-user font-18"></i>
                                </div>
                            </div>
                            <div>
                                <h6 class="mb-1">{{ $notification->user->name }}</h6>
                                <p class="text-muted mb-0 font-13">{{ $notification->user->email }}</p>
                            </div>
                        </div>
                    </div>
                    @endif

                    @if($notification->reservation)
                    <div class="mb-4">
                        <h5 class="card-subtitle mb-2 text-muted">Related Reservation</h5>
                        <div class="d-flex align-items-center">
                            <div class="avatar-sm me-3">
                                <div class="avatar-title bg-soft-info text-info rounded">
                                    <i class="iconoir-calendar font-18"></i>
                                </div>
                            </div>
                            <div>
                                <h6 class="mb-1">
                                    <a href="{{ route('admin.reservations.show', $notification->reservation->id) }}" class="text-primary">
                                        #{{ $notification->reservation->reservation_code }}
                                    </a>
                                </h6>
                                <p class="text-muted mb-0 font-13">
                                    Status:
                                    <span class="badge bg-{{
                                        $notification->reservation->status == 'pending' ? 'warning' :
                                        ($notification->reservation->status == 'approved' ? 'info' :
                                        ($notification->reservation->status == 'confirmed' ? 'success' :
                                        ($notification->reservation->status == 'cancelled' ? 'danger' : 'secondary')))
                                    }}">
                                        {{ ucfirst($notification->reservation->status) }}
                                    </span>
                                </p>
                            </div>
                        </div>
                    </div>
                    @endif

                    @if($notification->transaction)
                    <div class="mb-4">
                        <h5 class="card-subtitle mb-2 text-muted">Related Transaction</h5>
                        <div class="d-flex align-items-center">
                            <div class="avatar-sm me-3">
                                <div class="avatar-title bg-soft-success text-success rounded">
                                    <i class="iconoir-receipt font-18"></i>
                                </div>
                            </div>
                            <div>
                                <h6 class="mb-1">
                                    <a href="{{ route('admin.transactions.show', $notification->transaction->id) }}" class="text-primary">
                                        #{{ $notification->transaction->transaction_code }}
                                    </a>
                                </h6>
                                <p class="text-muted mb-0 font-13">
                                    Amount: Rp {{ number_format($notification->transaction->total_price, 0, ',', '.') }}
                                </p>
                            </div>
                        </div>
                    </div>
                    @endif

                    <div class="d-flex justify-content-between mt-4">
                        <div>
                            <span class="text-muted me-3">
                                <i class="iconoir-eye{{ $notification->status === 'read' ? '' : '-closed' }} me-1"></i>
                                {{ $notification->status === 'read' ? 'Read ' . $notification->read_at->diffForHumans() : 'Unread' }}
                            </span>
                        </div>
                        <div>
                            @if($notification->status === 'unread')
                                <form action="{{ route('admin.notifications.mark-read', $notification->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-primary me-1">
                                        <i class="iconoir-check me-1"></i> Mark as Read
                                    </button>
                                </form>
                            @else
                                <form action="{{ route('admin.notifications.mark-unread', $notification->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-secondary me-1">
                                        <i class="iconoir-eye-closed me-1"></i> Mark as Unread
                                    </button>
                                </form>
                            @endif

                            @if($notification->is_manual)
                                <a href="{{ route('admin.notifications.edit', $notification->id) }}" class="btn btn-sm btn-outline-info me-1">
                                    <i class="iconoir-edit-pencil me-1"></i> Edit
                                </a>
                            @endif

                            <form action="{{ route('admin.notifications.destroy', $notification->id) }}" method="POST" class="d-inline delete-form">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                    <i class="iconoir-trash me-1"></i> Delete
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <a href="{{ route('admin.notifications.index') }}" class="btn btn-outline-secondary">
                        <i class="iconoir-arrow-left me-1"></i> Back to Notifications
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // Confirm delete
        $('.delete-form').on('submit', function(e) {
            e.preventDefault();
            if (confirm('Are you sure you want to delete this notification?')) {
                this.submit();
            }
        });
    });
</script>
@endpush