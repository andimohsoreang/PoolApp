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
use App\Models\User;
use App\Models\Reservation;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class TransactionController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:owner')->only(['create', 'store']);
    }

    /**
     * Display a listing of the transactions.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = Transaction::with(['customer', 'table', 'table.room', 'user', 'payment']);

        // Apply filters if provided
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        if ($request->filled('transaction_type')) {
            $query->where('transaction_type', $request->transaction_type);
        }

        if ($request->filled('table_id')) {
            $query->where('table_id', $request->table_id);
        }

        if ($request->filled('room_id')) {
            $query->whereHas('table', function($q) use ($request) {
                $q->where('room_id', $request->room_id);
            });
        }

        if ($request->filled('date_from')) {
            $query->whereDate('start_time', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('start_time', '<=', $request->date_to);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('transaction_code', 'like', "%{$search}%")
                  ->orWhereHas('customer', function($q2) use ($search) {
                      $q2->where('name', 'like', "%{$search}%")
                         ->orWhere('phone', 'like', "%{$search}%")
                         ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        // Get results with pagination
        $transactions = $query->latest()->paginate(15);

        // Get rooms and tables for filters
        $rooms = Room::where('status', true)->get();
        $tables = Table::whereHas('room', function($q) {
            $q->where('status', true);
        })->where('status', 'normal')->get();

        // Get statistics for dashboard
        $totalToday = Transaction::whereDate('created_at', today())->count();
        $totalPendingPayment = Transaction::where('status', 'pending')->count();
        $totalRevenue = Payment::where('status', 'paid')->sum('total_amount');
        $totalRevenueToday = Payment::whereDate('created_at', today())
            ->where('status', 'paid')
            ->sum('total_amount');

        return view('admin.transactions.index', compact('transactions', 'rooms', 'tables',
            'totalToday', 'totalPendingPayment', 'totalRevenue', 'totalRevenueToday'));
    }

    /**
     * Show the form for creating a new transaction.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $rooms = Room::where('status', true)->get();
        $tables = Table::whereHas('room', function($q) {
            $q->where('status', true);
        })->where('status', 'normal')->get();
        $customers = Customer::where('status', true)->get();

        return view('admin.transactions.create', compact('rooms', 'tables', 'customers'));
    }

    /**
     * Store a newly created transaction in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'customer_id' => 'required|exists:customers,id',
            'table_id' => 'required|exists:tables,id',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
            'transaction_type' => 'required|in:walk_in,reservation',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Start a database transaction
        DB::beginTransaction();

        try {
            // Check if table is available for the time slot
            $isTableAvailable = $this->isTableAvailable(
                $request->table_id,
                Carbon::parse($request->start_time),
                Carbon::parse($request->end_time)
            );

            if (!$isTableAvailable) {
                return redirect()->back()
                    ->with('error', 'Meja tidak tersedia pada waktu yang dipilih.')
                    ->withInput();
            }

            // Get the table
            $table = Table::findOrFail($request->table_id);

            // Calculate duration in hours
            $startTime = Carbon::parse($request->start_time);
            $endTime = Carbon::parse($request->end_time);
            $durationHours = $endTime->diffInMinutes($startTime) / 60;

            // Get appropriate price for the table and time
            $price = $this->getPriceForTable($table, $startTime);

            if (!$price) {
                return redirect()->back()
                    ->with('error', 'Harga untuk meja dan waktu yang dipilih tidak ditemukan.')
                    ->withInput();
            }

            // Validasi price_per_hour tidak boleh 0
            if ($price->price_per_hour <= 0) {
                return redirect()->back()
                    ->with('error', 'Harga per jam tidak valid.')
                    ->withInput();
            }

            // Calculate subtotal
            $subtotal = $price->price_per_hour * $durationHours;
            $totalPrice = $subtotal;
            $discount = 0;

            // Check if this is a reservation-based transaction
            if ($request->transaction_type === 'reservation' && $request->filled('reservation_id')) {
                $reservation = Reservation::find($request->reservation_id);

                Log::info('Reservation Data:', [
                    'reservation_found' => $reservation ? true : false,
                    'reservation_data' => $reservation ? [
                        'id' => $reservation->id,
                        'status' => $reservation->status
                    ] : null
                ]);
            }

            // Calculate total price
            $totalPrice = $subtotal - $discount;

            // Generate a unique transaction code
            $transactionCode = 'TRX-' . strtoupper(Str::random(8));
            while (Transaction::where('transaction_code', $transactionCode)->exists()) {
                $transactionCode = 'TRX-' . strtoupper(Str::random(8));
            }

            // Log data sebelum create untuk debugging
            Log::info('Transaction Data Before Create:', [
                'transaction_type' => $request->transaction_type,
                'price_per_hour' => $price->price_per_hour,
                'duration_hours' => $durationHours,
                'subtotal' => $subtotal,
                'discount' => $discount,
                'total_price' => $totalPrice
            ]);

            // Create the transaction
            $transaction = Transaction::create([
                'customer_id' => $request->customer_id,
                'table_id' => $table->id,
                'user_id' => Auth::id(),
                'transaction_type' => $request->transaction_type,
                'start_time' => $startTime,
                'end_time' => $endTime,
                'status' => 'pending',
                'total_price' => $totalPrice,
                'transaction_code' => $transactionCode,
                'duration_hours' => $durationHours,
                'price_per_hour' => $price->price_per_hour,
                'discount' => $discount
            ]);

            // Create transaction detail
            $transactionDetail = TransactionDetail::create([
                'transaction_id' => $transaction->id,
                'duration_hours' => $durationHours,
                'price_per_hour' => $price->price_per_hour,
                'discount' => $discount,
                'subtotal' => $subtotal,
            ]);

            // Create initial payment record
            $payment = Payment::create([
                'transaction_id' => $transaction->id,
                'payment_method' => null,
                'total_amount' => $totalPrice,
                'amount_paid' => 0,
                'change_amount' => 0,
                'status' => 'pending',
            ]);

            // Log data setelah create untuk debugging
            Log::info('Transaction Created:', [
                'transaction_id' => $transaction->id,
                'transaction_code' => $transaction->transaction_code,
                'total_price' => $transaction->total_price,
                'duration_hours' => $transaction->duration_hours,
                'price_per_hour' => $transaction->price_per_hour,
                'discount' => $transaction->discount,
                'subtotal' => $subtotal
            ]);

            DB::commit();

            return redirect()->route('admin.transactions.show', $transaction->id)
                ->with('success', 'Transaksi berhasil dibuat.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified transaction.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $transaction = Transaction::with([
            'customer',
            'table',
            'table.room',
            'user',
            'details',
            'payment',
            'reservation'
        ])->findOrFail($id);

        return view('admin.transactions.show', compact('transaction'));
    }

    /**
     * Show the form for editing the specified transaction.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $transaction = Transaction::with([
            'customer',
            'table',
            'table.room',
            'user',
            'payment',
            'details'
        ])->findOrFail($id);

        // Only allow editing pending transactions
        if ($transaction->status !== 'pending') {
            return redirect()->route('admin.transactions.show', $transaction->id)
                ->with('error', 'Hanya transaksi dengan status pending yang dapat diedit.');
        }

        $rooms = Room::where('status', true)->get();
        $tables = Table::whereHas('room', function($q) {
            $q->where('status', true);
        })->where('status', 'normal')->get();
        $customers = Customer::where('status', true)->get();

        return view('admin.transactions.edit', compact('transaction', 'rooms', 'tables', 'customers'));
    }

    /**
     * Update the specified transaction in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $transaction = Transaction::findOrFail($id);

        // Only allow updating pending transactions
        if ($transaction->status !== 'pending') {
            return redirect()->route('admin.transactions.show', $transaction->id)
                ->with('error', 'Hanya transaksi dengan status pending yang dapat diperbarui.');
        }

        $validator = Validator::make($request->all(), [
            'customer_id' => 'required|exists:customers,id',
            'table_id' => 'required|exists:tables,id',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
            'transaction_type' => 'required|in:walk_in,reservation',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Start a database transaction
        DB::beginTransaction();

        try {
            // Check if table is available for the time slot (excluding current transaction)
            $isTableAvailable = $this->isTableAvailable(
                $request->table_id,
                Carbon::parse($request->start_time),
                Carbon::parse($request->end_time),
                $transaction->id
            );

            if (!$isTableAvailable) {
                return redirect()->back()
                    ->with('error', 'Meja tidak tersedia pada waktu yang dipilih.')
                    ->withInput();
            }

            // Get the table
            $table = Table::findOrFail($request->table_id);

            // Calculate duration in hours
            $startTime = Carbon::parse($request->start_time);
            $endTime = Carbon::parse($request->end_time);
            $durationHours = $endTime->diffInMinutes($startTime) / 60;

            // Get appropriate price for the table and time
            $price = $this->getPriceForTable($table, $startTime);

            if (!$price) {
                return redirect()->back()
                    ->with('error', 'Harga untuk meja dan waktu yang dipilih tidak ditemukan.')
                    ->withInput();
            }

            // Calculate total price
            $subtotal = $price->price_per_hour * $durationHours;
            $discount = 0;
            $totalPrice = $subtotal - $discount;

            // Update the transaction
            $transaction->update([
                'customer_id' => $request->customer_id,
                'table_id' => $table->id,
                'transaction_type' => $request->transaction_type,
                'start_time' => $startTime,
                'end_time' => $endTime,
                'total_price' => $totalPrice,
                'duration_hours' => $durationHours,
                'price_per_hour' => $price->price_per_hour,
                'discount' => $discount,
            ]);

            // Update or create transaction detail
            $transactionDetail = TransactionDetail::updateOrCreate(
                ['transaction_id' => $transaction->id],
                [
                    'duration_hours' => $durationHours,
                    'price_per_hour' => $price->price_per_hour,
                    'discount' => $discount,
                    'subtotal' => $subtotal,
                ]
            );

            // Update payment record
            $payment = Payment::updateOrCreate(
                ['transaction_id' => $transaction->id],
                [
                    'total_amount' => $totalPrice,
                ]
            );

            DB::commit();

            return redirect()->route('admin.transactions.show', $transaction->id)
                ->with('success', 'Transaksi berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Process payment for a transaction.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function processPayment(Request $request, $id)
    {
        $transaction = Transaction::with(['promo', 'details.promo'])->findOrFail($id);

        // Validate input
        $validator = Validator::make($request->all(), [
            'payment_method' => 'required|in:cash,e_payment',
            'amount_paid' => 'required|numeric|min:' . $transaction->total_price,
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Start a database transaction
        DB::beginTransaction();

        try {
            // Update transaction status
            $transaction->update([
                'status' => 'paid',
                'payment_method' => $request->payment_method,
            ]);

            // Calculate change amount
            $changeAmount = $request->amount_paid - $transaction->total_price;

            // Update payment record
            $payment = Payment::where('transaction_id', $transaction->id)->first();

            if ($payment) {
                $payment->update([
                    'payment_method' => $request->payment_method,
                    'amount_paid' => $request->amount_paid,
                    'change_amount' => $changeAmount,
                    'status' => 'paid',
                    'payment_date' => now(),
                ]);
            } else {
                // Create payment record if it doesn't exist
                $payment = Payment::create([
                    'transaction_id' => $transaction->id,
                    'payment_method' => $request->payment_method,
                    'total_amount' => $transaction->total_price,
                    'amount_paid' => $request->amount_paid,
                    'change_amount' => $changeAmount,
                    'status' => 'paid',
                    'payment_date' => now(),
                ]);
            }

            // Log data untuk debugging
            Log::info('Payment Processed:', [
                'transaction_id' => $transaction->id,
                'transaction_code' => $transaction->transaction_code,
                'total_price' => $transaction->total_price,
                'discount' => $transaction->discount,
                'promo' => $transaction->promo ? [
                    'id' => $transaction->promo->id,
                    'code' => $transaction->promo->code,
                    'name' => $transaction->promo->name,
                    'discount_type' => $transaction->promo->discount_type,
                    'discount_value' => $transaction->promo->discount_value
                ] : null,
                'payment' => [
                    'payment_method' => $request->payment_method,
                    'amount_paid' => $request->amount_paid,
                    'change_amount' => $changeAmount
                ]
            ]);

            DB::commit();

            return redirect()->route('admin.transactions.show', $transaction->id)
                ->with('success', 'Pembayaran berhasil diproses.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Cancel the specified transaction.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function cancel($id)
    {
        $transaction = Transaction::findOrFail($id);

        // Only allow cancelling pending or paid transactions
        if (!in_array($transaction->status, ['pending', 'paid'])) {
            return redirect()->route('admin.transactions.show', $transaction->id)
                ->with('error', 'Hanya transaksi dengan status pending atau paid yang dapat dibatalkan.');
        }

        // Start a database transaction
        DB::beginTransaction();

        try {
            // Update transaction status
            $transaction->update([
                'status' => 'cancelled',
            ]);

            // Update payment if exists
            $payment = Payment::where('transaction_id', $transaction->id)->first();
            if ($payment) {
                $payment->update([
                    'status' => 'cancelled',
                ]);
            }

            DB::commit();

            return redirect()->route('admin.transactions.show', $transaction->id)
                ->with('success', 'Transaksi berhasil dibatalkan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Complete the specified transaction.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function complete($id)
    {
        $transaction = Transaction::findOrFail($id);

        // Only allow completing paid transactions
        if ($transaction->status !== 'paid') {
            return redirect()->route('admin.transactions.show', $transaction->id)
                ->with('error', 'Hanya transaksi dengan status paid yang dapat diselesaikan.');
        }

        // Update transaction status
        $transaction->update([
            'status' => 'completed',
        ]);

        return redirect()->route('admin.transactions.show', $transaction->id)
            ->with('success', 'Transaksi berhasil diselesaikan.');
    }

    /**
     * Generate invoice for the transaction.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function generateInvoice($id)
    {
        $transaction = Transaction::with([
            'customer',
            'table.room',
            'details',
            'payment'
        ])->findOrFail($id);

        // Log data transaction
        Log::info('Transaction Data:', [
            'transaction_id' => $transaction->id,
            'transaction_code' => $transaction->transaction_code,
            'total_price' => $transaction->total_price,
            'duration_hours' => $transaction->duration_hours,
            'details' => $transaction->details->map(function($detail) {
                return [
                    'id' => $detail->id,
                    'price_per_hour' => $detail->price_per_hour,
                    'discount' => $detail->discount,
                    'subtotal' => $detail->subtotal
                ];
            })->toArray()
        ]);

        // Pastikan data harga tersedia
        if (!$transaction->details->first()) {
            $transaction->details = collect([(object)[
                'duration_hours' => $transaction->duration_hours,
                'price_per_hour' => $transaction->price_per_hour,
                'discount' => $transaction->discount,
                'subtotal' => $transaction->price_per_hour * $transaction->duration_hours
            ]]);
        }

        return view('admin.transactions.invoice', compact('transaction'));
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

        // Exclude the current transaction if editing
        if ($excludeTransactionId) {
            $query->where('id', '!=', $excludeTransactionId);
        }

        return $query->count() === 0;
    }

    /**
     * Get the price for a table at the specified time.
     *
     * @param  \App\Models\Table  $table
     * @param  \Carbon\Carbon  $dateTime
     * @return \App\Models\Price|null
     */
    private function getPriceForTable($table, $dateTime)
    {
        $dayOfWeek = strtolower($dateTime->format('l'));
        $timeOfDay = $dateTime->format('H:i:s');

        // Log data untuk debugging
        Log::info('Getting Price For Table:', [
            'table_id' => $table->id,
            'day_of_week' => $dayOfWeek,
            'time_of_day' => $timeOfDay
        ]);

        // Find the appropriate price for the table and time
        $price = Price::where('table_id', $table->id)
            ->where(function($q) use ($dayOfWeek) {
                $q->where('day_type', $dayOfWeek)
                  ->orWhere('day_type', 'all');
            })
            ->where('start_time', '<=', $timeOfDay)
            ->where('end_time', '>=', $timeOfDay)
            ->where('status', true)
            ->first();

        // If no specific price is found, look for a default price
        if (!$price) {
            $price = Price::where('table_id', $table->id)
                ->where('day_type', 'all')
                ->where('status', true)
                ->first();
        }

        // Log hasil pencarian harga
        if ($price) {
            Log::info('Price Found:', [
                'price_id' => $price->id,
                'price_per_hour' => $price->price_per_hour,
                'day_type' => $price->day_type,
                'start_time' => $price->start_time,
                'end_time' => $price->end_time
            ]);
        } else {
            Log::warning('No Price Found for Table', [
                'table_id' => $table->id,
                'day_of_week' => $dayOfWeek,
                'time_of_day' => $timeOfDay
            ]);
        }

        return $price;
    }

    /**
     * Get all active tables with their current status.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getTablesStatus()
    {
        $tables = Table::with(['room', 'transactions' => function($q) {
                $q->where('status', '!=', 'cancelled')
                  ->where('start_time', '<=', now())
                  ->where('end_time', '>=', now());
            }])
            ->whereHas('room', function($q) {
                $q->where('status', true);
            })
            ->where('status', 'normal')
            ->get()
            ->map(function($table) {
                return [
                    'id' => $table->id,
                    'table_number' => $table->table_number,
                    'room' => $table->room->name,
                    'available' => $table->transactions->count() === 0,
                    'current_transaction' => $table->transactions->first() ? [
                        'id' => $table->transactions->first()->id,
                        'code' => $table->transactions->first()->transaction_code,
                        'customer' => $table->transactions->first()->customer->name,
                        'start_time' => $table->transactions->first()->start_time->format('H:i'),
                        'end_time' => $table->transactions->first()->end_time->format('H:i'),
                    ] : null,
                ];
            });

        return response()->json([
            'tables' => $tables,
        ]);
    }

    /**
     * Get transaction statistics for dashboard.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getStatistics()
    {
        // Get summary for last 7 days
        $last7Days = collect();
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $count = Transaction::whereDate('created_at', $date)->count();
            $revenue = Payment::whereDate('created_at', $date)
                ->where('status', 'paid')
                ->sum('total_amount');

            $last7Days->push([
                'date' => now()->subDays($i)->format('d M'),
                'count' => $count,
                'revenue' => $revenue,
            ]);
        }

        // Get status distribution
        $statusCounts = Transaction::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get()
            ->pluck('count', 'status')
            ->toArray();

        // Get payment method distribution
        $paymentMethodCounts = Payment::select('payment_method', DB::raw('count(*) as count'))
            ->where('status', 'paid')
            ->groupBy('payment_method')
            ->get()
            ->pluck('count', 'payment_method')
            ->toArray();

        return response()->json([
            'last7Days' => $last7Days,
            'statusCounts' => $statusCounts,
            'paymentMethodCounts' => $paymentMethodCounts,
        ]);
    }

    public function updatePaymentStatus(Request $request, $id)
    {
        try {
            $transaction = Transaction::findOrFail($id);

            // Update transaction status
            $transaction->status = 'paid';
            $transaction->payment_status = 'paid';
            $transaction->payment_details = $request->result;
            $transaction->save();

            return response()->json([
                'success' => true,
                'message' => 'Payment status updated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update payment status: ' . $e->getMessage()
            ], 500);
        }
    }
}
