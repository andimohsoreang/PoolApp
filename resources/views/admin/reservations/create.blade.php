@extends('layouts.app')

@section('title', 'Create Reservation')

@section('content')
<div class="container-fluid">
    <!-- Page-Title -->
    <div class="row">
        <div class="col-sm-12">
            <div class="page-title-box">
                <div class="float-end">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.reservations.index') }}">Reservations</a></li>
                        <li class="breadcrumb-item active">Create Reservation</li>
                    </ol>
                </div>
                <h4 class="page-title">Create New Reservation</h4>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Reservation Details</h4>
                </div>
                <div class="card-body">
                    @if(session('error'))
                        <div class="alert alert-danger">
                            {{ session('error') }}
                        </div>
                    @endif

                    <form action="{{ route('admin.reservations.store') }}" method="POST">
                        @csrf
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="customer_id" class="form-label">Customer <span class="text-danger">*</span></label>
                                <select class="form-select @error('customer_id') is-invalid @enderror" id="customer_id" name="customer_id" required>
                                    <option value="">Select Customer</option>
                                    @foreach($customers as $customer)
                                        <option value="{{ $customer->id }}" {{ old('customer_id') == $customer->id ? 'selected' : '' }}>
                                            {{ $customer->name }} ({{ $customer->phone }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('customer_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="table_id" class="form-label">Table <span class="text-danger">*</span></label>
                                <select class="form-select @error('table_id') is-invalid @enderror" id="table_id" name="table_id" required>
                                    <option value="">Select Table</option>
                                    @foreach($tables as $table)
                                        <option value="{{ $table->id }}" {{ old('table_id') == $table->id ? 'selected' : '' }}>
                                            Table {{ $table->table_number }} - {{ $table->room->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('table_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="start_time" class="form-label">Start Time <span class="text-danger">*</span></label>
                                <input type="datetime-local" class="form-control @error('start_time') is-invalid @enderror" id="start_time" name="start_time" value="{{ old('start_time') }}" required>
                                @error('start_time')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="end_time" class="form-label">End Time <span class="text-danger">*</span></label>
                                <input type="datetime-local" class="form-control @error('end_time') is-invalid @enderror" id="end_time" name="end_time" value="{{ old('end_time') }}" required>
                                @error('end_time')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="promo_code" class="form-label">Promo Code</label>
                            <input type="text" class="form-control @error('promo_code') is-invalid @enderror" id="promo_code" name="promo_code" value="{{ old('promo_code') }}">
                            @error('promo_code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="notes" class="form-label">Notes</label>
                            <textarea class="form-control @error('notes') is-invalid @enderror" id="notes" name="notes" rows="3">{{ old('notes') }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.reservations.index') }}" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">Create Reservation</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card mt-4">
                <div class="card-header">
                    <h4 class="card-title">Table Availability</h4>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        Select a table and date to view its availability timeline.
                    </div>

                    <div id="table-timeline-container" style="height: 300px; display: none;">
                        <!-- Timeline will be loaded here -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // Initialize select2 for better dropdown experience
        $('#customer_id, #table_id').select2({
            theme: 'bootstrap-5'
        });

        // Function to check table availability when table or dates change
        function loadTableTimeline() {
            const tableId = $('#table_id').val();
            const startDate = $('#start_time').val();

            if (tableId && startDate) {
                // Get the date part only from the datetime
                const date = startDate.split('T')[0];

                // Show loading
                $('#table-timeline-container').html('<div class="text-center py-4"><div class="spinner-border text-primary" role="status"></div><p class="mt-2">Loading timeline...</p></div>').show();

                // Load the timeline for the selected table and date
                $.get(`{{ route('admin.walkin.timeline', '') }}/${tableId}?date=${date}`, function(response) {
                    $('#table-timeline-container').html(response).show();
                });
            }
        }

        // Trigger timeline load when table or dates change
        $('#table_id, #start_time').on('change', loadTableTimeline);

        // Calculate duration and price when times change
        $('#start_time, #end_time').on('change', function() {
            const startTime = $('#start_time').val();
            const endTime = $('#end_time').val();

            if (startTime && endTime) {
                const start = new Date(startTime);
                const end = new Date(endTime);

                // Make sure end time is after start time
                if (end <= start) {
                    $('#end_time').addClass('is-invalid');
                    $('#end_time').after('<div class="invalid-feedback">End time must be after start time</div>');
                    return;
                } else {
                    $('#end_time').removeClass('is-invalid');
                    $('.invalid-feedback').remove();
                }

                // Check availability again when times change
                loadTableTimeline();
            }
        });
    });
</script>
@endpush