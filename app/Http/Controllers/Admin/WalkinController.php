<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Payment;
use App\Models\Price;
use App\Models\Room;
use App\Models\Table;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Midtrans\Config;
use Midtrans\Snap;

class WalkinController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display the walk-in page with billiard tables.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // Get selected date (default to today)
        $selectedDate = $request->date ? Carbon::parse($request->date) : Carbon::today();

        // Get all available rooms
        $rooms = Room::where('status', true)->get();

        // Get tables grouped by room
        $tablesByRoom = [];
        foreach ($rooms as $room) {
            $tables = Table::where('room_id', $room->id)->get();

            // Add availability data to each table
            foreach ($tables as $table) {
                // Get all transactions for this table on the selected date
                $transactions = Transaction::where('table_id', $table->id)
                    ->where('status', '!=', 'cancelled')
                    ->whereDate('start_time', $selectedDate)
                    ->orderBy('start_time')
                    ->get();

                // Determine current status
                $currentStatus = 'available';
                $currentTransaction = null;

                if ($table->status === 'maintenance') {
                    $currentStatus = 'maintenance';
                } else {
                    // Check if there's an active transaction right now
                    $activeTransaction = Transaction::where('table_id', $table->id)
                        ->where('status', 'paid')
                        ->where('start_time', '<=', Carbon::now())
                        ->where('end_time', '>=', Carbon::now())
                        ->first();

                    if ($activeTransaction) {
                        $currentStatus = 'in_use';
                        $currentTransaction = $activeTransaction;
                    }
                }

                $table->current_status = $currentStatus;
                $table->current_transaction = $currentTransaction;
                $table->day_transactions = $transactions;
            }

            $tablesByRoom[$room->id] = [
                'room' => $room,
                'tables' => $tables
            ];
        }

        // Get all operating hours (default 8 AM to 11 PM)
        $operatingHours = [];
        $startHour = 8; // 8 AM
        $endHour = 23; // 11 PM

        for ($hour = $startHour; $hour <= $endHour; $hour++) {
            $operatingHours[] = [
                'hour' => $hour,
                'display' => sprintf('%02d:00', $hour)
            ];
        }

        return view('admin.walkin.index', compact('tablesByRoom', 'selectedDate', 'operatingHours'));
    }

    /**
     * Get detailed information about a table.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getTableDetails(Request $request)
    {
        $tableId = $request->table_id;
        $date = $request->date ? Carbon::parse($request->date) : Carbon::today();

        // Get the table with room
        $table = Table::with('room')->findOrFail($tableId);

        // Get all transactions for this table on the selected date
        $transactions = Transaction::with('customer')
            ->where('table_id', $tableId)
            ->where('status', '!=', 'cancelled')
            ->whereDate('start_time', $date)
            ->orderBy('start_time')
            ->get()
            ->map(function($transaction) {
                return [
                    'id' => $transaction->id,
                    'transaction_code' => $transaction->transaction_code,
                    'customer_name' => $transaction->customer->name,
                    'start_time' => $transaction->start_time->format('H:i'),
                    'end_time' => $transaction->end_time->format('H:i'),
                    'status' => $transaction->status,
                    'total_price' => $transaction->total_price,
                ];
            });

        // Calculate total usage hours for today
        $totalHours = 0;
        foreach ($transactions as $transaction) {
            $start = Carbon::parse($date->format('Y-m-d') . ' ' . $transaction['start_time']);
            $end = Carbon::parse($date->format('Y-m-d') . ' ' . $transaction['end_time']);
            $totalHours += $end->diffInMinutes($start) / 60;
        }

        // Determine available time slots
        $availableSlots = $this->getAvailableTimeSlots($tableId, $date);

        // Determine current status
        $currentStatus = 'available';
        $currentTransaction = null;

        if ($table->status === 'maintenance') {
            $currentStatus = 'maintenance';
        } else {
            // Check if there's an active transaction right now
            $activeTransaction = Transaction::with('customer')
                ->where('table_id', $tableId)
                ->where('status', 'paid')
                ->where('start_time', '<=', Carbon::now())
                ->where('end_time', '>=', Carbon::now())
                ->first();

            if ($activeTransaction) {
                $currentStatus = 'in_use';
                $currentTransaction = [
                    'id' => $activeTransaction->id,
                    'transaction_code' => $activeTransaction->transaction_code,
                    'customer_name' => $activeTransaction->customer->name,
                    'start_time' => $activeTransaction->start_time->format('H:i'),
                    'end_time' => $activeTransaction->end_time->format('H:i'),
                    'remaining_minutes' => Carbon::now()->diffInMinutes($activeTransaction->end_time),
                ];
            }
        }

        return response()->json([
            'table' => [
                'id' => $table->id,
                'number' => $table->table_number,
                'brand' => $table->brand,
                'room' => $table->room->name,
                'status' => $table->status,
                'current_status' => $currentStatus,
                'total_hours_used' => round($totalHours, 1)
            ],
            'current_transaction' => $currentTransaction,
            'transactions' => $transactions,
            'available_slots' => $availableSlots
        ]);
    }

    /**
     * Show the form for creating a new walk-in transaction.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Table  $table
     * @return \Illuminate\Http\Response
     */
    public function createTransaction(Request $request, $table)
    {
        Log::info('Membuat transaksi walk-in baru', [
            'request_data' => $request->all()
        ]);

        $startTime = $request->has('start_time') ? $request->start_time : null;

        $table = Table::with('room')->findOrFail($table);
        $customers = Customer::where('status', true)->get();
        $date = $request->date ? Carbon::parse($request->date)->format('Y-m-d') : Carbon::today()->format('Y-m-d');

        // Get current price for the table using PriceService
        $currentTime = Carbon::now();
        $price = \App\Services\PriceService::getPriceForTable($table, $currentTime);

        if (!$price || !isset($price->price_per_hour)) {
            // Jika tidak ada harga, gunakan placeholder
            $price = (object)[
                'price_per_hour' => 0,
                'day_type' => 'all',
                'start_time' => '08:00',
                'end_time' => '23:00'
            ];

            Log::warning('Tidak ada harga ditemukan untuk meja', [
                'table_id' => $table->id,
                'room_id' => $table->room_id,
                'date' => $date
            ]);
        }

        return view('admin.walkin.walkin_form', compact('table', 'customers', 'date', 'startTime', 'price'));
    }

    /**
     * Check availability of a time slot.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkAvailability(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'table_id' => 'required|exists:tables,id',
            'start_time' => 'required|date_format:Y-m-d H:i',
            'duration' => 'required|numeric|min:0.5|max:12',
        ]);

        if ($validator->fails()) {
            Log::warning('Validation failed in checkAvailability', ['errors' => $validator->errors()->toArray()]);
            return response()->json([
                'status' => 'error',
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        $tableId = $request->table_id;
        $startTime = Carbon::parse($request->start_time);
        $duration = (float) $request->duration;
        $endTime = (clone $startTime)->addHours($duration);

        // Get the table with its room
        $table = Table::with('room')->findOrFail($tableId);

        // Check if table is in maintenance
        if ($table->status === 'maintenance') {
            return response()->json([
                'status' => 'error',
                'message' => 'Meja sedang dalam perawatan'
            ]);
        }

        // Check for conflicting transactions
        $conflictingTransaction = Transaction::where('table_id', $tableId)
            ->where('status', '!=', 'cancelled')
            ->where(function ($query) use ($startTime, $endTime) {
                // Overlapping time ranges
                $query->where(function ($q) use ($startTime, $endTime) {
                    $q->where('start_time', '<', $endTime)
                        ->where('end_time', '>', $startTime);
                });
            })
            ->first();

        if ($conflictingTransaction) {
            // Get customer information for the conflicting transaction
            $conflictingTransaction->load('customer');

            return response()->json([
                'status' => 'error',
                'message' => 'Meja sudah dibooking pada waktu tersebut',
                'conflict_info' => [
                    'customer' => $conflictingTransaction->customer->name,
                    'start_time' => $conflictingTransaction->start_time->format('H:i'),
                    'end_time' => $conflictingTransaction->end_time->format('H:i'),
                    'transaction_code' => $conflictingTransaction->transaction_code
                ]
            ]);
        }

        // Get current price for the table using PriceService
        $price = \App\Services\PriceService::getPriceForTable($table, $startTime);

        if (!$price) {
            Log::warning('No price found for table', [
                'table_id' => $tableId,
                'start_time' => $startTime->format('Y-m-d H:i:s'),
                'time_of_day' => $startTime->format('H:i:s')
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Harga tidak tersedia untuk waktu yang dipilih'
            ]);
        }

        // Calculate total price
        $pricePerHour = $price->price_per_hour ?? $price->price; // Use price_per_hour if set, otherwise use price
        $totalPrice = $pricePerHour * $duration;

        // Format time for response
        $startTimeFormatted = Carbon::parse($price->start_time)->format('H:i');
        $endTimeFormatted = Carbon::parse($price->end_time)->format('H:i');
        $isOvernight = $startTimeFormatted > $endTimeFormatted;

        // Format response data
        $availabilityData = [
            'status' => 'available',
            'message' => 'Meja tersedia pada waktu yang dipilih',
            'price_info' => [
                'price_per_hour' => $pricePerHour,
                'total_price' => $totalPrice,
                'day_type' => $price->day_type,
                'start_time' => $startTimeFormatted,
                'end_time' => $endTimeFormatted,
                'is_overnight' => $isOvernight
            ]
        ];

        Log::info('Price calculated successfully', ['price_info' => $availabilityData['price_info']]);

        return response()->json($availabilityData);
    }

    /**
     * Store a new walk-in transaction.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function storeTransaction(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'customer_id' => 'required|exists:customers,id',
            'table_id' => 'required|exists:tables,id',
            'start_time' => 'required|date_format:Y-m-d H:i',
            'duration' => 'required|numeric|min:0.5|max:12',
            'payment_method' => 'required|in:cash,e_payment',
            'notes' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Calculate end time
        $startTime = Carbon::parse($request->start_time);
        $endTime = (clone $startTime)->addMinutes($request->duration * 60);

        // Check availability
        if (!$this->isTableAvailable($request->table_id, $startTime, $endTime)) {
            return redirect()->back()
                ->with('error', 'Meja tidak tersedia pada waktu yang dipilih.')
                ->withInput();
        }

        // Start a database transaction
        DB::beginTransaction();

        try {
            // Get the table
            $table = Table::findOrFail($request->table_id);

            // Get price for the time using PriceService
            $price = \App\Services\PriceService::getPriceForTable($table, $startTime);

            if (!$price || $price->price_per_hour === null) {
                return redirect()->back()
                    ->with('error', 'Gagal mengambil harga dari master harga. Silakan pastikan harga sudah dikonfigurasi dengan benar.')
                    ->withInput();
            }

            // Calculate total price
            $totalPrice = $price->price_per_hour * $request->duration;

            // Generate transaction code
            $transactionCode = 'WI-' . strtoupper(Str::random(8));
            while (Transaction::where('transaction_code', $transactionCode)->exists()) {
                $transactionCode = 'WI-' . strtoupper(Str::random(8));
            }

            // Create transaction
            $transaction = Transaction::create([
                'customer_id' => $request->customer_id,
                'table_id' => $table->id,
                'user_id' => Auth::id(),
                'transaction_type' => 'walk_in',
                'start_time' => $startTime,
                'end_time' => $endTime,
                'status' => 'pending',
                'total_price' => $totalPrice,
                'transaction_code' => $transactionCode,
                'notes' => $request->notes,
                'price_per_hour' => $price->price_per_hour,
                'duration_hours' => $request->duration,
            ]);

            // Create transaction detail
            TransactionDetail::create([
                'transaction_id' => $transaction->id,
                'duration_hours' => $request->duration,
                'price_per_hour' => $price->price_per_hour,
                'subtotal' => $totalPrice,
            ]);

            DB::commit();

            // Handle payment differently based on method
            if ($request->payment_method === 'cash') {
                return redirect()->route('admin.walkin.confirmCashPayment', ['id' => $transaction->id]);
            } else {
                return redirect()->route('admin.walkin.confirmEPayment', ['id' => $transaction->id]);
            }

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating transaction', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Show cash payment confirmation page.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function confirmCashPayment($id)
    {
        $transaction = Transaction::with(['customer', 'table', 'table.room', 'details'])
            ->findOrFail($id);

        return view('admin.walkin.cash_payment', compact('transaction'));
    }

    /**
     * Process cash payment.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function processCashPayment(Request $request, $id)
    {
        $transaction = Transaction::with(['customer', 'table', 'table.room', 'details'])
            ->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'amount_paid' => 'required|numeric|min:' . $transaction->total_price,
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        DB::beginTransaction();

        try {
            // Calculate change
            $changeAmount = $request->amount_paid - $transaction->total_price;

            // Update transaction status
            $transaction->update([
                'status' => 'paid',
                'payment_method' => 'cash',
            ]);

            // Create payment record
            Payment::create([
                'transaction_id' => $transaction->id,
                'payment_method' => 'cash',
                'total_amount' => $transaction->total_price,
                'amount_paid' => $request->amount_paid,
                'change_amount' => $changeAmount,
                'status' => 'paid',
                'payment_date' => now(),
                'notes' => 'Pembayaran cash',
            ]);

            DB::commit();

            return redirect()->route('admin.walkin.transactionSuccess', ['id' => $transaction->id])
                ->with('success', 'Pembayaran berhasil diproses.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error processing cash payment', [
                'transaction_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Show e-payment confirmation page.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function confirmEPayment($id)
    {
        $transaction = Transaction::with(['customer', 'table', 'table.room', 'user', 'payment', 'details'])
            ->findOrFail($id);

        // Set Midtrans configuration
        Config::$serverKey = config('services.midtrans.server_key');
        Config::$isProduction = config('services.midtrans.is_production');
        Config::$isSanitized = true;
        Config::$is3ds = true;

        // Generate unique order ID by appending timestamp
        $orderId = $transaction->transaction_code . '-' . time();

        // Calculate total
        $totalAmount = $transaction->total_price;

        // Log data untuk debugging
        Log::info('Preparing Midtrans Payment:', [
            'transaction_id' => $transaction->id,
            'transaction_code' => $transaction->transaction_code,
            'total_amount' => $totalAmount
        ]);

        // Prepare transaction details for Midtrans
        $params = [
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => (int) $totalAmount,
            ],
            'customer_details' => [
                'first_name' => $transaction->customer->name,
                'email' => $transaction->customer->email ?? '',
                'phone' => $transaction->customer->phone,
            ],
            'item_details' => [
                [
                    'id' => $transaction->table->id,
                    'price' => (int) $transaction->details->first()->price_per_hour,
                    'quantity' => $transaction->details->first()->duration_hours,
                    'name' => 'Meja ' . $transaction->table->table_number . ' - ' . $transaction->table->room->name,
                ]
            ],
        ];

        // Log data yang akan dikirim ke Midtrans
        Log::info('Midtrans Payment Parameters:', [
            'order_id' => $orderId,
            'gross_amount' => $totalAmount,
            'item_details' => $params['item_details']
        ]);

        try {
            // Get Snap Token
            $snapToken = Snap::getSnapToken($params);

            // Store the order_id in session for later use
            session(['midtrans_order_id' => $orderId]);

            return view('admin.walkin.e_payment', compact('transaction', 'snapToken'));
        } catch (\Exception $e) {
            Log::error('Midtrans Error:', [
                'transaction_id' => $transaction->id,
                'error' => $e->getMessage(),
                'params' => $params
            ]);
            return redirect()->back()
                ->with('error', 'Gagal membuat token pembayaran: ' . $e->getMessage());
        }
    }

    /**
     * Process e-payment (callback from payment gateway).
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function processEPayment(Request $request)
    {
        // This would be a callback endpoint for the payment gateway
        // For simplicity, we'll simulate a successful payment

        $transactionId = $request->transaction_id;
        $transaction = Transaction::findOrFail($transactionId);

        DB::beginTransaction();

        try {
            // Update transaction status
            $transaction->update([
                'status' => 'paid',
                'payment_method' => 'e_payment',
            ]);

            // Create payment record
            Payment::create([
                'transaction_id' => $transaction->id,
                'payment_method' => 'e_payment',
                'total_amount' => $transaction->total_price,
                'amount_paid' => $transaction->total_price,
                'change_amount' => 0,
                'status' => 'paid',
                'payment_date' => now(),
                'payment_details' => json_encode($request->all()),
            ]);

            DB::commit();

            return response()->json(['status' => 'success']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Show success page after transaction.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function transactionSuccess($id)
    {
        $transaction = Transaction::with(['customer', 'table', 'table.room', 'payment', 'details'])
            ->findOrFail($id);

        return view('admin.walkin.success', compact('transaction'));
    }

    /**
     * Extend an active transaction.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function extendTransaction(Request $request, $id)
    {
        $transaction = Transaction::findOrFail($id);

        // Only allow extending active transactions
        if ($transaction->status !== 'paid') {
            return redirect()->back()
                ->with('error', 'Hanya transaksi yang sedang aktif yang dapat diperpanjang.');
        }

        $validator = Validator::make($request->all(), [
            'extension_hours' => 'required|numeric|min:0.5|max:12',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Calculate new end time
        $newEndTime = (clone $transaction->end_time)->addMinutes($request->extension_hours * 60);

        // Check if the extension is available
        if (!$this->isTableAvailable($transaction->table_id, $transaction->end_time, $newEndTime, $transaction->id)) {
            return redirect()->back()
                ->with('error', 'Perpanjangan tidak tersedia karena ada jadwal lain setelahnya.')
                ->withInput();
        }

        DB::beginTransaction();

        try {
            // Get price for extension using PriceService
            $table = Table::find($transaction->table_id);
            $price = \App\Services\PriceService::getPriceForTable($table, $transaction->end_time);

            if (!$price) {
                return redirect()->back()
                    ->with('error', 'Harga untuk perpanjangan tidak ditemukan.')
                    ->withInput();
            }

            // Calculate extension price
            $extensionPrice = $price->price_per_hour * $request->extension_hours;

            // Update transaction end time
            $transaction->update([
                'end_time' => $newEndTime,
                'total_price' => $transaction->total_price + $extensionPrice,
            ]);

            // Create a new payment for the extension
            Payment::create([
                'transaction_id' => $transaction->id,
                'payment_method' => 'cash', // Assuming extensions are paid in cash
                'total_amount' => $extensionPrice,
                'amount_paid' => $extensionPrice,
                'change_amount' => 0,
                'status' => 'paid',
                'payment_date' => now(),
                'notes' => 'Extension payment',
            ]);

            // Update transaction detail
            $transactionDetail = TransactionDetail::where('transaction_id', $transaction->id)->first();
            $transactionDetail->update([
                'duration_hours' => $transactionDetail->duration_hours + $request->extension_hours,
                'subtotal' => $transactionDetail->subtotal + $extensionPrice,
            ]);

            DB::commit();

            return redirect()->route('admin.walkin.index')
                ->with('success', 'Transaksi berhasil diperpanjang.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Stop an active transaction early.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function stopTransaction($id)
    {
        $transaction = Transaction::findOrFail($id);

        // Only allow stopping active transactions
        if ($transaction->status !== 'paid') {
            return redirect()->back()
                ->with('error', 'Hanya transaksi yang sedang aktif yang dapat dihentikan.');
        }

        // Update transaction status and end time
        $transaction->update([
            'status' => 'completed',
            'end_time' => now(),
        ]);

        // Update transaction detail with actual duration
        $transactionDetail = TransactionDetail::where('transaction_id', $transaction->id)->first();
        $actualDuration = $transaction->start_time->diffInMinutes(now()) / 60;
        $transactionDetail->update([
            'duration_hours' => $actualDuration,
        ]);

        return redirect()->route('admin.walkin.index')
            ->with('success', 'Transaksi berhasil dihentikan.');
    }

    /**
     * Process sessions that have expired.
     */
    public function processExpiredSessions()
    {
        // Find all paid transactions that have ended but aren't completed yet
        $expiredTransactions = Transaction::where('status', 'paid')
            ->where('end_time', '<', now())
            ->get();

        foreach ($expiredTransactions as $transaction) {
            $transaction->update(['status' => 'completed']);
        }

        return response()->json([
            'status' => 'success',
            'count' => $expiredTransactions->count()
        ]);
    }

    /**
     * Check if a table is available for the specified time slot.
     *
     * @param  int  $tableId
     * @param  \Carbon\Carbon  $startTime
     * @param  \Carbon\Carbon  $endTime
     * @param  int|null  $excludeTransactionId
     * @return bool
     */
    private function isTableAvailable($tableId, $startTime, $endTime, $excludeTransactionId = null)
    {
        // Check if table is in maintenance
        $table = Table::find($tableId);
        if ($table->status === 'maintenance') {
            return false;
        }

        $query = Transaction::where('table_id', $tableId)
            ->where('status', '!=', 'cancelled')
            ->where(function($q) use ($startTime, $endTime) {
                // Check if the requested time slot overlaps with any existing transaction
                // (startA <= endB) && (endA >= startB)
                $q->where(function($q1) use ($startTime, $endTime) {
                    $q1->where('start_time', '<=', $endTime)
                       ->where('end_time', '>=', $startTime);
                });
            });

        // Exclude the current transaction if extending
        if ($excludeTransactionId) {
            $query->where('id', '!=', $excludeTransactionId);
        }

        return $query->count() === 0;
    }

    /**
     * Get available time slots for a table on a specific date.
     *
     * @param  int  $tableId
     * @param  \Carbon\Carbon  $date
     * @return array
     */
    private function getAvailableTimeSlots($tableId, $date)
    {
        // Get all transactions for this table on the date
        $transactions = Transaction::where('table_id', $tableId)
            ->where('status', '!=', 'cancelled')
            ->whereDate('start_time', $date)
            ->orderBy('start_time')
            ->get();

        // Define operating hours (8 AM to 11 PM)
        $operatingStart = 8;
        $operatingEnd = 23;

        $dateStr = $date->format('Y-m-d');
        $startOfDay = Carbon::parse($dateStr . ' ' . sprintf('%02d:00:00', $operatingStart));
        $endOfDay = Carbon::parse($dateStr . ' ' . sprintf('%02d:00:00', $operatingEnd));

        // Initialize available slots
        $availableSlots = [];

        // Start with the beginning of operating hours
        $currentTime = clone $startOfDay;

        // For each transaction, add available slot before it
        foreach ($transactions as $transaction) {
            if ($currentTime < $transaction->start_time) {
                $availableSlots[] = [
                    'start' => $currentTime->format('H:i'),
                    'end' => $transaction->start_time->format('H:i'),
                    'duration_hours' => $currentTime->diffInMinutes($transaction->start_time) / 60
                ];
            }

            // Move current time to after this transaction
            $currentTime = clone $transaction->end_time;
        }

        // Add final slot if time remains in the day
        if ($currentTime < $endOfDay) {
            $availableSlots[] = [
                'start' => $currentTime->format('H:i'),
                'end' => $endOfDay->format('H:i'),
                'duration_hours' => $currentTime->diffInMinutes($endOfDay) / 60
            ];
        }

        return $availableSlots;
    }

    public function updatePaymentStatus(Request $request, $id)
    {
        try {
            DB::beginTransaction();

            $transaction = Transaction::findOrFail($id);

            // Update status transaksi
            $transaction->status = 'paid';
            $transaction->payment_method = 'e_payment';
            $transaction->payment_status = 'success';
            $transaction->payment_details = $request->result;
            $transaction->save();

            // Create payment record if not exists
            if (!$transaction->payment) {
                Payment::create([
                    'transaction_id' => $transaction->id,
                    'payment_method' => 'e_payment',
                    'total_amount' => $transaction->total_price,
                    'amount_paid' => $transaction->total_price,
                    'change_amount' => 0,
                    'status' => 'paid',
                    'payment_date' => now(),
                    'payment_details' => json_encode($request->result),
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Status pembayaran berhasil diperbarui'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating payment status', [
                'transaction_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui status pembayaran: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show timeline view for a specific table.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $table
     * @return \Illuminate\Http\Response
     */
    public function timeline(Request $request, $table)
    {
        // Get selected date (default to today)
        $selectedDate = $request->date ? Carbon::parse($request->date) : Carbon::today();

        // Get the table with room
        $table = Table::with('room')->findOrFail($table);

        // Get all transactions for this table on the selected date
        $transactions = Transaction::with('customer')
            ->where('table_id', $table->id)
            ->where('status', '!=', 'cancelled')
            ->whereDate('start_time', $selectedDate)
            ->orderBy('start_time')
            ->get();

        // Calculate total hours used
        $totalHoursUsed = 0;
        foreach ($transactions as $transaction) {
            $totalHoursUsed += $transaction->start_time->diffInMinutes($transaction->end_time) / 60;
        }

        // Get available slots
        $availableSlots = $this->getAvailableTimeSlots($table->id, $selectedDate);

        return view('admin.walkin.timeline', compact('table', 'transactions', 'selectedDate', 'totalHoursUsed', 'availableSlots'));
    }

    /**
     * Return current server time as JSON (for timeline sync)
     */
    public function serverTime()
    {
        $now = now()->setTimezone('Asia/Makassar');
        return response()->json([
            'server_time' => $now->format('c') // ISO 8601 with offset
        ]);
    }
}
