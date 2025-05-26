<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\User;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class CustomerController extends Controller
{
    /**
     * Display a listing of customers.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = Customer::with('user')->select('customers.*');

        // Filter by search term
        if ($request->has('search') && $request->search) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('name', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('email', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('phone', 'LIKE', "%{$searchTerm}%");
            });
        }

        // Filter by category
        if ($request->has('category') && $request->category) {
            $query->where('category', $request->category);
        }

        // Filter by status
        if ($request->has('status')) {
            $status = $request->status === 'active' ? 1 : 0;
            $query->where('status', $status);
        }

        $customers = $query->orderBy('created_at', 'desc')->paginate(10);

        return view('admin.customers.index', compact('customers'));
    }

    /**
     * Show the form for creating a new customer.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('admin.customers.create');
    }

    /**
     * Store a newly created customer in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Validasi input
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|string|max:15',
            'whatsapp' => 'nullable|string|max:15',
            'password' => 'required|string|min:6',
            'gender' => 'nullable|in:male,female',
            'age' => 'nullable|integer',
            'category' => 'required|in:member,non_member',
            'current_address' => 'nullable|string',
            'origin_address' => 'nullable|string',
            'status' => 'required|boolean',
        ]);

        DB::beginTransaction();
        try {
            // Buat user baru
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'username' => generateUsername($request->name, $request->email),
                'password' => Hash::make($request->password),
                'role' => 'customer',
                'phone' => $request->phone,
                'whatsapp' => $request->whatsapp,
                'gender' => $request->gender,
                'age' => $request->age,
                'address' => $request->current_address,
                'status' => $request->status,
            ]);

            // Buat customer baru
            $customer = Customer::create([
                'user_id' => $user->id,
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'whatsapp' => $request->whatsapp,
                'gender' => $request->gender,
                'age' => $request->age,
                'current_address' => $request->current_address,
                'origin_address' => $request->origin_address,
                'category' => $request->category,
                'status' => $request->status,
            ]);

            DB::commit();
            return redirect()->route('admin.customers.index')
                ->with('success', 'Customer berhasil ditambahkan');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified customer.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $customer = Customer::with('user')->findOrFail($id);

        // Hitung statistik customer
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

        // Ambil 5 transaksi terbaru
        $recentTransactions = Transaction::with(['table', 'table.room'])
            ->where('customer_id', $customer->id)
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        $stats = [
            'total_transactions' => $totalTransactions,
            'completed_transactions' => $completedTransactions,
            'total_spent' => $totalSpent,
            'pending' => $pendingTransactions
        ];

        return view('admin.customers.show', compact('customer', 'stats', 'recentTransactions'));
    }

    /**
     * Show the form for editing the specified customer.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $customer = Customer::with('user')->findOrFail($id);
        return view('admin.customers.edit', compact('customer'));
    }

    /**
     * Update the specified customer in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $customer = Customer::findOrFail($id);
        $user = User::findOrFail($customer->user_id);

        // Validasi input
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                Rule::unique('users')->ignore($user->id),
            ],
            'phone' => 'required|string|max:15',
            'whatsapp' => 'nullable|string|max:15',
            'password' => 'nullable|string|min:6',
            'gender' => 'nullable|in:male,female',
            'age' => 'nullable|integer',
            'category' => 'required|in:member,non_member',
            'current_address' => 'nullable|string',
            'origin_address' => 'nullable|string',
            'status' => 'required|boolean',
        ]);

        DB::beginTransaction();
        try {
            // Update user data
            $userData = [
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'whatsapp' => $request->whatsapp,
                'gender' => $request->gender,
                'age' => $request->age,
                'address' => $request->current_address,
                'status' => $request->status,
            ];

            // Jika password diubah, update password
            if ($request->filled('password')) {
                $userData['password'] = Hash::make($request->password);
            }

            $user->update($userData);

            // Update customer data
            $customer->update([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'whatsapp' => $request->whatsapp,
                'gender' => $request->gender,
                'age' => $request->age,
                'current_address' => $request->current_address,
                'origin_address' => $request->origin_address,
                'category' => $request->category,
                'status' => $request->status,
            ]);

            DB::commit();
            return redirect()->route('admin.customers.show', $customer->id)
                ->with('success', 'Data customer berhasil diperbarui');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified customer from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        // Cek apakah customer memiliki transaksi
        $customer = Customer::findOrFail($id);
        $transactionCount = Transaction::where('customer_id', $id)->count();

        if ($transactionCount > 0) {
            return redirect()->back()
                ->with('error', 'Customer tidak dapat dihapus karena memiliki transaksi');
        }

        DB::beginTransaction();
        try {
            // Hapus user terkait
            if ($customer->user_id) {
                User::where('id', $customer->user_id)->delete();
            }

            // Hapus customer
            $customer->delete();

            DB::commit();
            return redirect()->route('admin.customers.index')
                ->with('success', 'Customer berhasil dihapus');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Soft delete the specified customer
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function softDelete($id)
    {
        $customer = Customer::findOrFail($id);
        $user = User::findOrFail($customer->user_id);

        DB::beginTransaction();
        try {
            // Set status to inactive
            $customer->update([
                'status' => false,
                'deleted_at' => now()
            ]);

            // Set user status to inactive
            $user->update([
                'status' => false,
                'deleted_at' => now()
            ]);

            DB::commit();
            return redirect()->route('admin.customers.index')
                ->with('success', 'Customer berhasil dinonaktifkan');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Toggle customer status.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function toggleStatus($id)
    {
        $customer = Customer::findOrFail($id);
        $user = User::findOrFail($customer->user_id);

        DB::beginTransaction();
        try {
            // Toggle status
            $newStatus = !$customer->status;

            // Update customer status
            $customer->update(['status' => $newStatus]);

            // Update user status
            $user->update(['status' => $newStatus]);

            DB::commit();

            $statusText = $newStatus ? 'diaktifkan' : 'dinonaktifkan';
            return redirect()->back()
                ->with('success', "Customer berhasil {$statusText}");
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * View customer transaction history.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function transactions($id)
    {
        $customer = Customer::findOrFail($id);
        $transactions = Transaction::with(['table', 'table.room', 'payment'])
            ->where('customer_id', $customer->id)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('admin.customers.transactions', compact('customer', 'transactions'));
    }
}

// Helper function to generate username
function generateUsername($name, $email)
{
    // Remove spaces and special characters from name
    $username = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $name));

    // If username is less than 5 characters, add part of email
    if (strlen($username) < 5) {
        $emailPart = strtok($email, '@');
        $username .= strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $emailPart));
    }

    // Truncate if too long
    $username = substr($username, 0, 15);

    // Add random number to make it unique
    $username .= rand(100, 999);

    return $username;
}
