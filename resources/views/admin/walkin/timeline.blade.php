@extends('layouts.app')

@section('title', 'Timeline Meja')

@php
use App\Helpers\TimeHelper;
@endphp

@push('links')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <style>
        .timeline-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            padding: 20px;
            margin-bottom: 30px;
            overflow-x: auto;
            position: relative;
        }
        .timeline-header {
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            color: white;
            padding: 25px;
            border-radius: 12px;
            margin-bottom: 25px;
            position: relative;
            overflow: hidden;
        }
        .timeline-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(45deg, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0) 100%);
            z-index: 1;
        }
        .timeline-header > * {
            position: relative;
            z-index: 2;
        }
        .timeline-hours {
            display: flex;
            height: 45px;
            background: #f8f9fa;
            border-radius: 10px;
            margin-bottom: 15px;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            min-width: 1000px;
        }
        .timeline-hour {
            flex: 1;
            text-align: center;
            font-size: 14px;
            font-weight: 500;
            padding: 12px 0;
            border-right: 1px dashed #dee2e6;
            color: #4b5563;
            min-width: 70px;
            background: white;
            transition: all 0.3s ease;
        }
        .timeline-hour:hover {
            background: #f1f5f9;
        }
        .timeline-slots {
            position: relative;
            min-height: 250px;
            padding: 25px 0;
            background: #f8fafc;
            border-radius: 10px;
            min-width: 1000px;
            overflow: visible;
        }
        .timeline-slot {
            position: absolute;
            height: 70px;
            border-radius: 10px;
            padding: 10px 15px;
            font-size: 14px;
            font-weight: 500;
            color: white;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            cursor: pointer;
            border: 2px solid rgba(255,255,255,0.2);
            max-width: calc(100% - 20px);
        }
        .timeline-slot:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 12px rgba(0,0,0,0.15);
        }
        .timeline-slot-booked {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        }
        .timeline-slot-available {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        }
        .timeline-slot-maintenance {
            background: linear-gradient(135deg, #6b7280 0%, #4b5563 100%);
        }
        .timeline-legend {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin-top: 25px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 12px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        .legend-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 15px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        .legend-color {
            width: 24px;
            height: 24px;
            border-radius: 6px;
        }
        .timeline-info {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 20px;
            margin-top: 25px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        .timeline-info-item {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 15px;
            padding: 12px;
            background: white;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        .timeline-info-item:hover {
            transform: translateX(5px);
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        .timeline-info-item i {
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f1f5f9;
            border-radius: 8px;
            color: #4f46e5;
            font-size: 16px;
        }
        .date-selector {
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            margin-bottom: 25px;
        }
        .date-selector .form-control {
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            padding: 12px 15px;
            font-size: 15px;
            transition: all 0.3s ease;
        }
        .date-selector .form-control:focus {
            border-color: #4f46e5;
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        }
        .date-selector .btn {
            padding: 12px 25px;
            font-weight: 500;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        .date-selector .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .badge {
            padding: 6px 12px;
            font-weight: 500;
            border-radius: 6px;
        }
        .badge.bg-white {
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        @media (max-width: 768px) {
            .timeline-container {
                padding: 15px;
                margin: 0 -15px;
                border-radius: 0;
            }
            .timeline-header {
                padding: 20px;
            }
            .timeline-hours {
                min-width: 800px;
            }
            .timeline-slots {
                min-width: 800px;
            }
            .timeline-hour {
                min-width: 60px;
                font-size: 12px;
            }
            .timeline-slot {
                height: 60px;
                font-size: 12px;
                padding: 8px 12px;
                max-width: calc(100% - 16px);
            }
            .timeline-legend {
                gap: 10px;
                flex-wrap: wrap;
            }
            .legend-item {
                padding: 6px 12px;
                font-size: 13px;
            }
            .timeline-info-item {
                padding: 10px;
            }
            .timeline-info-item i {
                width: 25px;
                height: 25px;
                font-size: 14px;
            }
        }
    </style>
@endpush

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="page-title-box d-md-flex justify-content-md-between align-items-center">
                <h4 class="page-title">Timeline Meja</h4>
                <div class="">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.walkin.index') }}">Walk-in</a></li>
                        <li class="breadcrumb-item active">Timeline</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Date Selection -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="date-selector">
                <form id="dateForm" action="{{ route('admin.walkin.timeline', ['table' => $table->id]) }}" method="GET">
                    <div class="row align-items-center">
                        <div class="col-md-4">
                            <h5 class="mb-0">
                                <i class="fas fa-calendar-alt me-2"></i>
                                Pilih Tanggal
                            </h5>
                        </div>
                        <div class="col-md-6">
                            <input type="text" class="form-control flatpickr-date" id="selectedDate" name="date"
                                   value="{{ $selectedDate->format('Y-m-d') }}" placeholder="Pilih tanggal">
                        </div>
                        <div class="col-md-2">
                            <button class="btn btn-primary w-100" type="submit">
                                <i class="fas fa-search me-1"></i> Tampilkan
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Timeline (Replaced with List) -->
    <div class="row">
        <div class="col-12">
            <div class="timeline-container">
                <div class="timeline-header">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <h5 class="mb-2">
                                <i class="fas fa-table me-2"></i>
                                Meja #{{ $table->table_number }}
                            </h5>
                            <p class="mb-0">
                                <i class="fas fa-door-open me-2"></i>
                                {{ $table->room->name }}
                            </p>
                        </div>
                        <div class="col-md-6 text-md-end">
                            <span class="badge bg-{{ $table->current_status == 'available' ? 'success' : ($table->current_status == 'in_use' ? 'warning' : 'danger') }} p-2">
                                <i class="fas fa-circle me-1"></i>
                                {{ $table->current_status == 'available' ? 'Tersedia' : ($table->current_status == 'in_use' ? 'Digunakan' : 'Maintenance') }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Booking List -->
                <div class="mb-4 text-center">
                    <div style="font-size:2.5rem; font-weight:bold; letter-spacing:2px;" id="serverTimeDisplay"></div>
                    <div style="font-size:1.2rem; color:#6366f1; font-weight:500;">
                        <i class="fas fa-clock me-1"></i>Waktu Server Sekarang (Makassar) <span class="badge bg-info">WITA (UTC+8)</span>
                    </div>
                </div>
                <div class="table-responsive mt-2">
                    <table class="table table-bordered align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Customer</th>
                                <th>Waktu</th>
                                <th>Durasi</th>
                                <th>Status</th>
                                <th>Sisa Waktu</th>
                            </tr>
                        </thead>
                        <tbody>
                        @php $serverNow = now(); @endphp
                        @forelse($transactions as $trx)
                            @php
                                $duration = $trx->start_time->diffInMinutes($trx->end_time);
                            @endphp
                            <tr data-start="{{ $trx->start_time->format('Y-m-d\TH:i:s') }}" data-end="{{ $trx->end_time->format('Y-m-d\TH:i:s') }}">
                                <td>{{ $trx->customer->name }}</td>
                                <td>{{ $trx->start_time->format('H:i') }} - {{ $trx->end_time->format('H:i') }}</td>
                                <td>{{ TimeHelper::formatDuration($duration/60) }}</td>
                                <td class="status-cell">
                                    <span class="badge bg-info">Akan Datang</span>
                                </td>
                                <td class="countdown-cell">-</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted">Tidak ada booking hari ini</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="timeline-info mt-4">
                    <h6 class="mb-3">
                        <i class="fas fa-info-circle me-2"></i>
                        Informasi Meja
                    </h6>
                    <div class="timeline-info-item">
                        <i class="fas fa-tag"></i>
                        <div>
                            <small class="text-muted d-block">Brand</small>
                            <strong>{{ $table->brand }}</strong>
                        </div>
                    </div>
                    <div class="timeline-info-item">
                        <i class="fas fa-clock"></i>
                        <div>
                            <small class="text-muted d-block">Total Jam Terpakai</small>
                            <strong>{{ $totalHoursUsed }} jam</strong>
                        </div>
                    </div>
                    <div class="timeline-info-item">
                        <i class="fas fa-calendar-check"></i>
                        <div>
                            <small class="text-muted d-block">Total Transaksi</small>
                            <strong>{{ $transactions->count() }} transaksi</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        flatpickr(".flatpickr-date", {
            dateFormat: "Y-m-d",
            allowInput: true
        });

        // Simpan waktu server string ISO
        let serverNow = @json($serverNow->setTimezone('Asia/Makassar')->format('c'));
        let baseTimestamp = new Date(serverNow).getTime();
        let lastUpdate = Date.now();
        let serverNowString = serverNow;

        function pad(num) { return num.toString().padStart(2, '0'); }
        function formatDateTime(dt) {
            // Format: 14:36:51, 14-05-2025
            return `${pad(dt.getHours())}:${pad(dt.getMinutes())}:${pad(dt.getSeconds())}, ${pad(dt.getDate())}-${pad(dt.getMonth()+1)}-${dt.getFullYear()} WITA`;
        }

        function updateStatusAndCountdown() {
            // Calculate current server time (simulate ticking)
            let now = new Date(baseTimestamp + (Date.now() - lastUpdate));
            // Tampilkan waktu server sekarang dalam format besar
            const serverTimeDisplay = document.getElementById('serverTimeDisplay');
            if (serverTimeDisplay) {
                serverTimeDisplay.textContent = formatDateTime(now);
            }
            document.querySelectorAll('tbody tr[data-start][data-end]').forEach(function(row) {
                let start = new Date(row.getAttribute('data-start'));
                let end = new Date(row.getAttribute('data-end'));
                let statusCell = row.querySelector('.status-cell span');
                let countdownCell = row.querySelector('.countdown-cell');
                if (now < start) {
                    statusCell.className = 'badge bg-info';
                    statusCell.textContent = 'Akan Datang';
                    countdownCell.textContent = '-';
                } else if (now >= start && now < end) {
                    statusCell.className = 'badge bg-success';
                    statusCell.textContent = 'Sedang Berlangsung';
                    let diff = Math.floor((end - now) / 1000);
                    if (diff < 0) diff = 0;
                    let h = Math.floor(diff / 3600);
                    let m = Math.floor((diff % 3600) / 60);
                    let s = diff % 60;
                    countdownCell.textContent = `${pad(h)}:${pad(m)}:${pad(s)}`;
                } else {
                    statusCell.className = 'badge bg-secondary';
                    statusCell.textContent = 'Selesai';
                    countdownCell.textContent = '-';
                }
            });
        }
        updateStatusAndCountdown();
        setInterval(updateStatusAndCountdown, 1000);

        // Poll server time every 60 seconds to resync
        setInterval(function() {
            fetch("{{ route('admin.walkin.serverTime') }}")
                .then(res => res.json())
                .then(data => {
                    if (data.server_time) {
                        serverNow = data.server_time;
                        baseTimestamp = new Date(serverNow).getTime();
                        lastUpdate = Date.now();
                        serverNowString = serverNow;
                    }
                });
        }, 60000);

        // Tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl, {
                placement: 'top',
                trigger: 'hover'
            })
        });
    });
</script>
@endpush