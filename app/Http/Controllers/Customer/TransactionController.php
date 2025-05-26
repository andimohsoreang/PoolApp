<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        // Ambil hanya data transaksi milik customer
        $transactions = \App\Models\Transaction::with(['table', 'table.room'])
            ->where('customer_id', Auth::user()->customer->id)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('customer.transaction.index', ['transactions' => $transactions]);
    }

    public function show($id)
    {
        Log::info('TransactionController@show called', [
            'user_id' => Auth::id(),
            'customer_id' => Auth::user()->customer->id ?? null,
            'transaction_id' => $id,
        ]);

        $transaction = Transaction::with(['table', 'table.room'])
            ->where('customer_id', Auth::user()->customer->id)
            ->find($id);

        if (!$transaction) {
            Log::warning('Transaction not found or not owned by user', [
                'user_id' => Auth::id(),
                'customer_id' => Auth::user()->customer->id ?? null,
                'transaction_id' => $id,
            ]);
            abort(404, 'Transaction not found or not owned by user');
        }

        Log::info('Transaction found', [
            'transaction' => $transaction->toArray()
        ]);

        return view('customer.transaction.show', ['transaction' => $transaction]);
    }

    /**
     * Menampilkan halaman cetak nota transaksi
     *
     * @param int $id ID transaksi
     * @return \Illuminate\View\View
     */
    public function printReceipt($id)
    {
        // Verifikasi kepemilikan transaksi
        $transaction = Transaction::with(['table.room', 'reservation'])
            ->where('customer_id', Auth::user()->customer->id)
            ->findOrFail($id);

        // Log akses untuk audit trail
        Log::info('Customer accessing transaction receipt', [
            'user_id' => Auth::id(),
            'customer_id' => Auth::user()->customer->id,
            'transaction_id' => $id,
            'ip_address' => request()->ip()
        ]);

        return view('customer.transaction.print', compact('transaction'));
    }

    /**
     * Menampilkan preview nota transaksi
     *
     * @param int $id ID transaksi
     * @return \Illuminate\View\View
     */
    public function previewReceipt($id)
    {
        // Verifikasi kepemilikan transaksi
        $transaction = Transaction::with(['table.room', 'reservation'])
            ->where('customer_id', Auth::user()->customer->id)
            ->findOrFail($id);

        // Log akses untuk audit trail
        Log::info('Customer previewing transaction receipt', [
            'user_id' => Auth::id(),
            'customer_id' => Auth::user()->customer->id,
            'transaction_id' => $id,
            'ip_address' => request()->ip()
        ]);

        return view('customer.transaction.preview', compact('transaction'));
    }
}
