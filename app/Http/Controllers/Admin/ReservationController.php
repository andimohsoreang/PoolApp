<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Models\Table;
use App\Models\Customer;
use App\Models\Payment;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Midtrans\Config;
use Midtrans\Snap;
use Midtrans\Transaction as MidtransTransaction;

class ReservationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Calculate discount for a transaction
     *
     * @param float $subtotal
     * @param string|null $promoCode
     * @return array
     */
    private function calculateDiscount($subtotal, $promoCode = null)
    {
        $discount = 0;
        $promo = null;

        if ($promoCode) {
            // If there's a promo code, we would look it up and calculate the discount
            // For now, we'll just return 0 discount since we don't have the actual promo model
        }

        return [
            'discount' => $discount,
            'promo' => $promo,
            'total_after_discount' => $subtotal - $discount
        ];
    }

    public function index(Request $request)
    {
        $status = $request->status;
        $query = Reservation::with(['customer', 'table', 'table.room'])
            ->orderBy('created_at', 'desc');

        if ($status) {
            $query->where('status', $status);
        }

        if ($request->date) {
            $query->whereDate('start_time', $request->date);
        }

        if ($request->table_id) {
            $query->where('table_id', $request->table_id);
        }

        $reservations = $query->paginate(20);

        // Get all tables for filter dropdown
        $tables = Table::with('room')->get();

        return view('admin.reservations.index', compact('reservations', 'status', 'tables'));
    }

    /**
     * Display a listing of pending reservations.
     */
    public function pending()
    {
        $reservations = Reservation::with(['customer', 'table', 'table.room'])
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        // Get all tables for filter dropdown
        $tables = Table::with('room')->get();
        $status = 'pending';

        return view('admin.reservations.index', compact('reservations', 'status', 'tables'));
    }

    /**
     * Show the form for creating a new reservation.
     */
    public function create()
    {
        // Get available tables
        $tables = Table::where('status', 'normal')->with('room')->get();

        // Get customers for dropdown
        $customers = Customer::where('status', 'active')->orderBy('name')->get();

        return view('admin.reservations.create', compact('tables', 'customers'));
    }

    /**
     * Store a newly created reservation in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'table_id' => 'required|exists:billiard_tables,id',
            'start_time' => 'required|date_format:Y-m-d H:i',
            'end_time' => 'required|date_format:Y-m-d H:i|after:start_time',
            'promo_code' => 'nullable|string|max:50',
            'notes' => 'nullable|string|max:500',
        ]);

        $start = Carbon::parse($request->start_time);
        $end = Carbon::parse($request->end_time);

        // Calculate duration in hours
        $durationHours = $end->diffInMinutes($start) / 60;

        // Check for table availability
        $exists = Reservation::where('table_id', $request->table_id)
            ->whereIn('status', ['pending', 'approved', 'paid'])
            ->where(function($q) use ($start, $end) {
                $q->where('start_time', '<', $end)
                  ->where('end_time', '>', $start);
            })->exists();

        if ($exists) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Table is already reserved for the selected time slot.');
        }

        // Get price per hour from the table
        $price = DB::table('prices')
            ->where('table_id', $request->table_id)
            ->where('status', true)
            ->first();

        $pricePerHour = $price ? $price->price_per_hour : 70000.00; // Default price if not found

        // Create reservation
        $reservation = new Reservation();
        $reservation->customer_id = $request->customer_id;
        $reservation->table_id = $request->table_id;
        $reservation->start_time = $start;
        $reservation->end_time = $end;
        $reservation->duration_hours = $durationHours;
        $reservation->price_per_hour = $pricePerHour;
        $reservation->status = 'pending';
        $reservation->promo_code = $request->promo_code;
        $reservation->notes = $request->notes;
        $reservation->created_by = Auth::id();
        $reservation->save();

        return redirect()->route('admin.reservations.show', $reservation->id)
            ->with('success', 'Reservation created successfully and is pending approval.');
    }

    public function show($id)
    {
        $reservation = Reservation::with(['customer', 'table', 'table.room'])->findOrFail($id);
        $paymentDetails = $reservation->getFormattedPaymentDetails();

        return view('admin.reservations.show', compact('reservation', 'paymentDetails'));
    }

    public function edit($id)
    {
        $reservation = Reservation::with(['customer', 'table', 'table.room'])->findOrFail($id);
        return view('admin.reservations.edit', compact('reservation'));
    }

    public function update(Request $request, $id)
    {
        $reservation = Reservation::findOrFail($id);
        $request->validate([
            'start_time' => 'required|date_format:Y-m-d H:i',
            'end_time' => 'required|date_format:Y-m-d H:i',
        ]);
        $start = Carbon::parse($request->start_time);
        $end = Carbon::parse($request->end_time);
        // Validasi slot free (tidak overlap dengan reservation lain)
        $exists = Reservation::where('table_id', $reservation->table_id)
            ->where('id', '!=', $reservation->id)
            ->whereIn('status', ['pending', 'approved', 'paid'])
            ->where(function($q) use ($start, $end) {
                $q->where('start_time', '<', $end)
                  ->where('end_time', '>', $start);
            })->exists();
        if ($exists) {
            return redirect()->back()->with('error', 'Slot sudah terisi, silakan pilih slot lain.');
        }
        $reservation->start_time = $start;
        $reservation->end_time = $end;
        $reservation->save();
        return redirect()->route('admin.reservations.show', $reservation->id)->with('success', 'Reservasi berhasil diupdate.');
    }

    public function approve($id)
    {
        $reservation = Reservation::with(['customer', 'table', 'table.room'])->findOrFail($id);

        Log::channel('midtrans')->info('Starting reservation approval process', [
            'reservation_id' => $reservation->id,
            'customer_id' => $reservation->customer_id,
            'current_status' => $reservation->status
        ]);

        if ($reservation->status !== 'pending') {
            Log::channel('midtrans')->warning('Invalid reservation status for approval', [
                'reservation_id' => $reservation->id,
                'status' => $reservation->status
            ]);
            return redirect()->back()->with('error', 'Reservasi sudah diproses.');
        }

        // Calculate total price
        $totalPrice = $reservation->calculateTotalPrice();
        if ($totalPrice <= 0) {
            Log::channel('midtrans')->error('Failed to calculate total price', [
                'reservation_id' => $reservation->id,
                'calculated_price' => $totalPrice
            ]);
            return redirect()->back()->with('error', 'Tidak dapat menghitung harga reservasi.');
        }

        // Save calculated price details on reservation
        $reservation->total_price = $totalPrice;
        $reservation->save();

        // Set Midtrans config
        Config::$serverKey = config('services.midtrans.server_key');
        Config::$isProduction = config('services.midtrans.is_production');
        Config::$isSanitized = true;
        Config::$is3ds = true;

        // Generate standardized order_id
        $orderId = 'POOL-' . $reservation->id . '-' . time();

        // Prepare standardized params
        $params = [
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => (int) $totalPrice,
            ],
            'customer_details' => [
                'first_name' => $reservation->customer->name,
                'email' => $reservation->customer->email ?? '',
                'phone' => $reservation->customer->phone,
            ],
            'item_details' => [
                [
                    'id' => $reservation->table->id,
                    'price' => (int) $reservation->price_per_hour,
                    'quantity' => $reservation->duration_hours,
                    'name' => 'Meja ' . $reservation->table->table_number . ' - ' . $reservation->table->room->name,
                ]
            ],
        ];

        try {
            Log::channel('midtrans')->info('Creating Midtrans payment', [
                'reservation_id' => $reservation->id,
                'order_id' => $orderId,
                'amount' => $totalPrice,
                'params' => $params
            ]);

            $snapToken = Snap::getSnapToken($params);

            $reservation->status = 'approved';
            $reservation->status_approved_at = now();
            $reservation->approved_by = Auth::id();
            $reservation->payment_token = $snapToken;
            $reservation->payment_order_id = $orderId;
            $reservation->payment_expired_at = now()->addMinutes(3);
            $reservation->total_price = $totalPrice;
            $reservation->save();

            Log::channel('midtrans')->info('Payment token generated successfully', [
                'reservation_id' => $reservation->id,
                'order_id' => $orderId,
                'token_length' => strlen($snapToken)
            ]);

            return redirect()->route('admin.reservations.show', $reservation->id)
                ->with('success', 'Reservasi disetujui. Customer dapat melakukan pembayaran.');
        } catch (\Exception $e) {
            Log::channel('midtrans')->error('Failed to generate payment token', [
                'reservation_id' => $reservation->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                ->with('error', 'Gagal membuat token pembayaran: ' . $e->getMessage());
        }
    }

    public function reject(Request $request, $id)
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:255',
        ]);
        $reservation = Reservation::findOrFail($id);
        $reservation->status = 'rejected';
        $reservation->rejection_reason = $request->rejection_reason;
        $reservation->status_rejected_at = now();
        $reservation->save();

        // ... notifikasi ke customer jika perlu

        return redirect()->route('admin.reservations.index')->with('success', 'Reservasi ditolak.');
    }

    public function checkPayment(Request $request, $id)
    {
        $reservation = Reservation::with(['customer', 'table', 'table.room'])
            ->findOrFail($id);

        // Cek status reservasi
        if ($reservation->status !== 'paid') {
            return redirect()->back()
                ->with('error', 'Status reservasi harus "paid" untuk melakukan verifikasi pembayaran.');
        }

        // Cek apakah reservasi sudah memiliki transaksi
        if ($reservation->transaction_id) {
            $transaction = Transaction::with(['details', 'payment'])->find($reservation->transaction_id);

            if ($transaction) {
                // Update status reservasi
                $reservation->update([
                    'status' => 'completed',
                    'status_completed_at' => now()
                ]);

                return redirect()->route('admin.reservations.show', $reservation->id)
                    ->with('success', 'Pembayaran telah diverifikasi.');
            }
        }

        // Cek apakah sudah ada transaksi untuk reservasi ini
        $existingTransaction = Transaction::where('reservation_id', $reservation->id)->first();
        if ($existingTransaction) {
            // Update reservation dengan transaction_id yang sudah ada
            $reservation->update([
                'transaction_id' => $existingTransaction->id,
                'status' => 'completed',
                'status_completed_at' => now()
            ]);

            return redirect()->route('admin.reservations.show', $reservation->id)
                ->with('success', 'Pembayaran telah diverifikasi.');
        }

        // Jika tidak ada transaksi, buat transaksi baru
        try {
            DB::beginTransaction();

            // Pastikan price_per_hour tidak 0
            if ($reservation->price_per_hour <= 0) {
                // Ambil dari Price table
                $price = DB::table('prices')
                    ->where('table_id', $reservation->table_id)
                    ->where('status', true)
                    ->first();

                if ($price) {
                    $reservation->price_per_hour = $price->price_per_hour;
                    $reservation->save();

                    Log::info('Updated reservation price_per_hour', [
                        'reservation_id' => $reservation->id,
                        'old_price' => 0,
                        'new_price' => $price->price_per_hour
                    ]);
                } else {
                    // Fallback ke nilai default jika tidak ada harga ditemukan
                    $reservation->price_per_hour = 70000.00;
                    $reservation->save();

                    Log::warning('Using fallback price for reservation', [
                        'reservation_id' => $reservation->id,
                        'fallback_price' => 70000.00
                    ]);
                }
            }

            // Calculate subtotal
            $subtotal = $reservation->price_per_hour * $reservation->duration_hours;

            // Calculate discount using DiscountService
            $discountResult = $this->calculateDiscount($subtotal, $reservation->promo_code);

            // Generate transaction code
            $transactionCode = 'RESV-' . strtoupper(Str::random(8));
            while (Transaction::where('transaction_code', $transactionCode)->exists()) {
                $transactionCode = 'RESV-' . strtoupper(Str::random(8));
            }

            // Create transaction
            $transaction = Transaction::create([
                'customer_id' => $reservation->customer_id,
                'table_id' => $reservation->table_id,
                'user_id' => Auth::id(),
                'transaction_type' => 'reservation',
                'start_time' => $reservation->start_time,
                'end_time' => $reservation->end_time,
                'status' => 'paid',
                'total_price' => $discountResult['total_after_discount'],
                'payment_method' => 'e_payment',
                'transaction_code' => $transactionCode,
                'discount' => $discountResult['discount'],
                'promo_id' => $discountResult['promo'] ? $discountResult['promo']->id : null,
                'price_per_hour' => $reservation->price_per_hour,
                'duration_hours' => $reservation->duration_hours,
                'reservation_id' => $reservation->id
            ]);

            // Tambahkan log setelah membuat transaksi
            Log::info('Transaction created with discount data', [
                'transaction_id' => $transaction->id,
                'discount_amount' => $transaction->discount,
                'promo_id' => $transaction->promo_id,
                'original_discount' => $discountResult['discount'],
                'original_promo_id' => $discountResult['promo'] ? $discountResult['promo']->id : null
            ]);

            // Create transaction detail
            $transactionDetail = TransactionDetail::create([
                'transaction_id' => $transaction->id,
                'duration_hours' => $reservation->duration_hours,
                'price_per_hour' => $reservation->price_per_hour,
                'discount' => $discountResult['discount'],
                'promo_id' => $discountResult['promo'] ? $discountResult['promo']->id : null,
                'subtotal' => $subtotal,
            ]);

            // Log transaction detail creation
            Log::info('Transaction detail created', [
                'transaction_detail_id' => $transactionDetail->id,
                'transaction_id' => $transaction->id,
                'duration_hours' => $reservation->duration_hours,
                'price_per_hour' => $reservation->price_per_hour,
                'discount' => $discountResult['discount'],
                'promo_id' => $discountResult['promo'] ? $discountResult['promo']->id : null,
                'subtotal' => $subtotal
            ]);

            // Create payment record
            Payment::create([
                'transaction_id' => $transaction->id,
                'payment_method' => 'e_payment',
                'total_amount' => $discountResult['total_after_discount'],
                'amount_paid' => $discountResult['total_after_discount'],
                'change_amount' => 0,
                'status' => 'paid',
                'payment_date' => now(),
                'payment_details' => json_encode([
                    'reservation_id' => $reservation->id,
                    'payment_method' => 'e_payment',
                    'payment_date' => now()->format('Y-m-d H:i:s'),
                    'discount' => $discountResult['discount'],
                    'promo_id' => $discountResult['promo'] ? $discountResult['promo']->id : null,
                    'subtotal' => $subtotal,
                    'total_after_discount' => $discountResult['total_after_discount']
                ]),
            ]);

            // Update reservation status and transaction_id
            $reservation->update([
                'status' => 'completed',
                'transaction_id' => $transaction->id,
                'status_completed_at' => now()
            ]);

            // Tambahkan log detail diskon
            Log::info('Discount calculation for transaction', [
                'reservation_id' => $reservation->id,
                'subtotal' => $subtotal,
                'discount_result' => $discountResult,
                'calculated_discount' => $discountResult['discount'],
                'promo' => $discountResult['promo'] ? [
                    'id' => $discountResult['promo']->id,
                    'code' => $discountResult['promo']->code,
                    'name' => $discountResult['promo']->name
                ] : null
            ]);

            DB::commit();

            return redirect()->route('admin.reservations.show', $reservation->id)
                ->with('success', 'Pembayaran berhasil diverifikasi.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error verifying payment', [
                'reservation_id' => $reservation->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}
