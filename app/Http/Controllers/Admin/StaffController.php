<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class StaffController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:owner,admin');
    }
    /**
     * Display a listing of the resource.
     */
        public function index()
    {
        // Only get staff users (admin_pool role)
        $staff = User::where('role', 'admin_pool')
                    ->orderBy('name')
                    ->paginate(10);

        return view('admin.staff.index', compact('staff'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.staff.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'position' => 'required|string|max:100',
        ]);

        $user = new User();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = Hash::make($request->password);
        $user->phone = $request->phone;
        $user->address = $request->address;
        $user->role = 'admin_pool'; // Staff role
        $user->status = 'active';
        $user->save();

        return redirect()->route('admin.staff.index')
            ->with('success', 'Staff berhasil ditambahkan');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $staff = User::findOrFail($id);

        // Make sure the user is a staff member
        if ($staff->role != 'admin_pool') {
            return redirect()->route('admin.staff.index')
                ->with('error', 'Hanya data staff yang dapat ditampilkan');
        }

        return view('admin.staff.show', compact('staff'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $staff = User::findOrFail($id);

        // Make sure the user is a staff member
        if ($staff->role != 'admin_pool') {
            return redirect()->route('admin.staff.index')
                ->with('error', 'Hanya dapat mengedit staff');
        }

        return view('admin.staff.edit', compact('staff'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $staff = User::findOrFail($id);

        // Make sure the user is a staff member
        if ($staff->role != 'admin_pool') {
            return redirect()->route('admin.staff.index')
                ->with('error', 'Hanya dapat mengedit staff');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $id,
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'position' => 'required|string|max:100',
            'status' => 'required|in:active,inactive',
        ]);

        // Update password if provided
        if ($request->filled('password')) {
            $request->validate([
                'password' => 'required|string|min:8|confirmed',
            ]);
            $staff->password = Hash::make($request->password);
        }

        $staff->name = $request->name;
        $staff->email = $request->email;
        $staff->phone = $request->phone;
        $staff->address = $request->address;
        $staff->position = $request->position;
        $staff->status = $request->status;
        $staff->save();

        return redirect()->route('admin.staff.index')
            ->with('success', 'Staff berhasil diperbarui');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $staff = User::findOrFail($id);

        // Make sure the user is a staff member
        if ($staff->role != 'admin_pool') {
            return redirect()->route('admin.staff.index')
                ->with('error', 'Hanya dapat menghapus staff');
        }

        // Soft delete if they have related records, hard delete if not
        $staff->delete();

        return redirect()->route('admin.staff.index')
            ->with('success', 'Staff berhasil dihapus');
    }
}
