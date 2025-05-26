<div class="order-details">
    <div class="row mb-3">
        <div class="col-md-6">
            <h5>Order Information</h5>
            <div class="mb-2">
                <strong>Order ID:</strong> #{{ $order->order_id }}
            </div>
            <div class="mb-2">
                <strong>Status:</strong>
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
            </div>
            <div class="mb-2">
                <strong>Date & Time:</strong> {{ \Carbon\Carbon::parse($order->created_at)->format('d M Y H:i') }}
            </div>
            <div class="mb-2">
                <strong>Payment Method:</strong> {{ ucfirst($order->payment_method ?? 'Cash') }}
            </div>
            <div class="mb-2">
                <strong>Payment Status:</strong>
                @if($order->payment_status == 'paid')
                    <span class="badge bg-success">Paid</span>
                @elseif($order->payment_status == 'pending')
                    <span class="badge bg-warning">Pending</span>
                @else
                    <span class="badge bg-secondary">{{ ucfirst($order->payment_status) }}</span>
                @endif
            </div>
        </div>
        <div class="col-md-6">
            <h5>Customer Information</h5>
            <div class="mb-2">
                <strong>Name:</strong> {{ $order->customer_name }}
            </div>
            <div class="mb-2">
                <strong>Phone:</strong> {{ $order->phone ?? 'Not available' }}
            </div>
            <div class="mb-2">
                <strong>Table/Location:</strong> {{ $order->table_number ?? 'Not specified' }}
            </div>
            <div class="mb-2">
                <strong>Notes:</strong> {{ $order->notes ?? 'No special instructions' }}
            </div>
        </div>
    </div>

    <h5>Order Items</h5>
    <div class="table-responsive">
        <table class="table table-bordered">
            <thead class="table-light">
                <tr>
                    <th>Item</th>
                    <th class="text-center">Quantity</th>
                    <th class="text-end">Price</th>
                    <th class="text-end">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @if(is_array($orderItems) && count($orderItems) > 0)
                    @foreach($orderItems as $item)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    @if(isset($item['image']) && !empty($item['image']))
                                        <img src="{{ asset('storage/' . $item['image']) }}" alt="{{ $item['name'] }}" class="me-2" style="width: 40px; height: 40px; object-fit: cover;">
                                    @endif
                                    <div>
                                        {{ $item['name'] }}
                                        @if(isset($item['options']) && !empty($item['options']))
                                            <small class="d-block text-muted">
                                                {{ implode(', ', $item['options']) }}
                                            </small>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="text-center">{{ $item['quantity'] }}</td>
                            <td class="text-end">Rp {{ number_format($item['price'], 0, ',', '.') }}</td>
                            <td class="text-end">Rp {{ number_format($item['price'] * $item['quantity'], 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="4" class="text-center">No items found</td>
                    </tr>
                @endif
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="3" class="text-end">Subtotal:</th>
                    <th class="text-end">Rp {{ number_format($order->subtotal ?? $order->total_amount, 0, ',', '.') }}</th>
                </tr>
                @if(isset($order->tax) && $order->tax > 0)
                    <tr>
                        <th colspan="3" class="text-end">Tax:</th>
                        <th class="text-end">Rp {{ number_format($order->tax, 0, ',', '.') }}</th>
                    </tr>
                @endif
                @if(isset($order->discount) && $order->discount > 0)
                    <tr>
                        <th colspan="3" class="text-end">Discount:</th>
                        <th class="text-end">- Rp {{ number_format($order->discount, 0, ',', '.') }}</th>
                    </tr>
                @endif
                <tr>
                    <th colspan="3" class="text-end">Total:</th>
                    <th class="text-end">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</th>
                </tr>
            </tfoot>
        </table>
    </div>

    @if($order->status == 'pending')
        <div class="d-flex justify-content-end mt-3">
            <form action="{{ route('admin.food-beverages.orders.process', $order->id) }}" method="POST" class="me-2">
                @csrf
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-utensils me-1"></i> Process Order
                </button>
            </form>
            <form action="{{ route('admin.food-beverages.orders.cancel', $order->id) }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to cancel this order?')">
                    <i class="fas fa-times me-1"></i> Cancel Order
                </button>
            </form>
        </div>
    @elseif($order->status == 'processing')
        <div class="d-flex justify-content-end mt-3">
            <form action="{{ route('admin.food-beverages.orders.complete', $order->id) }}" method="POST" class="me-2">
                @csrf
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-check me-1"></i> Complete Order
                </button>
            </form>
            <form action="{{ route('admin.food-beverages.orders.cancel', $order->id) }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to cancel this order?')">
                    <i class="fas fa-times me-1"></i> Cancel Order
                </button>
            </form>
        </div>
    @endif
</div>
