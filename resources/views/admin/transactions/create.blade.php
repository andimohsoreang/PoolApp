@extends('layouts.app')

@section('title', 'Buat Transaksi Baru')

@push('links')
    <link href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
@endpush

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="page-title-box d-md-flex justify-content-between align-items-center">
                <h4 class="page-title">Buat Transaksi Baru</h4>
                <div class="">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.transactions.index') }}">Transaksi</a></li>
                        <li class="breadcrumb-item active">Buat Baru</li>
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
                    <ul class="mb-0">
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
            <!-- Transaction Form Card -->
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Form Transaksi</h4>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.transactions.store') }}" method="POST" id="transactionForm" onsubmit="return validateForm()">
                        @csrf
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="transaction_type" class="form-label">Tipe Transaksi <span class="text-danger">*</span></label>
                                <select class="form-select @error('transaction_type') is-invalid @enderror" id="transaction_type" name="transaction_type" required>
                                    <option value="walk_in" {{ old('transaction_type') == 'walk_in' ? 'selected' : '' }}>Walk In</option>
                                    <option value="reservation" {{ old('transaction_type') == 'reservation' ? 'selected' : '' }}>Reservation</option>
                                </select>
                                @error('transaction_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="customer_id" class="form-label">Customer <span class="text-danger">*</span></label>
                                <select class="form-select select2 @error('customer_id') is-invalid @enderror" id="customer_id" name="customer_id" required>
                                    <option value="">-- Pilih Customer --</option>
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
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="room_id" class="form-label">Ruangan <span class="text-danger">*</span></label>
                                <select class="form-select @error('room_id') is-invalid @enderror" id="room_id" name="room_id" required>
                                    <option value="">-- Pilih Ruangan --</option>
                                    @foreach($rooms as $room)
                                        <option value="{{ $room->id }}" {{ old('room_id') == $room->id ? 'selected' : '' }}>
                                            {{ $room->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('room_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="table_id" class="form-label">Meja <span class="text-danger">*</span></label>
                                <select class="form-select @error('table_id') is-invalid @enderror" id="table_id" name="table_id" required>
                                    <option value="">-- Pilih Meja --</option>
                                    @foreach($tables as $table)
                                        <option value="{{ $table->id }}" data-room="{{ $table->room_id }}" {{ old('table_id') == $table->id ? 'selected' : '' }}>
                                            {{ $table->table_number }} ({{ $table->room->name }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('table_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="start_date" class="form-label">Tanggal <span class="text-danger">*</span></label>
                                <input type="text" class="form-control flatpickr-date @error('start_date') is-invalid @enderror"
                                    id="start_date" name="start_date" value="{{ old('start_date') }}" required>
                                @error('start_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="start_time_input" class="form-label">Waktu Mulai <span class="text-danger">*</span></label>
                                <input type="text" class="form-control flatpickr-time @error('start_time_input') is-invalid @enderror"
                                    id="start_time_input" name="start_time_input" value="{{ old('start_time_input') }}" required>
                                <input type="hidden" id="start_time" name="start_time">
                                @error('start_time_input')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="duration_hours" class="form-label">Durasi (Jam) <span class="text-danger">*</span></label>
                                <input type="number" class="form-control @error('duration_hours') is-invalid @enderror"
                                    id="duration_hours" name="duration_hours" value="{{ old('duration_hours', 1) }}" min="0.5" step="0.5" required>
                                @error('duration_hours')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="end_time_display" class="form-label">Waktu Selesai</label>
                                <input type="text" class="form-control" id="end_time_display" readonly>
                                <input type="hidden" id="end_time" name="end_time">
                            </div>
                        </div>

                        <!-- Price Input Field - Made visible to ensure it's submitted -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="price_per_hour" class="form-label">Harga Per Jam <span class="text-danger">*</span></label>
                                <input type="number" class="form-control @error('price_per_hour') is-invalid @enderror"
                                    id="price_per_hour" name="price_per_hour" value="800000" min="1" required>
                                <small class="form-text text-muted">Harga akan diisi otomatis, tapi bisa diubah manual jika diperlukan</small>
                                @error('price_per_hour')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="d-flex justify-content-end mt-3">
                            <a href="{{ route('admin.transactions.index') }}" class="btn btn-secondary me-2">
                                <i class="fas fa-times me-1"></i> Batal
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> Simpan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Table Status Card -->
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Status Meja</h4>
                </div>
                <div class="card-body">
                    <div id="tableStatusContainer">
                        <div class="text-center">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="mt-2">Memuat status meja...</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Price Info Card -->
            <div class="card mt-3">
                <div class="card-header">
                    <h4 class="card-title">Informasi Harga</h4>
                </div>
                <div class="card-body">
                    <div id="priceInfoContainer">
                        <p class="text-center text-muted">Pilih meja dan waktu untuk melihat informasi harga</p>
                    </div>
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
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize select2
            $('.select2').select2({
                width: '100%',
                placeholder: "Pilih opsi"
            });

            // Set default times
            const now = new Date();
            const oneHourLater = new Date(now.getTime() + (60 * 60 * 1000));

            // Initialize hidden time fields
            document.getElementById('start_time').value = now.toISOString();
            document.getElementById('end_time').value = oneHourLater.toISOString();

            // Setup price debug display updates
            const priceInput = document.getElementById('price_per_hour');
            const debugDisplay = document.getElementById('current-price-debug');

            // Function to update debug display
            function updatePriceDebug() {
                debugDisplay.textContent = priceInput.value || '0';
            }

            // Set up a MutationObserver to watch for changes to the price input value
            const observer = new MutationObserver(updatePriceDebug);
            observer.observe(priceInput, { attributes: true });

            // Also update on value change
            priceInput.addEventListener('change', updatePriceDebug);

            // Manual price input handler
            document.getElementById('manual_price').addEventListener('change', function() {
                priceInput.value = this.value;
                updatePriceDebug();
            });

            // Calculate initial price
            setTimeout(function() {
                if (document.getElementById('table_id').value) {
                    calculatePrice();
                }
            }, 500);

            // Initialize start time picker
            const startTimePicker = flatpickr('#start_time_input', {
                enableTime: true,
                noCalendar: false,
                dateFormat: "Y-m-d H:i",
                time_24hr: true,
                minDate: "today",
                allowInput: true,
                defaultDate: now,
                onChange: function(selectedDates, dateStr) {
                    if (selectedDates[0]) {
                        // Update the hidden start_time field
                        document.getElementById('start_time').value = selectedDates[0].toISOString();

                        // Update end time based on new start time
                        updateEndTime();

                        // Recalculate price
                        calculatePrice();
                    }
                }
            });

            // Initialize date picker
            flatpickr('.flatpickr-date', {
                dateFormat: "Y-m-d",
                minDate: "today",
                defaultDate: "today"
            });

            // Initialize end time picker
            const endTimePicker = flatpickr('#end_time_display', {
                enableTime: true,
                dateFormat: "Y-m-d H:i",
                time_24hr: true,
                minDate: now,
                allowInput: true,
                defaultDate: oneHourLater
            });

            // Handle room change to filter tables
            document.getElementById('room_id').addEventListener('change', function() {
                const roomId = this.value;
                const tableSelect = document.getElementById('table_id');
                const tableOptions = tableSelect.querySelectorAll('option');

                // Show only tables for the selected room
                tableOptions.forEach(option => {
                    if (option.value === '' || option.getAttribute('data-room') === roomId) {
                        option.style.display = '';
                    } else {
                        option.style.display = 'none';
                    }
                });

                // Reset table selection
                tableSelect.value = '';
            });

            // Handle table selection
            document.getElementById('table_id').addEventListener('change', function() {
                // If a table is selected, calculate price
                if (this.value) {
                    updateEndTime();
                    // Add slight delay to ensure other values are set
                    setTimeout(calculatePrice, 200);
                }
            });

            // Load tables status
            loadTablesStatus();

            // Set up price calculation when times or table changes
            ['start_time_input', 'duration_hours', 'table_id'].forEach(id => {
                document.getElementById(id).addEventListener('change', function() {
                    updateEndTime();
                    calculatePrice();
                });
            });

            // Function to update end time based on start time and duration
            function updateEndTime() {
                const startTimeStr = document.getElementById('start_time_input').value;
                const durationHours = parseFloat(document.getElementById('duration_hours').value) || 1;

                if (startTimeStr) {
                    const startTime = new Date(startTimeStr);
                    const endTime = new Date(startTime.getTime() + (durationHours * 60 * 60 * 1000));

                    // Update hidden fields for form submission
                    document.getElementById('start_time').value = startTime.toISOString();
                    document.getElementById('end_time').value = endTime.toISOString();

                    // Update end time display
                    document.getElementById('end_time_display').value =
                        endTime.toLocaleDateString() + ' ' +
                        endTime.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
                }
            }

            // Check promo code
            document.getElementById('checkPromoBtn').addEventListener('click', checkPromoCode);
        });

        function loadTablesStatus() {
            fetch('{{ route("admin.transactions.tables-status") }}')
                .then(response => response.json())
                .then(data => {
                    const container = document.getElementById('tableStatusContainer');
                    container.innerHTML = '';

                    // Group tables by room
                    const tablesByRoom = {};
                    data.tables.forEach(table => {
                        if (!tablesByRoom[table.room]) {
                            tablesByRoom[table.room] = [];
                        }
                        tablesByRoom[table.room].push(table);
                    });

                    // Create room sections
                    for (const [roomName, tables] of Object.entries(tablesByRoom)) {
                        // Create room title
                        const roomTitle = document.createElement('h5');
                        roomTitle.textContent = roomName;
                        roomTitle.className = 'mt-3 mb-2';
                        container.appendChild(roomTitle);

                        // Create table buttons grid
                        const tableGrid = document.createElement('div');
                        tableGrid.className = 'd-flex flex-wrap gap-2';

                        tables.forEach(table => {
                            const tableBtn = document.createElement('div');
                            tableBtn.className = 'text-center';
                            tableBtn.style.width = '60px';

                            const statusBadge = document.createElement('div');
                            statusBadge.className = table.available ?
                                'badge bg-success p-2 mb-1' : 'badge bg-danger p-2 mb-1';
                            statusBadge.style.width = '100%';
                            statusBadge.textContent = table.table_number;

                            tableBtn.appendChild(statusBadge);

                            if (!table.available && table.current_transaction) {
                                const timeInfo = document.createElement('small');
                                timeInfo.className = 'd-block text-muted';
                                timeInfo.textContent = `${table.current_transaction.start_time} - ${table.current_transaction.end_time}`;
                                tableBtn.appendChild(timeInfo);
                            }

                            tableGrid.appendChild(tableBtn);
                        });

                        container.appendChild(tableGrid);
                    }
                })
                .catch(error => {
                    console.error('Error loading table status:', error);
                    document.getElementById('tableStatusContainer').innerHTML =
                        '<div class="alert alert-danger">Failed to load table status</div>';
                });
        }

        function calculatePrice() {
            const tableId = document.getElementById('table_id').value;
            const startTime = document.getElementById('start_time').value;
            const endTime = document.getElementById('end_time').value;
            const priceContainer = document.getElementById('priceInfoContainer');
            const priceField = document.getElementById('price_per_hour');

            // Reset container
            priceContainer.innerHTML = '<p class="text-center text-muted">Mengkalkulasi harga...</p>';

            // Check if all required fields are filled
            if (!tableId || !startTime || !endTime) {
                priceContainer.innerHTML = '<p class="text-center text-muted">Pilih meja dan waktu untuk melihat informasi harga</p>';
                priceField.value = '800000'; // Set default price
                return;
            }

            // Calculate duration
            const start = new Date(startTime);
            const end = new Date(endTime);

            if (end <= start) {
                priceContainer.innerHTML = '<div class="alert alert-danger">Waktu selesai harus lebih besar dari waktu mulai</div>';
                priceField.value = '800000'; // Set default price
                return;
            }

            // Calculate duration in hours
            const durationMs = end - start;
            const durationHours = durationMs / (1000 * 60 * 60);

            // Get price for this table and time via API
            fetch(`/admin/tables/${tableId}/price?datetime=${encodeURIComponent(startTime)}`)
                .then(response => response.json())
                .then(data => {
                    console.log("API Response:", data);

                    // Check both possible response formats
                    let pricePerHour = null;
                    if (data.success && data.price && data.price.price_per_hour) {
                        pricePerHour = data.price.price_per_hour;
                    } else if (data.price_info && data.price_info.price_per_hour) {
                        pricePerHour = data.price_info.price_per_hour;
                    } else if (data.status === "available" && data.price_info && data.price_info.price_per_hour) {
                        pricePerHour = data.price_info.price_per_hour;
                    }

                    // If no price is found, use default price
                    if (!pricePerHour) {
                        pricePerHour = 800000; // Default price
                        priceContainer.innerHTML = `<div class="alert alert-warning">Menggunakan harga default: Rp ${formatNumber(pricePerHour)}/jam</div>`;
                    }

                    // Parse price to ensure it's a numeric value
                    const priceValue = parseFloat(pricePerHour);
                    const subtotal = priceValue * durationHours;

                    // Store the price_per_hour in the visible field
                    priceField.value = priceValue;
                    console.log("Price per hour set to:", priceValue);

                    // Display price info
                    priceContainer.innerHTML = `
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <tr>
                                    <th>Harga per jam</th>
                                    <td>Rp ${formatNumber(priceValue)}</td>
                                </tr>
                                <tr>
                                    <th>Durasi</th>
                                    <td>${durationHours.toFixed(2)} jam</td>
                                </tr>
                                <tr>
                                    <th>Subtotal</th>
                                    <td>Rp ${formatNumber(subtotal)}</td>
                                </tr>
                            </table>
                        </div>
                    `;
                })
                .catch(error => {
                    console.error('Error fetching price:', error);
                    priceContainer.innerHTML = '<div class="alert alert-danger">Gagal memuat informasi harga. Menggunakan harga default.</div>';
                    priceField.value = '800000'; // Set default price on error
                });
        }

        function checkPromoCode() {
            const promoCode = document.getElementById('promo_code').value;
            const promoInfo = document.getElementById('promoInfo');
            const tableId = document.getElementById('table_id').value;
            const startTime = document.getElementById('start_time').value;
            const endTime = document.getElementById('end_time').value;

            // Check if promo code is entered
            if (!promoCode) {
                promoInfo.innerHTML = '<div class="alert alert-warning">Masukkan kode promo untuk dicek</div>';
                return;
            }

            // Show loading indicator
            document.getElementById('checkPromoBtnText').classList.add('d-none');
            document.getElementById('checkPromoBtnLoading').classList.remove('d-none');
            document.getElementById('checkPromoBtn').disabled = true;

            // Calculate amount for validation
            const start = new Date(startTime);
            const end = new Date(endTime);
            const durationHours = (end - start) / (1000 * 60 * 60);

            // Get price and validate promo
            fetch(`/admin/promos/test?promo_code=${encodeURIComponent(promoCode)}&table_id=${tableId}&amount=10000`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const promo = data.promo;

                        if (data.valid && data.applicable) {
                            promoInfo.innerHTML = `
                                <div class="alert alert-success">
                                    <strong>${promo.name}</strong><br>
                                    Diskon: ${promo.discount_type === 'percentage' ?
                                        `${promo.discount_value}%` :
                                        `Rp ${formatNumber(promo.discount_value)}`
                                    }<br>
                                    ${promo.minimum_price > 0 ? `Min. transaksi: Rp ${formatNumber(promo.minimum_price)}` : ''}
                                </div>
                            `;
                        } else {
                            promoInfo.innerHTML = `
                                <div class="alert alert-warning">
                                    <strong>${promo.name}</strong><br>
                                    ${!data.valid ? 'Promo tidak berlaku: ' + data.message : ''}
                                    ${!data.applicable ? 'Tidak berlaku untuk meja/ruangan yang dipilih' : ''}
                                </div>
                            `;
                        }
                    } else {
                        promoInfo.innerHTML = `<div class="alert alert-danger">${data.message || 'Promo tidak ditemukan'}</div>`;
                    }
                })
                .catch(error => {
                    console.error('Error checking promo:', error);
                    promoInfo.innerHTML = '<div class="alert alert-danger">Gagal memeriksa promo</div>';
                })
                .finally(() => {
                    // Reset button state
                    document.getElementById('checkPromoBtnText').classList.remove('d-none');
                    document.getElementById('checkPromoBtnLoading').classList.add('d-none');
                    document.getElementById('checkPromoBtn').disabled = false;
                });
        }

        function formatNumber(num) {
            return new Intl.NumberFormat('id-ID').format(num);
        }

        // Form validation function
        function validateForm() {
            const pricePerHour = document.getElementById('price_per_hour').value;
            const tableId = document.getElementById('table_id').value;
            const startTime = document.getElementById('start_time').value;
            const endTime = document.getElementById('end_time').value;

            console.log("Form submission values:");
            console.log("Table ID: " + tableId);
            console.log("Start Time: " + startTime);
            console.log("End Time: " + endTime);
            console.log("Price Per Hour: " + pricePerHour);

            if (!tableId) {
                alert('Silakan pilih meja terlebih dahulu');
                return false;
            }

            // Ensure price_per_hour has a valid value
            if (!pricePerHour || isNaN(pricePerHour) || parseFloat(pricePerHour) <= 0) {
                alert('Harga per jam tidak valid. Silakan periksa nilai di kolom Harga Per Jam.');
                document.getElementById('price_per_hour').focus();
                return false;
            }

            // Ensure start time is before end time
            const start = new Date(startTime);
            const end = new Date(endTime);
            if (end <= start) {
                alert('Waktu selesai harus lebih besar dari waktu mulai');
                return false;
            }

            return true;
        }
    </script>
@endpush
