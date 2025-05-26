@extends('layouts.app')

@section('title', 'Test Harga Berdasarkan Waktu')

@section('content')
<div class="row">
    <div class="col-sm-12">
        <div class="page-title-box d-md-flex justify-content-md-between align-items-center">
            <h4 class="page-title">Test Harga Berdasarkan Waktu</h4>
            <div class="">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="#">Master</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.prices.index') }}">Harga</a></li>
                    <li class="breadcrumb-item active">Test Harga</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">Cek Harga Berdasarkan Waktu</h4>
            </div>
            <div class="card-body">
                <div class="text-center mb-4">
                    <button type="button" id="currentTimeBtn" class="btn btn-lg btn-success">
                        <i class="fas fa-clock me-1"></i> Gunakan Waktu Saat Ini
                    </button>
                </div>

                <form action="{{ route('admin.prices.test') }}" method="GET" id="priceTestForm">
                    <div class="mb-3 row">
                        <label for="table_id" class="col-sm-3 col-form-label text-end">Meja</label>
                        <div class="col-sm-9">
                            <select class="form-select" id="table_id" name="table_id" required>
                                <option value="">Pilih Meja</option>
                                @foreach($tables as $table)
                                    <option value="{{ $table->id }}" {{ request('table_id') == $table->id ? 'selected' : '' }}>
                                        {{ $table->table_number }} - {{ $table->room->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="mb-3 row">
                        <label for="selected_date" class="col-sm-3 col-form-label text-end">Tanggal</label>
                        <div class="col-sm-9">
                            <input type="date" class="form-control" id="selected_date" name="selected_date"
                                value="{{ request('selected_date', date('Y-m-d')) }}" required>
                        </div>
                    </div>

                    <div class="mb-3 row">
                        <label for="selected_time" class="col-sm-3 col-form-label text-end">Waktu</label>
                        <div class="col-sm-9">
                            <input type="time" class="form-control" id="selected_time" name="selected_time"
                                value="{{ request('selected_time', date('H:i')) }}" required>
                        </div>
                    </div>

                    <div class="mb-3 row">
                        <label for="duration" class="col-sm-3 col-form-label text-end">Durasi (jam)</label>
                        <div class="col-sm-9">
                            <input type="number" class="form-control" id="duration" name="duration"
                                value="{{ request('duration', 2) }}" min="1" max="24" required>
                        </div>
                    </div>

                    <div class="mb-3 row">
                        <div class="col-sm-9 offset-sm-3">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search me-1"></i> Cek Harga
                            </button>
                            <a href="{{ route('admin.prices.index') }}" class="btn btn-secondary ms-2">
                                <i class="fas fa-arrow-left me-1"></i> Kembali
                            </a>
                        </div>
                    </div>
                </form>

                @if($result)
                    <hr>
                    <div class="mt-4">
                        <h5 class="mb-3">Hasil Pencarian:</h5>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <tbody>
                                    <tr>
                                        <th width="30%">Meja</th>
                                        <td>
                                            @foreach($tables as $table)
                                                @if($table->id == $result['table_id'])
                                                    {{ $table->table_number }} - {{ $table->room->name }}
                                                @endif
                                            @endforeach
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Tanggal</th>
                                        <td>{{ \Carbon\Carbon::parse($result['selected_date'])->format('d F Y') }}
                                            ({{ $result['day_type'] == 'weekend' ? 'Akhir Pekan' : 'Hari Kerja' }})
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Waktu</th>
                                        <td>{{ $result['selected_time'] }}</td>
                                    </tr>
                                    @if(isset($result['duration']))
                                    <tr>
                                        <th>Durasi & Akhir Sesi</th>
                                        <td>
                                            @php
                                                $startDateTime = \Carbon\Carbon::parse($result['selected_date'] . ' ' . $result['selected_time']);
                                                $endDateTime = $startDateTime->copy()->addHours($result['duration']);
                                                $differentDay = $startDateTime->format('Y-m-d') !== $endDateTime->format('Y-m-d');
                                            @endphp
                                            <strong>{{ $result['duration'] }} jam</strong>
                                            <br>
                                            <span>
                                                Mulai: {{ $startDateTime->format('d M Y H:i') }}
                                                <br>
                                                Selesai: {{ $endDateTime->format('d M Y H:i') }}
                                                @if($differentDay)
                                                    <span class="badge bg-info">Berlanjut ke Hari Berikutnya</span>
                                                @endif
                                            </span>
                                        </td>
                                    </tr>
                                    @endif
                                    <tr>
                                        <th>Harga yang Berlaku</th>
                                        <td>
                                            @if($result['price'])
                                                <div class="alert alert-success mb-0">
                                                    <strong>Rp {{ number_format($result['price']->price, 0, ',', '.') }}</strong>
                                                    <br>
                                                    @php
                                                        $startTime = \Carbon\Carbon::parse($result['price']->start_time);
                                                        $endTime = \Carbon\Carbon::parse($result['price']->end_time);
                                                        $isOvernightRange = $startTime->format('H:i') > $endTime->format('H:i');
                                                    @endphp
                                                    <small>Waktu: {{ $startTime->format('H:i') }} -
                                                        {{ $endTime->format('H:i') }}
                                                        @if($isOvernightRange)
                                                            <span class="badge bg-warning">Melewati Tengah Malam</span>
                                                        @endif
                                                    </small>
                                                </div>

                                                <!-- Debug info untuk overnight -->
                                                @if($isOvernightRange)
                                                <div class="alert alert-info mt-2">
                                                    <strong>Debug Info (Overnight Range)</strong>
                                                    <p class="mb-1">Range waktu ini melewati tengah malam, yang berarti:</p>
                                                    <ul class="mb-0">
                                                        <li>Kondisi: <code>{{ $startTime->format('H:i') }} > {{ $endTime->format('H:i') }}</code></li>
                                                        <li>Waktu saat ini: <code>{{ now()->format('H:i') }}</code></li>
                                                        <li>Waktu terpilih: <code>{{ $result['selected_time'] }}</code></li>
                                                        <li>Range ini berlaku jika:
                                                            <ol>
                                                                <li>Waktu >= {{ $startTime->format('H:i') }} <b>ATAU</b></li>
                                                                <li>Waktu <= {{ $endTime->format('H:i') }}</li>
                                                            </ol>
                                                        </li>
                                                        @php
                                                            $timeNow = \Carbon\Carbon::createFromFormat('H:i', now()->format('H:i'));
                                                            $selectedTime = \Carbon\Carbon::createFromFormat('H:i', substr($result['selected_time'], 0, 5));

                                                            $timeNowAfterStart = $timeNow->format('H:i') >= $startTime->format('H:i');
                                                            $timeNowBeforeEnd = $timeNow->format('H:i') <= $endTime->format('H:i');
                                                            $timeNowInRange = $timeNowAfterStart || $timeNowBeforeEnd;

                                                            $selectedTimeAfterStart = $selectedTime->format('H:i') >= $startTime->format('H:i');
                                                            $selectedTimeBeforeEnd = $selectedTime->format('H:i') <= $endTime->format('H:i');
                                                            $selectedTimeInRange = $selectedTimeAfterStart || $selectedTimeBeforeEnd;
                                                        @endphp
                                                        <li>Waktu saat ini dalam range: <span class="badge bg-{{ $timeNowInRange ? 'success' : 'danger' }}">{{ $timeNowInRange ? 'Ya' : 'Tidak' }}</span></li>
                                                        <li>Waktu terpilih dalam range: <span class="badge bg-{{ $selectedTimeInRange ? 'success' : 'danger' }}">{{ $selectedTimeInRange ? 'Ya' : 'Tidak' }}</span></li>
                                                    </ul>
                                                </div>
                                                @endif
                                            @else
                                                <div class="alert alert-danger mb-0">
                                                    <strong>Tidak ada harga yang tersedia</strong> untuk waktu dan meja yang dipilih.
                                                </div>
                                            @endif
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Current time button handler
        document.getElementById('currentTimeBtn').addEventListener('click', function() {
            // Get current server time via AJAX
            fetch('{{ route('admin.prices.get-current-time') }}')
                .then(response => response.json())
                .then(data => {
                    // Update form with current date and time
                    document.getElementById('selected_date').value = data.current_date;
                    document.getElementById('selected_time').value = data.current_time;

                    // Check if a table is selected
                    const tableSelect = document.getElementById('table_id');
                    if (tableSelect.value === "") {
                        // Select the first table if none selected
                        if (tableSelect.options.length > 1) {
                            tableSelect.selectedIndex = 1;
                        }
                    }

                    // Submit the form automatically
                    document.getElementById('priceTestForm').submit();
                })
                .catch(error => {
                    console.error('Error fetching current time:', error);
                    alert('Gagal mendapatkan waktu saat ini. Silakan coba lagi.');
                });
        });
    });
</script>
@endpush
