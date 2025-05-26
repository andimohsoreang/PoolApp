@extends('layouts.app')

@section('title', 'Food & Beverage Orders')

@section('content')
<div class="container-fluid">
    <!-- Page-Title -->
    <div class="row">
        <div class="col-sm-12">
            <div class="page-title-box">
                <div class="float-end">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.food-beverages.index') }}">Food & Beverages</a></li>
                        <li class="breadcrumb-item active">Orders</li>
                    </ol>
                </div>
                <h4 class="page-title">Food & Beverage Orders</h4>
            </div>
        </div>
    </div>

    <!-- Alert Messages -->
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

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="row align-items-center">
                        <div class="col">
                            <h4 class="card-title">Orders List</h4>
                            <p class="text-muted mb-0">View and manage all food & beverage orders</p>
                        </div>
                        <div class="col-auto">
                            <div class="dropdown">
                                <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" id="orderFilterDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fas fa-filter me-1"></i> Filter
                                </button>
                                <ul class="dropdown-menu" aria-labelledby="orderFilterDropdown">
                                    <li><a class="dropdown-item" href="{{ route('admin.food-beverages.orders') }}">All Orders</a></li>
                                    <li><a class="dropdown-item" href="{{ route('admin.food-beverages.orders', ['status' => 'pending']) }}">Pending</a></li>
                                    <li><a class="dropdown-item" href="{{ route('admin.food-beverages.orders', ['status' => 'processing']) }}">Processing</a></li>
                                    <li><a class="dropdown-item" href="{{ route('admin.food-beverages.orders', ['status' => 'completed']) }}">Completed</a></li>
                                    <li><a class="dropdown-item" href="{{ route('admin.food-beverages.orders', ['status' => 'cancelled']) }}">Cancelled</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    @if(isset($orders) && count($orders) > 0)
                        <div class="table-responsive">
                            <table class="table table-hover table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th>Order ID</th>
                                        <th>Customer</th>
                                        <th>Items</th>
                                        <th>Total</th>
                                        <th>Status</th>
                                        <th>Order Time</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($orders as $order)
                                        <tr>
                                            <td>#{{ $order->order_id }}</td>
                                            <td>{{ $order->customer_name }}</td>
                                            <td>
                                                @php
                                                    $orderDetails = json_decode($order->order_details, true);
                                                    $itemCount = count($orderDetails ?? []);
                                                @endphp
                                                {{ $itemCount }} item(s)
                                            </td>
                                            <td>Rp {{ number_format($order->total_amount, 0, ',', '.') }}</td>
                                            <td>
                                                @if($order->status == 'pending')
                                                    <span class="badge bg-warning">Pending</span>
                                                @elseif($order->status == 'processing')
                                                    <span class="badge bg-info">Processing</span>
                                                @elseif($order->status == 'completed')
                                                    <span class="badge bg-success">Completed</span>
                                                @elseif($order->status == 'cancelled')
                                                    <span class="badge bg-danger">Cancelled</span>
                                                @else
                                                    <span class="badge bg-secondary">{{ ucfirst($order->status) }}</span>
                                                @endif
                                            </td>
                                            <td>{{ \Carbon\Carbon::parse($order->created_at)->format('d M Y H:i') }}</td>
                                            <td>
                                                <div class="d-flex gap-2">
                                                    <button type="button" class="btn btn-sm btn-info view-order" data-bs-toggle="modal" data-bs-target="#orderDetailsModal" data-order-id="{{ $order->id }}">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    @if($order->status == 'pending')
                                                        <form action="{{ route('admin.food-beverages.orders.process', $order->id) }}" method="POST" class="d-inline">
                                                            @csrf
                                                            <button type="submit" class="btn btn-sm btn-primary">
                                                                <i class="fas fa-utensils"></i> Process
                                                            </button>
                                                        </form>
                                                    @endif
                                                    @if($order->status == 'processing')
                                                        <form action="{{ route('admin.food-beverages.orders.complete', $order->id) }}" method="POST" class="d-inline">
                                                            @csrf
                                                            <button type="submit" class="btn btn-sm btn-success">
                                                                <i class="fas fa-check"></i> Complete
                                                            </button>
                                                        </form>
                                                    @endif
                                                    @if($order->status == 'pending' || $order->status == 'processing')
                                                        <form action="{{ route('admin.food-beverages.orders.cancel', $order->id) }}" method="POST" class="d-inline">
                                                            @csrf
                                                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to cancel this order?')">
                                                                <i class="fas fa-times"></i> Cancel
                                                            </button>
                                                        </form>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="mt-4">
                            {{ $orders->links() }}
                        </div>
                    @else
                        <div class="text-center py-4">
                            <img src="{{ asset('assets/images/no-data.svg') }}" alt="No Orders" class="img-fluid mb-3" style="max-height: 200px;">
                            <h4 class="text-muted">No orders found</h4>
                            <p>There are no food & beverage orders to display at this time.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Order Details Modal -->
<div class="modal fade" id="orderDetailsModal" tabindex="-1" aria-labelledby="orderDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="orderDetailsModalLabel">Order Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="orderDetailsContent">
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2">Loading order details...</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // Initialize DataTable
        $('table').DataTable({
            ordering: true,
            paging: false,
            info: false,
            searching: true,
            responsive: true
        });

        // Handle order details modal
        $('.view-order').click(function() {
            const orderId = $(this).data('order-id');
            $('#orderDetailsContent').html('<div class="text-center py-4"><div class="spinner-border text-primary" role="status"></div><p class="mt-2">Loading order details...</p></div>');

            // Load order details via AJAX
            $.get(`{{ route('admin.food-beverages.orders.details', '') }}/${orderId}`, function(response) {
                $('#orderDetailsContent').html(response);
            }).fail(function() {
                $('#orderDetailsContent').html('<div class="alert alert-danger">Failed to load order details. Please try again.</div>');
            });
        });
    });
</script>
@endpush
