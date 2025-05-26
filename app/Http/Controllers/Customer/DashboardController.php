<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:customer');
    }

    /**
     * Display customer dashboard.
     */
    public function index()
    {
        $user = Auth::user();
        $customer = Customer::where('user_id', $user->id)->first();

        if (!$customer) {
            return redirect()->route('home')->with('error', 'Data customer tidak ditemukan.');
        }

        // Get recent transactions (last 30 days)
        $recentTransactions = Transaction::with(['table', 'table.room'])
            ->where('customer_id', $customer->id)
            ->where('created_at', '>=', Carbon::now()->subDays(30))
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        // Get statistics
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

        return view('customer.dashboard.index', [
            'customer' => $customer,
            'recentTransactions' => $recentTransactions,
            'recent_transactions' => $recentTransactions, // Alias untuk kecocokan dengan nama variable di view
            'stats' => $stats
        ]);
    }

    /**
     * Show customer profile
     */
    public function show()
    {
        $user = Auth::user();
        $customer = Customer::where('user_id', $user->id)->first();

        if (!$customer) {
            return redirect()->route('home')->with('error', 'Data customer tidak ditemukan.');
        }

        return view('customer.dashboard.show', compact('user', 'customer'));
    }

    /**
     * Show edit profile form
     */
    public function edit()
    {
        $user = Auth::user();
        $customer = Customer::where('user_id', $user->id)->first();

        if (!$customer) {
            return redirect()->route('home')->with('error', 'Data customer tidak ditemukan.');
        }

        return view('customer.dashboard.edit', compact('user', 'customer'));
    }

    /**
     * Update customer profile
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'phone' => 'required|string|max:20',
            'whatsapp' => 'nullable|string|max:20',
            'gender' => 'nullable|in:male,female',
            'age' => 'nullable|integer',
            'current_address' => 'nullable|string|max:255',
            'origin_address' => 'nullable|string|max:255',
        ]);

        // Update user data
        User::where('id', $user->id)->update([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'whatsapp' => $request->whatsapp,
            'gender' => $request->gender,
            'age' => $request->age,
            'address' => $request->current_address
        ]);

        // Update customer data
        $customer = Customer::where('user_id', $user->id)->first();
        if ($customer) {
            Customer::where('id', $customer->id)->update([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'whatsapp' => $request->whatsapp,
                'gender' => $request->gender,
                'age' => $request->age,
                'current_address' => $request->current_address,
                'origin_address' => $request->origin_address
            ]);
        }

        return redirect()->route('customer.profile.show')
            ->with('success', 'Profil berhasil diperbarui.');
    }

    /**
     * Show customer transaction history
     */
    public function transactionHistory()
    {
        $user = Auth::user();
        $customer = Customer::where('user_id', $user->id)->first();

        if (!$customer) {
            return redirect()->route('home')->with('error', 'Data customer tidak ditemukan.');
        }

        $transactions = Transaction::with(['table', 'table.room', 'payment', 'details'])
            ->where('customer_id', $customer->id)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('customer.dashboard.transactions', compact('customer', 'transactions'));
    }
}
