<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Table;
use App\Models\Reservation;
use App\Models\Price;
use App\Models\Payment;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Midtrans\Config;
use Midtrans\Snap;
use App\Services\TimelineService;
use App\Services\PriceService;
use Illuminate\Support\Facades\Validator;
use App\Events\NewReservationNotification;
use App\Models\Notification;
use App\Models\Promo;

class ReservationController extends Controller
{
    public function __construct()
    {
        // Set Midtrans configuration
        Config::$serverKey = config('services.midtrans.server_key');
        Config::$isProduction = config('services.midtrans.is_production');
        Config::$isSanitized = true;
        Config::$is3ds = true;
    }

    public function index(Request $request)
    {
        try {
            // Ambil semua meja beserta slot free untuk hari ini (atau tanggal terpilih)
            $selectedDate = $request->date ? Carbon::parse($request->date) : Carbon::today();

            // Query dasar untuk meja
            $query = Table::with(['room', 'prices' => function($query) use ($selectedDate) {
                $query->where('status', true)
                    ->where(function($q) use ($selectedDate) {
                        $q->whereNull('valid_from')
                            ->orWhere('valid_from', '<=', $selectedDate);
                    })
                    ->where(function($q) use ($selectedDate) {
                        $q->whereNull('valid_until')
                            ->orWhere('valid_until', '>=', $selectedDate);
                    });
            }]);

            // Filter berdasarkan tipe ruangan
            if ($request->type) {
                $query->whereHas('room', function($q) use ($request) {
                    $q->where('type', $request->type);
                });
            }

            $tables = $query->get();

            // Get user's reservations with notifications
            $reservations = Reservation::with(['table', 'table.room'])
                ->where('customer_id', Auth::user()->customer->id)
                ->latest()
                ->get();

            foreach ($tables as $table) {
                // Ambil semua reservation (pending, approved, paid) untuk meja ini di tanggal terpilih
                $tableReservations = Reservation::where('table_id', $table->id)
                    ->whereIn('status', ['pending', 'approved', 'paid'])
                    ->whereDate('start_time', $selectedDate)
                    ->orderBy('start_time')
                    ->get();

                // Tentukan slot free (08:00-23:00, 1 jam)
                $operatingStart = 8;
                $operatingEnd = 23;
                $dateStr = $selectedDate->format('Y-m-d');
                $slots = [];

                for ($hour = $operatingStart; $hour < $operatingEnd; $hour++) {
                    $slotStart = Carbon::parse("$dateStr " . sprintf('%02d:00:00', $hour));
                    $slotEnd = Carbon::parse("$dateStr " . sprintf('%02d:00:00', $hour+1));

                    // Cek overlap dengan reservation
                    $overlap = false;
                    foreach ($tableReservations as $res) {
                        if ($slotStart < $res->end_time && $slotEnd > $res->start_time) {
                            $overlap = true;
                            break;
                        }
                    }

                    if (!$overlap) {
                        // Get price for this slot
                        $price = $table->prices->first(function($price) use ($slotStart) {
                            return $price->start_time->format('H:i:s') <= $slotStart->format('H:i:s')
                                && $price->end_time->format('H:i:s') >= $slotStart->format('H:i:s')
                                && $price->day_type === ($slotStart->isWeekend() ? 'weekend' : 'weekday');
                        });

                        $slots[] = [
                            'start' => $slotStart->format('H:i'),
                            'end' => $slotEnd->format('H:i'),
                            'price' => $price ? $price->price : 0
                        ];
                    }
                }
                // Tambahkan slot ke table sebagai attribute dinamis
                $table->forceFill(['available_slots' => $slots]);
            }

            return view('customer.reservation.index', compact('tables', 'selectedDate', 'reservations'));
        } catch (\Exception $e) {
            Log::error('Error in reservation index', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return view('customer.reservation.index', [
                'tables' => collect(),
                'selectedDate' => Carbon::today(),
                'reservations' => collect(),
                'error' => 'Terjadi kesalahan saat memuat data. Silakan coba lagi.'
            ]);
        }
    }

    public function store(Request $request)
    {
        try {
            // Log the entire request
            Log::debug('Reservation Store - Request received', [
                'all_params' => $request->all(),
                'table_id' => $request->table_id,
                'start_time' => $request->start_time,
                'end_time' => $request->end_time,
                'headers' => [
                    'ajax' => $request->ajax(),
                    'wants_json' => $request->wantsJson(),
                    'X-Requested-With' => $request->header('X-Requested-With')
                ],
                'ip' => $request->ip(),
                'route' => $request->route()->getName(),
                'user_id' => auth()->id(),
                'user_email' => auth()->user()->email ?? 'no-email'
            ]);

            // Fix the start_time format if it's just a time
            $requestData = $request->all();

            // Check if start_time doesn't match Y-m-d H:i format and we have a date parameter
            if ($request->has('date') && $request->has('start_time') && !preg_match('/^\d{4}-\d{2}-\d{2}\s\d{2}:\d{2}$/', $request->start_time)) {
                Log::debug('Reservation Store - Fixing start_time format', [
                    'original_start_time' => $request->start_time,
                    'date' => $request->date
                ]);

                $requestData['start_time'] = $request->date . ' ' . $request->start_time;

                Log::debug('Reservation Store - Fixed start_time', [
                    'fixed_start_time' => $requestData['start_time']
                ]);
            }

            $validator = Validator::make($requestData, [
                'table_id' => 'required|exists:tables,id',
                'start_time' => 'required|date_format:Y-m-d H:i',
                'end_time' => 'required|date_format:Y-m-d H:i',
            ]);

            Log::debug('Reservation Store - Validation run', [
                'passes' => !$validator->fails(),
                'errors' => $validator->errors()->toArray() ?: 'no errors'
            ]);

            if ($validator->fails()) {
                Log::debug('Reservation Store - Validation failed', [
                    'errors' => $validator->errors()->toArray()
                ]);

                if ($request->ajax() || $request->wantsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
                    return response()->json([
                        'success' => false,
                        'message' => 'Validasi gagal',
                        'errors' => $validator->errors()
                    ], 422);
                }

                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }

            // Manual validation for start/end time
            try {
                $startDateTime = Carbon::parse($requestData['start_time']);
                $endDateTime = Carbon::parse($requestData['end_time']);

                Log::debug('Reservation Store - Date parsing successful', [
                    'start_original' => $requestData['start_time'],
                    'end_original' => $requestData['end_time'],
                    'start_parsed' => $startDateTime->format('Y-m-d H:i:s'),
                    'end_parsed' => $endDateTime->format('Y-m-d H:i:s')
                ]);
            } catch (\Exception $e) {
                Log::error('Reservation Store - Date parsing failed', [
                    'start_time' => $requestData['start_time'],
                    'end_time' => $requestData['end_time'],
                    'error' => $e->getMessage()
                ]);

                $error = ['date' => ['Format tanggal tidak valid.']];

                if ($request->ajax() || $request->wantsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
                    return response()->json([
                        'success' => false,
                        'message' => 'Format tanggal tidak valid',
                        'errors' => $error
                    ], 422);
                }

                return redirect()->back()
                    ->withErrors($error)
                    ->withInput();
            }

            // Handle overnight bookings by adding a day if end time is earlier than start time
            if ($endDateTime->format('H:i') < $startDateTime->format('H:i')) {
                Log::debug('Reservation Store - Detected overnight booking', [
                    'start_time' => $startDateTime->format('Y-m-d H:i:s'),
                    'end_time_before' => $endDateTime->format('Y-m-d H:i:s'),
                ]);

                $endDateTime->addDay();

                Log::debug('Reservation Store - Adjusted end time for overnight', [
                    'end_time_after' => $endDateTime->format('Y-m-d H:i:s'),
                ]);
            }

            // Check that end time is after start time
            if ($endDateTime->lessThanOrEqualTo($startDateTime)) {
                Log::debug('Reservation Store - End time not after start time', [
                    'start_time' => $startDateTime->format('Y-m-d H:i:s'),
                    'end_time' => $endDateTime->format('Y-m-d H:i:s'),
                    'comparison' => $endDateTime->lessThanOrEqualTo($startDateTime)
                ]);

                $error = ['end_time' => ['Waktu selesai harus setelah waktu mulai.']];

                if ($request->ajax() || $request->wantsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
                    return response()->json([
                        'success' => false,
                        'message' => 'Validasi gagal',
                        'errors' => $error
                    ], 422);
                }

                return redirect()->back()
                    ->withErrors($error)
                    ->withInput();
            }

            try {
                $table = Table::findOrFail($requestData['table_id']);
                Log::debug('Reservation Store - Table found', [
                    'table_id' => $table->id,
                    'table_number' => $table->table_number,
                    'room_id' => $table->room_id
                ]);
            } catch (\Exception $e) {
                Log::error('Reservation Store - Table not found', [
                    'table_id' => $requestData['table_id'],
                    'error' => $e->getMessage()
                ]);

                if ($request->ajax() || $request->wantsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
                    return response()->json([
                        'success' => false,
                        'message' => 'Meja tidak ditemukan'
                    ], 422);
                }

                return redirect()->back()
                    ->with('error', 'Meja tidak ditemukan')
                    ->withInput();
            }

            $start = $startDateTime;
            $end = $endDateTime;

            // Validate table availability
            $conflictingReservations = Reservation::where('table_id', $table->id)
                ->whereIn('status', ['pending', 'approved', 'paid'])
                ->where(function($q) use ($start, $end) {
                    $q->where('start_time', '<', $end)
                    ->where('end_time', '>', $start);
                })->get();

            Log::debug('Reservation Store - Availability check', [
                'has_conflicts' => $conflictingReservations->isNotEmpty(),
                'conflicting_count' => $conflictingReservations->count(),
                'conflicts' => $conflictingReservations->map(function($res) {
                    return [
                        'id' => $res->id,
                        'start' => $res->start_time,
                        'end' => $res->end_time,
                        'status' => $res->status
                    ];
                })
            ]);

            if ($conflictingReservations->isNotEmpty()) {
                if ($request->ajax() || $request->wantsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
                    return response()->json([
                        'success' => false,
                        'message' => 'Meja sudah dipesan untuk waktu tersebut.'
                    ], 422);
                }

                return redirect()->back()
                    ->with('error', 'Meja sudah dipesan untuk waktu tersebut.')
                    ->withInput();
            }

            // Calculate total price
            $totalHours = $end->diffInHours($start);

            Log::debug('Reservation Store - Duration calculation', [
                'start_time' => $start->format('Y-m-d H:i:s'),
                'end_time' => $end->format('Y-m-d H:i:s'),
                'total_hours' => $totalHours
            ]);

            try {
                $priceModel = \App\Services\PriceService::getPriceForTable($table, $start);

                Log::debug('Reservation Store - Price calculation', [
                    'price_service_result' => $priceModel ? 'price found' : 'no price found',
                    'price_model' => $priceModel ? get_class($priceModel) : 'null',
                    'price_per_hour' => $priceModel ? $priceModel->price : 0
                ]);
            } catch (\Exception $e) {
                Log::error('Reservation Store - Price calculation error', [
                    'table_id' => $table->id,
                    'error' => $e->getMessage()
                ]);

                if ($request->ajax() || $request->wantsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
                    return response()->json([
                        'success' => false,
                        'message' => 'Terjadi kesalahan saat menghitung harga.'
                    ], 500);
                }

                return redirect()->back()
                    ->with('error', 'Terjadi kesalahan saat menghitung harga.')
                    ->withInput();
            }

            if (!$priceModel) {
                Log::warning('Reservation Store - No price found', [
                    'table_id' => $table->id,
                    'start_time' => $start->format('Y-m-d H:i:s')
                ]);

                if ($request->ajax() || $request->wantsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
                    return response()->json([
                        'success' => false,
                        'message' => 'Tidak dapat menentukan harga untuk waktu tersebut.'
                    ], 422);
                }

                return redirect()->back()
                    ->with('error', 'Tidak dapat menentukan harga untuk waktu tersebut.')
                    ->withInput();
            }

            // Extract the actual price value from the Price model
            $pricePerHour = $priceModel->price;
            $totalPrice = $pricePerHour * $totalHours;

            Log::debug('Reservation Store - Total price calculation', [
                'price_per_hour' => $pricePerHour,
                'hours' => $totalHours,
                'total_price' => $totalPrice
            ]);

            // Create reservation
            try {
                $reservationCode = 'RSV-' . strtoupper(uniqid());

                $reservationData = [
                    'customer_id' => Auth::user()->customer->id,
                    'table_id' => $table->id,
                    'start_time' => $start,
                    'end_time' => $end,
                    'status' => 'pending',
                    'total_price' => $totalPrice,
                    'duration_hours' => $totalHours,
                    'price_per_hour' => $pricePerHour,
                    'reservation_code' => $reservationCode,
                ];

                Log::debug('Reservation Store - Creating reservation', $reservationData);

                $reservation = Reservation::create($reservationData);

                Log::debug('Reservation Store - Reservation created successfully', [
                    'reservation_id' => $reservation->id,
                    'reservation_code' => $reservation->reservation_code
                ]);

                // Create and dispatch notification for admin
                try {
                                        // Create notification record
                    $notification = new Notification();
                    $notification->type = 'reservation';
                    $notification->message = 'New reservation request: Table #' . $table->table_number . ' (' . $table->room->name . ') from ' . Auth::user()->name;
                    $notification->status = 'unread';
                    $notification->reservation_id = $reservation->id; // Store reservation ID in the proper column

                    // Store additional data in the data column
                    $notification->data = [
                        'reservation_id' => $reservation->id,
                        'table_number' => $table->table_number,
                        'room_name' => $table->room->name,
                        'start_time' => $start->format('Y-m-d H:i:s'),
                        'end_time' => $end->format('Y-m-d H:i:s'),
                        'customer_name' => Auth::user()->name
                    ];

                    $notification->save();

                    // Log notification creation with ID
                    Log::debug('Notification created with data', [
                        'notification_id' => $notification->id,
                        'reservation_id' => $reservation->id,
                        'message' => $notification->message,
                        'has_data' => !empty($notification->data)
                    ]);

                    // Dispatch event with notification and reservation data
                    event(new NewReservationNotification($notification, $reservation));

                    Log::debug('Reservation Store - Notification created and event dispatched', [
                        'notification_id' => $notification->id,
                        'type' => 'reservation',
                        'reservation_id' => $reservation->id
                    ]);
                } catch (\Exception $e) {
                    // Don't stop the process if notification fails
                    Log::error('Reservation Store - Failed to create notification', [
                        'error' => $e->getMessage(),
                        'reservation_id' => $reservation->id
                    ]);
                }
            } catch (\Exception $e) {
                Log::error('Reservation Store - Reservation creation failed', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);

                if ($request->ajax() || $request->wantsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
                    return response()->json([
                        'success' => false,
                        'message' => 'Gagal membuat reservasi: ' . $e->getMessage()
                    ], 500);
                }

                return redirect()->back()
                    ->with('error', 'Gagal membuat reservasi: ' . $e->getMessage())
                    ->withInput();
            }

            Log::debug('Reservation Store - Preparing response', [
                'is_ajax' => $request->ajax(),
                'wants_json' => $request->wantsJson(),
                'x_requested_with' => $request->header('X-Requested-With')
            ]);

            if ($request->ajax() || $request->wantsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
                Log::debug('Reservation Store - Returning JSON response');
                return response()->json([
                    'success' => true,
                    'message' => 'Reservasi berhasil dibuat. Silakan tunggu persetujuan admin.',
                    'redirect' => route('customer.reservation.show', $reservation->id)
                ]);
            }

            Log::debug('Reservation Store - Returning redirect response');
            return redirect()->route('customer.reservation.show', $reservation->id)
                ->with('success', 'Reservasi berhasil dibuat. Silakan tunggu persetujuan admin.');

        } catch (\Exception $e) {
            Log::error('Reservation Store - Unhandled exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request' => $request->all()
            ]);

            if ($request->ajax() || $request->wantsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
                return response()->json([
                    'success' => false,
                    'message' => 'Terjadi kesalahan: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function pay($id)
    {
        $reservation = Reservation::with(['table', 'table.room', 'customer'])
            ->where('customer_id', Auth::user()->customer->id)
            ->findOrFail($id);

        Log::channel('midtrans')->info('Customer accessing payment page', [
            'reservation_id' => $reservation->id,
            'customer_id' => Auth::user()->customer->id,
            'current_status' => $reservation->status,
            'payment_token' => $reservation->payment_token ? 'exists' : 'missing',
            'payment_expired_at' => $reservation->payment_expired_at
        ]);

        // Auto expire jika sudah lewat payment_expired_at dan belum paid
        if ($reservation->status === 'approved' && $reservation->payment_expired_at && now()->gt($reservation->payment_expired_at)) {
            $reservation->status = 'expired';
            $reservation->save();

            Log::channel('midtrans')->warning('Payment session expired', [
                'reservation_id' => $reservation->id,
                'expired_at' => $reservation->payment_expired_at,
                'current_time' => now()
            ]);

            return redirect()->route('customer.reservation.index')
                ->with('error', 'Waktu pembayaran telah habis.');
        }

        if ($reservation->status !== 'approved') {
            Log::channel('midtrans')->warning('Invalid reservation status for payment', [
                'reservation_id' => $reservation->id,
                'status' => $reservation->status
            ]);

            return redirect()->route('customer.reservation.index')
                ->with('error', 'Reservasi tidak valid untuk pembayaran.');
        }

        if ($reservation->status === 'paid') {
            Log::channel('midtrans')->info('Reservation already paid', [
                'reservation_id' => $reservation->id
            ]);

            return redirect()->route('customer.transaction.index')
                ->with('info', 'Reservasi ini sudah dibayar.');
        }

        // Ambil Snap Token dari kolom payment_token (hasil generate admin)
        $snapToken = $reservation->payment_token;
        if (!$snapToken) {
            Log::channel('midtrans')->error('Payment token not found', [
                'reservation_id' => $reservation->id,
                'status' => $reservation->status
            ]);

            return redirect()->route('customer.reservation.index')
                ->with('error', 'Pembayaran belum dapat dilakukan. Menunggu persetujuan admin.');
        }

        Log::channel('midtrans')->info('Displaying payment page', [
            'reservation_id' => $reservation->id,
            'snap_token_length' => strlen($snapToken),
            'expires_in_minutes' => $reservation->payment_expired_at ? now()->diffInMinutes($reservation->payment_expired_at, false) : null
        ]);

        return view('customer.reservation.pay', compact('reservation', 'snapToken'));
    }

    public function paymentCallback(Request $request, $id)
    {
        // Log the incoming request data
        Log::info('Payment callback received', [
            'reservation_id' => $id,
            'request_data' => $request->all(),
        ]);

        try {
        // Validasi data yang diterima
        $validator = Validator::make($request->all(), [
            'transaction_status' => 'required|string',
            'order_id' => 'required|string',
                'payment_type' => 'required|string',
                'gross_amount' => 'required|string',
        ]);

        if ($validator->fails()) {
            Log::error('Payment callback validation failed', [
                'errors' => $validator->errors(),
            ]);
                return response()->json(['success' => false, 'message' => 'Invalid payment data'], 400);
        }

        // Proses pembayaran di sini
        $reservation = Reservation::findOrFail($id);

            // Check if payment is successful
            if (in_array($request->transaction_status, ['capture', 'settlement'])) {
                DB::beginTransaction();
                try {
            $reservation->status = 'paid';
                    $reservation->status_paid_at = now();
                    $reservation->payment_details = json_encode($request->all());
                    $reservation->save();

            // Cek apakah sudah ada transaksi untuk reservasi ini
            $existing = \App\Models\Transaction::where('transaction_type', 'reservation')
                ->where('start_time', $reservation->start_time)
                ->where('end_time', $reservation->end_time)
                ->where('table_id', $reservation->table_id)
                ->where('customer_id', $reservation->customer_id)
                ->first();

            if (!$existing) {
                $transactionCode = 'RESV-' . strtoupper(Str::random(8));
                while (\App\Models\Transaction::where('transaction_code', $transactionCode)->exists()) {
                    $transactionCode = 'RESV-' . strtoupper(Str::random(8));
                }

                        $transaction = \App\Models\Transaction::create([
                    'customer_id' => $reservation->customer_id,
                    'table_id' => $reservation->table_id,
                            'user_id' => $reservation->approved_by,
                    'transaction_type' => 'reservation',
                    'start_time' => $reservation->start_time,
                    'end_time' => $reservation->end_time,
                    'status' => 'paid',
                    'total_price' => $reservation->total_price,
                    'payment_method' => 'e_payment',
                    'transaction_code' => $transactionCode,
                            'payment_details' => json_encode($request->all()),
                            'reservation_id' => $reservation->id
                        ]);

                        // Create payment record
                        \App\Models\Payment::create([
                            'transaction_id' => $transaction->id,
                            'payment_method' => 'e_payment',
                            'total_amount' => $reservation->total_price,
                            'amount_paid' => $reservation->total_price,
                            'change_amount' => 0,
                            'status' => 'paid',
                            'payment_date' => now(),
                            'payment_details' => json_encode($request->all()),
                        ]);
                    }

                    DB::commit();
                    Log::info('Payment processed successfully', [
                        'reservation_id' => $id,
                        'new_status' => $reservation->status,
                    ]);

                    return response()->json(['success' => true, 'message' => 'Payment processed successfully']);
                } catch (\Exception $e) {
                    DB::rollBack();
                    Log::error('Error processing payment', [
                        'reservation_id' => $id,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                    return response()->json(['success' => false, 'message' => 'Error processing payment: ' . $e->getMessage()], 500);
            }
        } else {
            Log::warning('Payment not settled', [
                'reservation_id' => $id,
                'transaction_status' => $request->transaction_status,
            ]);
                return response()->json(['success' => false, 'message' => 'Payment not settled'], 400);
            }
        } catch (\Exception $e) {
            Log::error('Payment callback error', [
                'reservation_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['success' => false, 'message' => 'Server error: ' . $e->getMessage()], 500);
        }
    }

    public function getNotifications()
    {
        $customer = Auth::user()->customer;

        // Ambil semua notifikasi yang belum dibaca
        $unreadReservations = Reservation::where('customer_id', $customer->id)
            ->where('notified', false)
            ->whereIn('status', ['approved', 'rejected', 'expired'])
            ->get();

        $notifications = [];

        foreach ($unreadReservations as $reservation) {
            $message = '';
            if ($reservation->status === 'approved') {
                $message = "Reservasi meja #{$reservation->table->table_number} telah disetujui. Silakan lakukan pembayaran dalam 3 menit.";
            } elseif ($reservation->status === 'rejected') {
                $message = "Reservasi meja #{$reservation->table->table_number} ditolak.";
            } elseif ($reservation->status === 'expired') {
                $message = "Waktu pembayaran untuk reservasi meja #{$reservation->table->table_number} telah habis.";
            }

            $notifications[] = [
                'id' => $reservation->id,
                'message' => $message,
                'status' => $reservation->status,
                'time' => $reservation->updated_at->diffForHumans()
            ];

            // Mark as notified
            $reservation->notified = true;
            $reservation->save();
        }

        return response()->json($notifications);
    }

    /**
     * Get table timeline data for the reservation modal
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getTableTimeline(Request $request)
    {
        $request->validate([
            'table_id' => 'required|exists:tables,id',
            'date' => 'required|date'
        ]);

        $tableId = $request->table_id;
        $date = Carbon::parse($request->date);
        $table = Table::with(['room'])->findOrFail($tableId);

        // Ambil jam dari request (default 08:00)
        $startTime = $request->has('start_time') ? $request->start_time : '08:00';
        $startDateTime = Carbon::parse($date->format('Y-m-d') . ' ' . $startTime);

        Log::info('🔍 ReservationController: Mencari harga untuk waktu', [
            'table_id' => $tableId,
            'date' => $date->format('Y-m-d'),
            'start_time' => $startTime,
            'start_datetime' => $startDateTime->format('Y-m-d H:i:s')
        ]);

        $price = PriceService::getPriceForTable($table, $startDateTime);
        $pricePerHour = $price ? $price->price : 0;

        // Debug information
        if ($price) {
            $priceStartTime = Carbon::parse($price->start_time)->format('H:i');
            $priceEndTime = Carbon::parse($price->end_time)->format('H:i');
            $isOvernight = $priceStartTime > $priceEndTime;

            Log::info('💰 ReservationController: Harga yang digunakan', [
                'price_id' => $price->id,
                'day_type' => $price->day_type,
                'start_time' => $priceStartTime,
                'end_time' => $priceEndTime,
                'is_overnight' => $isOvernight,
                'price' => $price->price
            ]);
        } else {
            Log::warning('⚠️ ReservationController: Tidak ada harga yang ditemukan', [
                'table_id' => $tableId,
                'start_time' => $startTime
            ]);
        }

        // Get all existing transactions for this table on this date (semua status: pending, approved, paid)
        $reservations = Reservation::where('table_id', $tableId)
            ->whereIn('status', ['approved', 'paid', 'pending'])
            ->whereDate('start_time', $date)
            ->with('customer')
            ->get();

        $transactions = [];
        foreach ($reservations as $reservation) {
            $transactions[] = [
                'id' => $reservation->id,
                'customer_name' => $reservation->customer ? $reservation->customer->name : '-',
                'start_time' => $reservation->start_time->format('H:i'),
                'end_time' => $reservation->end_time->format('H:i'),
                'status' => $reservation->status,
            ];
        }

        // Menentukan apakah harga menggunakan rentang overnight
        $isOvernight = false;
        $priceStartTime = null;
        $priceEndTime = null;
        $priceDayType = null;

        if ($price) {
            $priceStartTime = Carbon::parse($price->start_time)->format('H:i');
            $priceEndTime = Carbon::parse($price->end_time)->format('H:i');
            $priceDayType = $price->day_type;
            $isOvernight = $priceStartTime > $priceEndTime;
        }

        return response()->json([
            'table_id' => $tableId,
            'table_number' => $table->table_number,
            'room_name' => $table->room->name,
            'price_per_hour' => $pricePerHour,
            'transactions' => $transactions,
            'is_overnight' => $isOvernight,
            'price_start_time' => $priceStartTime,
            'price_end_time' => $priceEndTime,
            'price_day_type' => $priceDayType
        ]);
    }

    public function create(Request $request)
    {
        try {
            // Accept both 'table' and 'table_id' parameters
            $tableId = $request->table ?? $request->table_id ?? null;

            // If no table ID is provided, show all available tables
            if (!$tableId) {
                // Get available tables
                $tables = Table::with('room')->where('status', 'available')->get();

                if ($tables->isEmpty()) {
                    return redirect()->route('customer.reservation.index')
                        ->with('error', 'Tidak ada meja yang tersedia saat ini.');
                }

                $selectedDate = $request->date ? Carbon::parse($request->date) : Carbon::today();
                return view('customer.reservation.index', compact('tables', 'selectedDate'));
            }

            // Validate table ID
            $table = Table::with('room')->find($tableId);

            if (!$table) {
                Log::warning('Table not found', ['table_id' => $tableId]);
                return redirect()->route('customer.reservation.index')
                    ->with('error', 'Meja tidak ditemukan. Silakan pilih meja lain.');
            }

            $selectedDate = $request->date ? Carbon::parse($request->date) : Carbon::today();
            return view('customer.reservation.create', compact('table', 'selectedDate'));
        } catch (\Exception $e) {
            Log::error('Error in reservation create', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request' => $request->all()
            ]);

            return redirect()->route('customer.reservation.index')
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function timeline(Request $request)
    {
        try {
            $tableId = $request->table_id;
            $date = $request->date ? Carbon::parse($request->date) : Carbon::today();
            $data = TimelineService::getTimelineData($tableId, $date);

            $transactions = [];
            foreach ($data['transactions'] as $trx) {
                $transactions[] = [
                    'customer_name' => $trx['customer_name'],
                    'start_time' => $trx['start_time'],
                    'end_time' => $trx['end_time'],
                    'status' => $trx['status'],
                    'source' => $trx['source'] ?? null,
                ];
            }
            return response()->json([
                'table' => $data['table']->table_number,
                'transactions' => $transactions,
                'total_hours_used' => $data['totalHoursUsed'],
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'error' => true,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ], 500);
        }
    }

    public function cancel($id)
    {
        $reservation = Reservation::findOrFail($id);

        if ($reservation->customer_id !== Auth::user()->customer->id) {
            abort(403);
        }

        if (!in_array($reservation->status, ['pending', 'approved'])) {
            return redirect()->back()->with('error', 'Reservasi tidak dapat dibatalkan.');
        }

        $reservation->status = 'cancelled';
        $reservation->save();

        return redirect()->route('customer.dashboard')
            ->with('success', 'Reservasi berhasil dibatalkan.');
    }

    /**
     * Show reservation history for customer
     */
    public function history(Request $request)
    {
        Log::info('History method called', [
            'user_id' => Auth::id(),
            'customer_id' => Auth::user()->customer->id ?? 'No customer',
            'request_data' => $request->all()
        ]);

        $reservations = \App\Models\Reservation::with(['table', 'table.room'])
            ->where('customer_id', Auth::user()->customer->id)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        Log::info('Reservations count: ' . $reservations->count());

        return view('customer.reservation.history', compact('reservations'));
    }

    /**
     * Show the specified reservation.
     */
    public function show($id)
    {
        $reservation = Reservation::with(['customer', 'table', 'table.room'])
            ->findOrFail($id);

        if ($reservation->customer_id !== Auth::user()->customer->id) {
            abort(403);
        }

        $paymentDetails = $reservation->getFormattedPaymentDetails();

        return view('customer.reservation.show', compact('reservation', 'paymentDetails'));
    }

    public function checkStatus($id)
    {
        $reservation = Reservation::findOrFail($id);

        // Pastikan user yang login adalah pemilik reservasi
        if ($reservation->customer_id !== auth()->user()->customer->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Periksa status pembayaran jika status approved
        if ($reservation->status === 'approved' && $reservation->payment_expired_at) {
            if (now()->isAfter($reservation->payment_expired_at)) {
                $reservation->update(['status' => 'expired', 'status_expired_at' => now()]);
            }
        }

        return response()->json([
            'status' => $reservation->status,
            'message' => 'Status berhasil diperbarui'
        ]);
    }
}
