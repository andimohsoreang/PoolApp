<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\Transaction;
use App\Models\Table;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CustomerDashboardController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:customer');
    }

    /**
     * Show the customer dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $customer = Auth::user()->customer;

        // Get active reservations
        $reservations = Reservation::where('customer_id', $customer->id)
            ->whereIn('status', ['pending', 'approved', 'paid'])
            ->with(['table', 'table.room'])
            ->latest()
            ->get();

        // Get completed reservations
        $completedReservations = Reservation::where('customer_id', $customer->id)
            ->whereIn('status', ['completed', 'cancelled'])
            ->with(['table', 'table.room'])
            ->latest()
            ->take(5)
            ->get();

        // Get recent transactions
        $transactions = Transaction::where('customer_id', $customer->id)
            ->with(['table', 'table.room'])
            ->latest()
            ->take(5)
            ->get();

        // Recent transactions for display in the dashboard
        $recent_transactions = $transactions;

        // Get available tables
        $tables = Table::whereHas('room', function($q) {
            $q->where('status', true);
        })->where('status', 'normal')->get();

        // Get rooms
        $rooms = Room::where('status', true)->get();

        // Get statistics for dashboard
        $totalTransactions = Transaction::where('customer_id', $customer->id)->count();
        $completedTransactions = Transaction::where('customer_id', $customer->id)
            ->where('status', 'completed')
            ->count();
        $pendingTransactions = Transaction::where('customer_id', $customer->id)
            ->whereIn('status', ['pending', 'approved'])
            ->count();
        $totalSpent = Transaction::where('customer_id', $customer->id)
            ->whereIn('status', ['paid', 'completed'])
            ->sum('total_price');

        $stats = [
            'total_transactions' => $totalTransactions,
            'completed_transactions' => $completedTransactions,
            'total_spent' => $totalSpent,
            'visit_count' => $customer->visit_count ?? 0,
            'pending' => $pendingTransactions
        ];

        return view('customer.dashboard.index', compact(
            'reservations',
            'completedReservations',
            'transactions',
            'tables',
            'rooms',
            'stats',
            'recent_transactions'
        ));
    }
}
