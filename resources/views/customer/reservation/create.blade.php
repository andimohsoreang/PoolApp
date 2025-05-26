@extends('layouts.customer')

@section('title', 'Buat Reservasi')

@section('styles')
    <link rel="stylesheet" href="{{ asset('Travgo/preview/assets/css/common.css') }}">
    <link rel="stylesheet" href="{{ asset('Travgo/preview/assets/css/booking.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link href="{{ asset('dist/assets/libs/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css">
<style>
    .content-wrapper {
        padding-top: 5rem;
        padding-bottom: 2rem;
    }
    .server-time {
        font-size: 20px;
        font-weight: 600;
        text-align: center;
        color: #4361ee;
        margin-bottom: 16px;
        letter-spacing: 1px;
    }
    .server-time-label {
        font-size: 12px;
        color: #6c757d;
        text-align: center;
        margin-bottom: 16px;
    }
    .price-info {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 16px;
        margin-top: 16px;
    }
    .price-row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 8px;
    }
    .price-total {
        font-weight: 600;
        color: #4361ee;
        font-size: 18px;
    }
    .booking-list-table {
        width: 100%;
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.04);
        margin-bottom: 24px;
        overflow: hidden;
    }
    .booking-list-table th, .booking-list-table td {
        padding: 10px 12px;
        text-align: center;
        font-size: 15px;
    }
    .booking-list-table th {
        background: #f8f9fa;
        font-weight: 600;
    }
    .badge-status {
        padding: 4px 10px;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 500;
    }
    .badge-success { background: #10b981; color: #fff; }
    .badge-danger { background: #ef4444; color: #fff; }
    .badge-info { background: #3b82f6; color: #fff; }
    .badge-secondary { background: #6b7280; color: #fff; }
    @media (max-width: 768px) {
        .content-wrapper { padding-top: 4rem; }
        .booking-list-table th, .booking-list-table td { font-size: 13px; padding: 7px 6px; }
    }
</style>
@endsection

@section('content')
<div class="content-wrapper">
    <div class="container">
        <!-- Page Header -->
        <div class="d-flex align-items-center justify-content-between mb-24">
            <div>
                <h1 class="reservation-title">Buat Reservasi</h1>
                @if(isset($table))
                    <p class="text-muted">Pilih waktu reservasi untuk meja #{{ $table->table_number }}</p>
                @else
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Silakan pilih meja terlebih dahulu dari halaman sebelumnya.
                    </div>
                @endif
            </div>
            <a href="{{ route('customer.reservation.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i> Kembali
            </a>
        </div>

        @if(isset($table))
            <!-- Server Time -->
            <div class="server-time" id="serverTimeDisplay">--:--:--</div>
            <div class="server-time-label">Waktu Server Sekarang (WITA)</div>

            <!-- Booking List -->
            <div class="mb-4">
                <table class="booking-list-table">
                    <thead>
                        <tr>
                            <th>Customer</th>
                            <th>Waktu</th>
                            <th>Durasi</th>
                            <th>Status</th>
                            <th>Sisa Waktu</th>
                        </tr>
                    </thead>
                    <tbody id="bookingListBody">
                        <tr><td colspan="5" class="text-muted">Memuat data...</td></tr>
                    </tbody>
                </table>
            </div>

            <!-- Reservation Form -->
            <div class="card">
                <div class="card-body">
                    <form id="reservationForm" method="POST" action="{{ route('customer.reservation.store') }}">
                        @csrf
                        <input type="hidden" name="table_id" value="{{ $table->id }}">
                        <input type="hidden" name="price_per_hour" id="price_per_hour">
                        <input type="hidden" name="total_price" id="total_price">

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="date_picker" class="form-label">Tanggal <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="date_picker" name="date"
                                       value="{{ request('date', now()->format('Y-m-d')) }}" required>
                                <small class="text-muted">Pilih hari ini atau besok</small>
                            </div>
                            <div class="col-md-6">
                                <label for="start_time" class="form-label">Waktu Mulai <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="start_time" name="start_time"
                                           placeholder="Pilih waktu mulai" required readonly>
                                    <span class="input-group-text"><i class="fas fa-clock"></i></span>
                                </div>
                                <small class="text-muted">Pilih waktu antara 00:00 - 23:30</small>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="duration" class="form-label">Durasi (jam) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="number" class="form-control" id="duration" name="duration_hours"
                                           min="1" max="12" step="1" value="1" required>
                                    <span class="input-group-text">jam</span>
                                </div>
                                <small class="text-muted">Minimal 1 jam, maksimal 12 jam</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Waktu Selesai</label>
                                <div class="form-control bg-light" id="end_time_display">-</div>
                                <input type="hidden" id="end_time" name="end_time">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Status Ketersediaan</label>
                                <div id="availability_status" class="form-control bg-light">Menunggu pemilihan waktu...</div>
                            </div>
                        </div>

                        <div class="price-info">
                            <h6 class="mb-3">Detail Harga</h6>
                            <div class="price-row">
                                <span>Harga per Jam:</span>
                                <span id="hourly_rate_display">Rp 0</span>
                            </div>
                            <div class="price-row">
                                <span>Durasi:</span>
                                <span id="duration_display">0 jam</span>
                            </div>
                            <div class="price-row">
                                <span>Total:</span>
                                <span class="price-total" id="total_price_display">Rp 0</span>
                            </div>
                            <div class="price-row" id="priceRangeInfo" style="font-size: 0.85em; margin-top: 8px; color: #6c757d;">
                                <!-- Info rentang waktu harga akan ditampilkan di sini -->
                            </div>
                        </div>

                        <div class="alert alert-info mt-3">
                            <i class="fas fa-info-circle me-2"></i>
                            <small>
                                Setelah reservasi disetujui, Anda akan memiliki waktu 3 menit untuk melakukan pembayaran.
                                Jika tidak, reservasi akan otomatis dibatalkan.
                            </small>
                        </div>

                        <div class="d-flex justify-content-end mt-4">
                            <button type="submit" class="btn btn-primary" id="submitReservation" disabled>
                                <i class="fas fa-check me-1"></i> Konfirmasi Reservasi
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endif
    </div>
</div>

<!-- Review Reservation Modal -->
<div class="modal fade" id="reviewReservationModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tinjau Reservasi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="mb-3">Informasi Meja</h6>
                        <div class="mb-3">
                            <label class="form-label text-muted">Nomor Meja</label>
                            <div id="reviewTableNumber" class="form-control-plaintext"></div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-muted">Ruangan</label>
                            <div id="reviewRoomName" class="form-control-plaintext"></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h6 class="mb-3">Detail Waktu</h6>
                        <div class="mb-3">
                            <label class="form-label text-muted">Tanggal</label>
                            <div id="reviewDate" class="form-control-plaintext"></div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-muted">Waktu Mulai</label>
                            <div id="reviewStartTime" class="form-control-plaintext"></div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-muted">Waktu Selesai</label>
                            <div id="reviewEndTime" class="form-control-plaintext"></div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-muted">Durasi</label>
                            <div id="reviewDuration" class="form-control-plaintext"></div>
                        </div>
                    </div>
                </div>

                <hr>

                <div class="row">
                    <div class="col-12">
                        <h6 class="mb-3">Rincian Harga</h6>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <tbody>
                                    <tr>
                                        <td>Harga per Jam</td>
                                        <td class="text-end" id="reviewPricePerHour"></td>
                                    </tr>
                                    <tr>
                                        <td>Durasi</td>
                                        <td class="text-end" id="reviewDurationPrice"></td>
                                    </tr>
                                    <tr class="table-primary">
                                        <td><strong>Total</strong></td>
                                        <td class="text-end"><strong id="reviewTotalPrice"></strong></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div id="reviewPriceRangeInfo" class="text-muted small mt-2"></div>
                    </div>
                </div>

                <div class="alert alert-info mt-3">
                    <i class="fas fa-info-circle me-2"></i>
                    <small>
                        Setelah reservasi disetujui, Anda akan memiliki waktu 3 menit untuk melakukan pembayaran.
                        Jika tidak, reservasi akan otomatis dibatalkan.
                    </small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" id="confirmReservationBtn">
                    <i class="fas fa-check me-1"></i> Konfirmasi Reservasi
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    <script src="{{ asset('dist/assets/libs/sweetalert2/sweetalert2.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/id.js"></script>
    <script>
        // Helper
        function pad(num) { return num.toString().padStart(2, '0'); }
        function formatTimeDisplay(date) {
            return pad(date.getHours()) + ':' + pad(date.getMinutes());
        }
        function formatCurrency(amount) {
            return 'Rp ' + new Intl.NumberFormat('id-ID').format(amount);
        }

        let bookingList = [];
        let pricePerHour = 0;
        let selectedDate = "{{ request('date', $selectedDate->format('Y-m-d')) }}";
        let bookingInterval = null;

        // Flatpickr for date (only today and tomorrow)
        flatpickr("#date_picker", {
            dateFormat: "Y-m-d",
            minDate: "today",
            maxDate: new Date().fp_incr(1),
            defaultDate: selectedDate,
            allowInput: true,
            clickOpens: true,
            locale: "id",
            onChange: function(selectedDates) {
                if (selectedDates.length > 0) {
                    const dateStr = selectedDates[0].toISOString().split('T')[0];
                    // Redirect with the new date
                    window.location.href = "{{ route('customer.reservation.create', ['table' => $table->id ?? 0]) }}?date=" + dateStr;
                }
            }
        });

        // Flatpickr for time (00:00 - 23:30)
        flatpickr("#start_time", {
            enableTime: true,
            noCalendar: true,
            dateFormat: "H:i",
            time_24hr: true,
            minTime: "00:00",
            maxTime: "23:30",
            defaultDate: "08:00",
            minuteIncrement: 30,
            onChange: function(selectedDates, dateStr) {
                console.log('🕒 Start time changed to:', dateStr);
                updateEndTimeAndStatus();
            }
        });

        document.getElementById('duration').addEventListener('change', function(e) {
            console.log('⏱️ Duration changed to:', e.target.value);
            updateEndTimeAndStatus();
        });

        // Load booking list & price
        function loadBookingListAndPrice() {
            // Load booking list
            fetch(`{{ route('customer.reservation.timeline') }}?table_id={{ $table->id }}&date=${selectedDate}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.error) {
                        console.error('Error loading booking list:', data.message);
                        return;
                    }
                    bookingList = data.transactions || [];
                    renderBookingList();
                    updateEndTimeAndStatus();
                })
                .catch(error => {
                    console.error('Error loading booking list:', error);
                    const tbody = document.getElementById('bookingListBody');
                    tbody.innerHTML = '<tr><td colspan="5" class="text-danger">Gagal memuat data booking</td></tr>';
                });

            // Load price
            fetch(`{{ route('customer.reservation.getTableTimeline') }}?table_id={{ $table->id }}&date=${selectedDate}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.error) {
                        console.error('Error loading price:', data.message);
                        return;
                    }
                    pricePerHour = data.price_per_hour || 0;
                    document.getElementById('hourly_rate_display').textContent = formatCurrency(pricePerHour);
                    document.getElementById('price_per_hour').value = pricePerHour;
                    updateEndTimeAndStatus();
                })
                .catch(error => {
                    console.error('Error loading price:', error);
                    document.getElementById('hourly_rate_display').textContent = 'Error loading price';
                    document.getElementById('price_per_hour').value = 0;
                });
        }

        function renderBookingList() {
            const tbody = document.getElementById('bookingListBody');
            tbody.innerHTML = '';
            if (bookingList.length === 0) {
                tbody.innerHTML = '<tr><td colspan="5" class="text-muted">Belum ada booking hari ini</td></tr>';
                return;
            }
            bookingList.forEach((trx, idx) => {
                tbody.innerHTML += `<tr id="booking-row-${idx}">
                    <td>${trx.customer_name}</td>
                    <td>${trx.start_time} - ${trx.end_time}</td>
                    <td data-start="${trx.start_time}" data-end="${trx.end_time}"></td>
                    <td class="status-cell"></td>
                    <td class="countdown-cell"></td>
                </tr>`;
            });
            updateBookingStatusAndCountdown();
            if (bookingInterval) clearInterval(bookingInterval);
            bookingInterval = setInterval(updateBookingStatusAndCountdown, 1000);
        }

        function updateBookingStatusAndCountdown() {
            const now = new Date();
            bookingList.forEach((trx, idx) => {
                const [startH, startM] = trx.start_time.split(':').map(Number);
                const [endH, endM] = trx.end_time.split(':').map(Number);
                const start = new Date(selectedDate + 'T' + pad(startH) + ':' + pad(startM) + ':00');
                const end = new Date(selectedDate + 'T' + pad(endH) + ':' + pad(endM) + ':00');
                let durRaw = (end - start) / (1000 * 60 * 60);
                const dur = (durRaw % 1 === 0) ? durRaw.toFixed(0) : durRaw.toFixed(1);

                // Status & countdown
                let status = '<span class="badge-status badge-info">Akan Datang</span>';
                let countdown = '';
                if (now >= start && now < end) {
                    status = '<span class="badge-status badge-success">Sedang Berlangsung</span>';
                    let diff = Math.floor((end - now) / 1000);
                    let h = Math.floor(diff / 3600);
                    let m = Math.floor((diff % 3600) / 60);
                    let s = diff % 60;
                    countdown = `${pad(h)}:${pad(m)}:${pad(s)}`;
                } else if (now >= end) {
                    status = '<span class="badge-status badge-secondary">Selesai</span>';
                    countdown = '';
                }
                // Update DOM
                const row = document.getElementById(`booking-row-${idx}`);
                if (row) {
                    row.children[2].textContent = dur + ' jam';
                    row.querySelector('.status-cell').innerHTML = status;
                    row.querySelector('.countdown-cell').textContent = countdown;
                }
            });
        }

        // Update end time, status, price
        function updateEndTimeAndStatus() {
            const startTime = document.getElementById('start_time').value;
            const duration = document.getElementById('duration').value;
            if (!startTime || !duration) return;

            try {
                // Parse start time
                const [h, m] = startTime.split(':').map(Number);
                if (isNaN(h) || isNaN(m)) {
                    throw new Error('Invalid start time format');
                }

                // Calculate end time
                const start = new Date(selectedDate + 'T' + pad(h) + ':' + pad(m) + ':00');
                const end = new Date(start.getTime() + duration * 60 * 60 * 1000);

                // Format the dates for display and form submission
                const endTimeDisplay = formatTimeDisplay(end);
                document.getElementById('end_time_display').textContent = endTimeDisplay;

                // For form submission, use full date format (handle overnight)
                const endDateStr = end.toISOString().split('T')[0]; // Always use actual calculated end date
                console.log('End date calculation:', {
                    startDate: selectedDate,
                    startTime: startTime,
                    duration: duration,
                    calculatedEndDate: endDateStr,
                    endTimeDisplay: endTimeDisplay
                });

                // Set the hidden end_time value with proper date
                document.getElementById('end_time').value = endDateStr + ' ' + endTimeDisplay;
                document.getElementById('duration_display').textContent = duration + ' jam';

                // Get updated price for the selected time
                updatePriceForSelectedTime(startTime);
            } catch (error) {
                console.error('Error in updateEndTimeAndStatus:', error);
                const statusDiv = document.getElementById('availability_status');
                statusDiv.textContent = 'Error: ' + error.message;
                statusDiv.className = 'form-control bg-danger text-white';
                document.getElementById('submitReservation').disabled = true;
            }
        }

        // Get updated price based on the selected time
        function updatePriceForSelectedTime(selectedTime) {
            // Show loading state
            document.getElementById('hourly_rate_display').innerHTML = '<small>Memperbarui harga...</small>';
            document.getElementById('total_price_display').innerHTML = '<small>Memperbarui...</small>';

            // Get current duration
            const duration = document.getElementById('duration').value;

            console.log('🔍 Requesting price update for time:', selectedTime);

            // Make AJAX request to get updated price
            fetch(`{{ route('customer.reservation.getTableTimeline') }}?table_id={{ $table->id }}&date=${selectedDate}&start_time=${selectedTime}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.error) {
                        console.error('Error loading price:', data.message);
                        return;
                    }

                    console.log('💰 Received price data:', data);

                    // Update price information
                    pricePerHour = data.price_per_hour || 0;

                    // Check if this is an overnight price
                    let overnightBadge = '';
                    if (data.is_overnight) {
                        overnightBadge = '<span class="badge badge-warning ms-1">Overnight</span>';
                    }

                    // Update display with new price
                    document.getElementById('hourly_rate_display').innerHTML = formatCurrency(pricePerHour) + overnightBadge;
                    document.getElementById('price_per_hour').value = pricePerHour;

                    // Update price range info
                    updatePriceRangeInfo(data);

                    // Calculate total price
                    const totalPrice = pricePerHour * duration;
                    document.getElementById('total_price_display').textContent = formatCurrency(totalPrice);
                    document.getElementById('total_price').value = totalPrice;

                    // Check for conflicts after price update
                    checkForTimeConflicts();
                })
                .catch(error => {
                    console.error('Error loading price:', error);
                    document.getElementById('hourly_rate_display').textContent = 'Error loading price';
                    document.getElementById('price_per_hour').value = 0;
                    document.getElementById('submitReservation').disabled = true;
                });
        }

        // Check for time conflicts
        function checkForTimeConflicts() {
            const startTime = document.getElementById('start_time').value;
            const duration = document.getElementById('duration').value;
            if (!startTime || !duration) return;

            // Parse start time
            const [h, m] = startTime.split(':').map(Number);
            const start = new Date(selectedDate + 'T' + pad(h) + ':' + pad(m) + ':00');
            const end = new Date(start.getTime() + duration * 60 * 60 * 1000);

            // Check for conflicts
            let hasConflict = false;
            let conflictMsg = '';
            bookingList.forEach(trx => {
                try {
                    const [startH, startM] = trx.start_time.split(':').map(Number);
                    const [endH, endM] = trx.end_time.split(':').map(Number);

                    if (isNaN(startH) || isNaN(startM) || isNaN(endH) || isNaN(endM)) {
                        console.error('Invalid time format in booking:', trx);
                        return;
                    }

                    const trxStart = new Date(selectedDate + 'T' + pad(startH) + ':' + pad(startM) + ':00');
                    const trxEnd = new Date(selectedDate + 'T' + pad(endH) + ':' + pad(endM) + ':00');

                    if ((start < trxEnd && end > trxStart)) {
                        hasConflict = true;
                        conflictMsg = `Waktu bertabrakan dengan reservasi ${trx.customer_name} (${trx.start_time} - ${trx.end_time})`;
                    }
                } catch (error) {
                    console.error('Error processing booking:', trx, error);
                }
            });

            // Update availability status
            const statusDiv = document.getElementById('availability_status');
            if (hasConflict) {
                statusDiv.textContent = conflictMsg;
                statusDiv.className = 'form-control bg-danger text-white';
                document.getElementById('submitReservation').disabled = true;
            } else {
                statusDiv.textContent = 'Tersedia untuk reservasi';
                statusDiv.className = 'form-control bg-success text-white';
                document.getElementById('submitReservation').disabled = false;
            }
        }

        // Server time
        function updateServerTime() {
            const now = new Date();
            document.getElementById('serverTimeDisplay').textContent =
                pad(now.getHours()) + ':' + pad(now.getMinutes()) + ':' + pad(now.getSeconds());
        }
        setInterval(updateServerTime, 1000);
        updateServerTime();

        // Initial load
        loadBookingListAndPrice();

        // Update showReviewModal function to include promo details
        function showReviewModal() {
            // Get form values
            const date = document.getElementById('date_picker').value;
            const startTime = document.getElementById('start_time').value;
            const duration = document.getElementById('duration').value;
            const endTime = document.getElementById('end_time').value;
            const pricePerHour = document.getElementById('price_per_hour').value;
            const totalPrice = document.getElementById('total_price').value;

            // Format date
            const formattedDate = new Date(date).toLocaleDateString('id-ID', {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });

            // Format end time properly
            let formattedEndTime = "";
            try {
                // Parse the end time which may include date information
                if (endTime.includes(' ')) {
                    // If it has a date part, extract just the time portion
                    const endTimeParts = endTime.split(' ');
                    if (endTimeParts.length > 1) {
                        formattedEndTime = endTimeParts[1];
                    } else {
                        formattedEndTime = endTime;
                    }
                } else {
                    formattedEndTime = endTime;
                }

                console.log('Review modal values:', {
                    originalDate: date,
                    formattedDate: formattedDate,
                    startTime: startTime,
                    originalEndTime: endTime,
                    formattedEndTime: formattedEndTime,
                    duration: duration
                });
            } catch (e) {
                console.error("Error formatting end time:", e);
                formattedEndTime = endTime; // Fallback to original value
            }

            // Update modal content
            document.getElementById('reviewTableNumber').textContent = 'Meja #{{ $table->table_number }}';
            document.getElementById('reviewRoomName').textContent = '{{ $table->room->name }}';
            document.getElementById('reviewDate').textContent = formattedDate;
            document.getElementById('reviewStartTime').textContent = startTime;
            document.getElementById('reviewEndTime').textContent = formattedEndTime;
            document.getElementById('reviewDuration').textContent = duration + ' jam';

            // Update price details
            document.getElementById('reviewPricePerHour').textContent = formatCurrency(pricePerHour);
            document.getElementById('reviewDurationPrice').textContent = formatCurrency(pricePerHour * duration);
            document.getElementById('reviewTotalPrice').textContent = formatCurrency(totalPrice);

            // Show modal
            const modal = new bootstrap.Modal(document.getElementById('reviewReservationModal'));
            modal.show();
        }

        // Handle confirm button click
        document.getElementById('confirmReservationBtn').addEventListener('click', function() {
            const form = document.getElementById('reservationForm');
            const formData = new FormData(form);
            const submitBtn = document.getElementById('submitReservation');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Mengajukan...';

            fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(response => {
                if (!response.ok) {
                    return response.json().then(data => {
                        throw new Error(data.message || 'Network response was not ok');
                    });
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Reservasi Diajukan',
                        text: data.message || 'Pengajuan reservasi berhasil, menunggu persetujuan admin.',
                        confirmButtonText: 'OK',
                        allowOutsideClick: false
                    }).then(() => {
                        window.location.href = data.redirect || '{{ route('customer.reservation.history') }}';
                    });
                } else {
                    throw new Error(data.message || 'Terjadi kesalahan, silakan coba lagi.');
                }
            })
            .catch(error => {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal Mengajukan Reservasi',
                    text: error.message || 'Terjadi kesalahan pada server. Silakan coba lagi.',
                });
            })
            .finally(() => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fas fa-check me-1"></i> Konfirmasi Reservasi';
            });
        });

        // Update form submit to show review modal first
        document.getElementById('reservationForm').addEventListener('submit', function(e) {
            e.preventDefault();
            showReviewModal();
        });

        // Update price range info based on the response data
        function updatePriceRangeInfo(data) {
            const priceRangeInfo = document.getElementById('priceRangeInfo');

            console.log('🏷️ Updating price range info:', {
                start_time: data.price_start_time,
                end_time: data.price_end_time,
                day_type: data.price_day_type,
                is_overnight: data.is_overnight
            });

            if (data.price_start_time && data.price_end_time && data.price_day_type) {
                const dayTypeText = data.price_day_type === 'weekend' ? 'Akhir Pekan' :
                                   data.price_day_type === 'weekday' ? 'Hari Kerja' : 'Semua Hari';

                let rangeText = `${dayTypeText}, ${data.price_start_time} - ${data.price_end_time}`;

                if (data.is_overnight) {
                    rangeText += ' <span class="badge badge-warning">Melewati Tengah Malam</span>';
                }

                priceRangeInfo.innerHTML = rangeText;
            } else {
                priceRangeInfo.innerHTML = '';
            }
        }
    </script>
@endpush
