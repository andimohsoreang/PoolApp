@extends('layouts.app')

@section('title', 'Buat Transaksi Walk-in')

@push('links')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <style>
        .price-card {
            transition: all 0.3s ease;
        }
        .price-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
    </style>
@endpush

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="page-title-box d-md-flex justify-content-md-between align-items-center">
                <h4 class="page-title">Buat Transaksi Walk-in</h4>
                <div class="">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.walkin.index') }}">Walk-in</a></li>
                        <li class="breadcrumb-item active">Buat Transaksi</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Flash Message -->
    <div class="row">
        <div class="col-12">
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    <strong>Berhasil!</strong> {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    <strong>Error!</strong> {{ session('error') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    <strong>Error!</strong> Ada kesalahan dalam data yang Anda masukkan.
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">Form Transaksi</h5>
                </div>
                <div class="card-body">
                    <form id="transactionForm" action="{{ route('admin.walkin.store') }}" method="POST">
                        @csrf

                        <input type="hidden" name="table_id" value="{{ $table->id }}">
                        <input type="hidden" name="price_amount" id="price_amount" value="0">

                        <div class="card bg-light mb-3 mt-3">
                            <div class="card-body">
                                <h5 class="card-title"><i class="fas fa-clock me-1"></i> Jadwal Hari Ini</h5>
                                <div id="tableSchedule">
                                    <div class="text-center py-2">
                                        <div class="spinner-border spinner-border-sm text-primary" role="status">
                                            <span class="visually-hidden">Loading...</span>
                                        </div>
                                        <span class="ms-2">Memuat jadwal meja...</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="customer_id" class="form-label">Customer <span class="text-danger">*</span></label>
                                <select class="form-select select2" id="customer_id" name="customer_id" required>
                                    <option value="">Pilih Customer</option>
                                    @foreach($customers as $customer)
                                        <option value="{{ $customer->id }}" {{ old('customer_id') == $customer->id ? 'selected' : '' }}>
                                            {{ $customer->name }} ({{ $customer->phone }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 text-end d-flex align-items-end">
                                <a href="#" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#addCustomerModal">
                                    <i class="fas fa-plus me-1"></i> Tambah Customer Baru
                                </a>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="start_time" class="form-label">Waktu Mulai <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="text" class="form-control flatpickr-datetime" id="start_time" name="start_time"
                                           placeholder="Pilih waktu mulai"
                                           value="{{ old('start_time', $startTime ? $date . ' ' . $startTime : $date . ' ' . now()->format('H:i')) }}"
                                           required>
                                    <span class="input-group-text"><i class="fas fa-clock"></i></span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="duration" class="form-label">Durasi (jam) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="number" class="form-control" id="duration" name="duration"
                                           min="0.5" max="12" step="0.5" value="{{ old('duration', 1) }}" required>
                                    <span class="input-group-text">jam</span>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Waktu Selesai</label>
                                <div class="form-control bg-light" id="end_time_display">-</div>
                            </div>
                            <div class="col-md-6">
                                <label for="payment_method" class="form-label">Metode Pembayaran <span class="text-danger">*</span></label>
                                <select class="form-select" id="payment_method" name="payment_method" required>
                                    <option value="">Pilih Metode Pembayaran</option>
                                    <option value="cash" {{ old('payment_method') == 'cash' ? 'selected' : '' }}>Cash</option>
                                    <option value="e_payment" {{ old('payment_method') == 'e_payment' ? 'selected' : '' }}>E-Payment</option>
                                </select>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-12">
                                <div id="availabilitySection" class="mt-2 mb-2">
                                    <!-- Ketersediaan meja akan ditampilkan di sini -->
                                </div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label for="notes" class="form-label">Catatan</label>
                                <textarea class="form-control" id="notes" name="notes" rows="2" placeholder="Catatan tambahan untuk transaksi">{{ old('notes') }}</textarea>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('admin.walkin.index') }}" class="btn btn-light">Batal</a>
                            <button type="submit" class="btn btn-primary" id="submitBtn" disabled>Buat Transaksi</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card mb-3">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">Informasi Meja</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-center mb-3">
                        <div class="avatar avatar-xl bg-primary-subtle text-primary rounded-circle">
                            <span class="avatar-title">{{ $table->table_number }}</span>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm table-borderless mb-0">
                            <tr>
                                <td width="40%">Nomor Meja</td>
                                <td width="5%">:</td>
                                <td width="55%"><strong>{{ $table->table_number }}</strong></td>
                            </tr>
                            <tr>
                                <td>Ruangan</td>
                                <td>:</td>
                                <td><strong>{{ $table->room->name }}</strong></td>
                            </tr>
                            <tr>
                                <td>Kapasitas</td>
                                <td>:</td>
                                <td><strong>{{ $table->capacity }} orang</strong></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">Harga per Jam</h5>
                </div>
                <div class="card-body">
                    {{-- <div class="price-card rounded border p-3 mb-3">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h5 class="mb-0">Rp {{ number_format($price->price_per_hour, 0, ',', '.') }}</h5>
                                <span class="badge bg-info">{{ ucfirst($price->day_type) }}</span>
                            </div>
                            <div class="text-end">
                                <small class="d-block text-muted">{{ Carbon\Carbon::parse($price->start_time)->format('H:i') }} - {{ Carbon\Carbon::parse($price->end_time)->format('H:i') }}</small>
                            </div>
                        </div>
                    </div> --}}

                    <h6 class="mb-2"><i class="fas fa-money-bill-wave me-1"></i> Ringkasan Harga</h6>
                    <div class="row mb-1">
                        <div class="col-6">Harga per jam:</div>
                        <div class="col-6 text-end" id="hourly_rate_display">Rp {{ number_format($price->price_per_hour, 0, ',', '.') }}</div>
                    </div>
                    <div class="row mb-1">
                        <div class="col-6">Durasi:</div>
                        <div class="col-6 text-end" id="duration_display">0 jam</div>
                    </div>
                    <div class="row mb-1">
                        <div class="col-6">Subtotal:</div>
                        <div class="col-6 text-end" id="subtotal_display">Rp 0</div>
                    </div>
                    <hr>
                    <div class="row fw-bold">
                        <div class="col-6">Total:</div>
                        <div class="col-6 text-end" id="total_price_display">Rp 0</div>
                    </div>

                    <h6 class="mt-3 mb-2"><i class="fas fa-calendar-alt me-1"></i> Informasi Harga</h6>
                    <div id="priceInfoSection">
                        <p class="text-muted">Informasi harga akan muncul setelah waktu mulai dipilih</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Tambah Customer -->
    <div class="modal fade" id="addCustomerModal" tabindex="-1" aria-labelledby="addCustomerModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addCustomerModalLabel">Tambah Customer Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="new_customer_name" class="form-label">Nama <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="new_customer_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="new_customer_phone" class="form-label">Nomor HP <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="new_customer_phone" required>
                    </div>
                    <div class="mb-3">
                        <label for="new_customer_email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="new_customer_email">
                    </div>
                    <div class="mb-3">
                        <label for="new_customer_address" class="form-label">Alamat</label>
                        <textarea class="form-control" id="new_customer_address" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary" id="saveCustomerBtn">Simpan</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    // Format currency function
    function formatCurrency(amount) {
        return 'Rp ' + new Intl.NumberFormat('id-ID').format(amount);
    }

    // Wait for jQuery to load first
    $(document).ready(function() {
        // Initialize Select2
        $('.select2').select2({
            width: '100%',
            placeholder: 'Pilih customer'
        });

        // Initialize flatpickr for datetime
        const flatpickrInstance = flatpickr(".flatpickr-datetime", {
            enableTime: true,
            dateFormat: "Y-m-d H:i",
            time_24hr: true,
            minuteIncrement: 30,
            allowInput: true,
            onChange: function() {
                checkAvailability();
            }
        });

        // Load table schedule
        loadTableSchedule();

        // Check availability initially
        checkAvailability();

        // Update availability check when duration changes
        $('#duration').on('change', function() {
            checkAvailability();
        });

        // Handle Add Customer button
        $('#saveCustomerBtn').on('click', function() {
            const name = $('#new_customer_name').val();
            const phone = $('#new_customer_phone').val();
            const email = $('#new_customer_email').val();
            const address = $('#new_customer_address').val();

            if (!name || !phone) {
                alert('Nama dan nomor HP wajib diisi!');
                return;
            }

            $.ajax({
                url: '{{ route('admin.customers.store') }}',
                type: 'POST',
                data: {
                    name: name,
                    phone: phone,
                    email: email,
                    address: address,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    // Add the new customer to the select box
                    const newOption = new Option(
                        `${response.data.name} (${response.data.phone})`,
                        response.data.id,
                        true,
                        true
                    );
                    $('#customer_id').append(newOption).trigger('change');

                    // Close the modal and reset form
                    $('#addCustomerModal').modal('hide');
                    $('#new_customer_name').val('');
                    $('#new_customer_phone').val('');
                    $('#new_customer_email').val('');
                    $('#new_customer_address').val('');
                },
                error: function(xhr) {
                    let errorMessage = 'Gagal menambahkan customer';
                    if (xhr.responseJSON && xhr.responseJSON.errors) {
                        errorMessage = Object.values(xhr.responseJSON.errors).flat().join('\n');
                    }
                    alert('Error: ' + errorMessage);
                }
            });
        });
    });

    // Functions moved outside document ready for global scope
    function loadTableSchedule() {
        $.ajax({
            url: '{{ route('admin.walkin.table-details') }}',
            type: 'GET',
            data: {
                table_id: {{ $table->id }},
                date: '{{ $date }}'
            },
            success: function(response) {
                const tableSchedule = $('#tableSchedule');

                if (response.transactions && response.transactions.length > 0) {
                    let scheduleHTML = '<div class="list-group">';

                    response.transactions.forEach(function(transaction) {
                        let statusBadge = '';
                        switch(transaction.status) {
                            case 'pending':
                                statusBadge = '<span class="badge bg-warning">Pending</span>';
                                break;
                            case 'paid':
                                statusBadge = '<span class="badge bg-success">Paid</span>';
                                break;
                            case 'completed':
                                statusBadge = '<span class="badge bg-info">Completed</span>';
                                break;
                            default:
                                statusBadge = '<span class="badge bg-secondary">'+transaction.status+'</span>';
                        }

                        scheduleHTML += `
                            <div class="list-group-item list-group-item-action flex-column align-items-start">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1">${transaction.customer_name}</h6>
                                    ${statusBadge}
                                </div>
                                <p class="mb-1">
                                    <i class="far fa-clock"></i> ${transaction.start_time} - ${transaction.end_time}
                                    <small class="text-muted">(${transaction.transaction_code})</small>
                                </p>
                            </div>
                        `;
                    });

                    scheduleHTML += '</div>';
                    tableSchedule.html(scheduleHTML);
                } else {
                    tableSchedule.html('<div class="alert alert-info">Belum ada jadwal untuk meja ini hari ini.</div>');
                }
            },
            error: function() {
                $('#tableSchedule').html('<div class="alert alert-danger">Gagal memuat jadwal meja.</div>');
            }
        });
    }

    // Function to check availability
    function checkAvailability() {
        const startTime = $('#start_time').val();
        const duration = $('#duration').val();

        if (!startTime || !duration) {
            console.log('Data tidak lengkap:', { startTime, duration });
            return;
        }

        // Show loading state
        $('#submitBtn').prop('disabled', true);
        $('#availabilitySection').html(`
            <div class="d-flex align-items-center">
                <div class="spinner-border spinner-border-sm text-primary me-2" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <span>Memeriksa ketersediaan meja...</span>
            </div>
        `);

        // Update end time display
        const startDateTime = new Date(startTime);
        const durationMs = duration * 60 * 60 * 1000;
        const endDateTime = new Date(startDateTime.getTime() + durationMs);

        const formatTime = (date) => {
            const hours = date.getHours().toString().padStart(2, '0');
            const minutes = date.getMinutes().toString().padStart(2, '0');
            return `${hours}:${minutes}`;
        };

        const formatDateTime = (date) => {
            const year = date.getFullYear();
            const month = (date.getMonth() + 1).toString().padStart(2, '0');
            const day = date.getDate().toString().padStart(2, '0');
            const hours = date.getHours().toString().padStart(2, '0');
            const minutes = date.getMinutes().toString().padStart(2, '0');
            return `${year}-${month}-${day} ${hours}:${minutes}`;
        };

        $('#end_time_display').text(formatTime(endDateTime));

        // Check availability
        $.ajax({
            url: "{{ route('admin.walkin.checkAvailability') }}",
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                table_id: {{ $table->id }},
                start_time: formatDateTime(startDateTime),
                duration: duration
            },
            success: function(response) {
                console.log("Response dari server:", response);

                if (response.status === 'available') {
                    // Update availability section with success message
                    $('#availabilitySection').html(`
                        <div class="alert alert-success mb-0">
                            <i class="fas fa-check-circle me-1"></i> Meja tersedia pada waktu yang dipilih
                        </div>
                    `);

                    // Update price calculation
                    const pricePerHour = response.price_info.price_per_hour;
                    const totalPrice = response.price_info.total_price;
                    const dayType = response.price_info.day_type === 'weekend' ? 'Akhir Pekan' : 'Hari Kerja';
                    const startTimeHarga = response.price_info.start_time;
                    const endTimeHarga = response.price_info.end_time;
                    const isOvernight = response.price_info.is_overnight;

                    // Update price info section
                    $('#priceInfoSection').html(`
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="mb-0">${formatCurrency(pricePerHour)}/jam</h6>
                            <span class="badge ${dayType === 'Akhir Pekan' ? 'bg-info' : 'bg-primary'}">${dayType}</span>
                        </div>
                        <p class="mb-1 text-muted">Waktu Berlaku: ${startTimeHarga} - ${endTimeHarga}
                            ${isOvernight ? '<span class="badge bg-warning text-dark">Overnight</span>' : ''}
                        </p>
                    `);

                    $('#hourly_rate_display').text(formatCurrency(pricePerHour));
                    $('#duration_display').text(duration + ' jam');
                    $('#subtotal_display').text(formatCurrency(totalPrice));
                    $('#total_price_display').text(formatCurrency(totalPrice));
                    $('#price_amount').val(totalPrice);

                    // Enable submit button
                    $('#submitBtn').prop('disabled', false);
                } else {
                    // Show error message in availability section
                    let errorMessage = response.message;
                    if (response.conflict_info) {
                        errorMessage = `Meja sudah dibooking oleh ${response.conflict_info.customer} dari ${response.conflict_info.start_time} sampai ${response.conflict_info.end_time} (No. Transaksi: ${response.conflict_info.transaction_code})`;
                    }

                    $('#availabilitySection').html(`
                        <div class="alert alert-danger mb-0">
                            <i class="fas fa-exclamation-circle me-1"></i> ${errorMessage}
                        </div>
                    `);

                    // Disable submit button
                    $('#submitBtn').prop('disabled', true);
                }
            },
            error: function(xhr) {
                console.error("Error:", xhr);

                // Show error message in availability section
                $('#availabilitySection').html(`
                    <div class="alert alert-danger mb-0">
                        <i class="fas fa-exclamation-circle me-1"></i> Terjadi kesalahan saat memeriksa ketersediaan
                    </div>
                `);

                // Disable submit button
                $('#submitBtn').prop('disabled', true);
            }
        });
    }
</script>
@endpush

