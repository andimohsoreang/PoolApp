<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;

class UserManagementController extends Controller
{
    public function index()
    {
        $users = User::with('customer')->paginate(10);
        Log::info('UserManagementController@index: ' . $users->total() . ' users found.');
        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        return view('admin.users.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'username' => 'required|string|unique:users,username',
            'password' => 'required|string|min:6',
            'role'     => ['required', Rule::in(['super_admin', 'owner', 'admin_pool', 'customer'])],
            // Tambahkan validasi lain sesuai kebutuhan
        ]);

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'username' => $request->username,
            'password' => Hash::make($request->password),
            'role'     => $request->role,
            'phone'    => $request->phone,
            'whatsapp' => $request->whatsapp,
            'gender'   => $request->gender,
            'age'      => $request->age,
            'address'  => $request->address,
            'status'   => $request->status ?? true,
        ]);

        if ($user->role === 'customer') {
            Customer::create([
                'user_id'        => $user->id,
                'name'           => $request->name,
                'email'          => $request->email,
                'phone'          => $request->phone,
                'whatsapp'       => $request->whatsapp,
                'gender'         => $request->gender,
                'age'            => $request->age,
                'origin_address' => $request->origin_address,
                'current_address'=> $request->current_address,
                'category'       => $request->category ?? 'non_member',
                'visit_count'    => 0,
                'status'         => true,
            ]);
        }

        return redirect()->route('admin.users.index')->with('success', 'User berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $user = User::with('customer')->findOrFail($id);
        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, $id)
    {
        $user = User::with('customer')->findOrFail($id);

        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => ['required','email', Rule::unique('users')->ignore($user->id)],
            'username' => ['required','string', Rule::unique('users')->ignore($user->id)],
            'role'     => ['required', Rule::in(['super_admin', 'owner', 'admin_pool', 'customer'])],
            // Tambahkan validasi lain sesuai kebutuhan
        ]);

        $user->update([
            'name'     => $request->name,
            'email'    => $request->email,
            'username' => $request->username,
            'role'     => $request->role,
            'phone'    => $request->phone,
            'whatsapp' => $request->whatsapp,
            'gender'   => $request->gender,
            'age'      => $request->age,
            'address'  => $request->address,
            'status'   => $request->status ?? true,
        ]);

        if ($user->role === 'customer' && $user->customer) {
            $user->customer->update([
                'name'           => $request->name,
                'email'          => $request->email,
                'phone'          => $request->phone,
                'whatsapp'       => $request->whatsapp,
                'gender'         => $request->gender,
                'age'            => $request->age,
                'origin_address' => $request->origin_address,
                'current_address'=> $request->current_address,
                'category'       => $request->category ?? 'non_member',
                'status'         => $request->status ?? true,
            ]);
        }

        return redirect()->route('admin.users.index')->with('success', 'User berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();
        return redirect()->route('admin.users.index')->with('success', 'User berhasil dihapus.');
    }
}
