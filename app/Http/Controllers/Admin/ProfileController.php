<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    /**
     * Display the admin profile.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user = Auth::user();
        return view('admin.profile.index', compact('user'));
    }

    /**
     * Show the form for editing the admin profile.
     *
     * @return \Illuminate\Http\Response
     */
    public function edit()
    {
        $user = Auth::user();
        return view('admin.profile.edit', compact('user'));
    }

    /**
     * Update the admin profile.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                Rule::unique('users')->ignore($user->id),
            ],
            'phone' => 'required|string|max:15',
            'whatsapp' => 'nullable|string|max:15',
            'gender' => 'nullable|in:male,female',
            'age' => 'nullable|integer',
            'address' => 'nullable|string',
        ]);

        // Update user data
        User::where('id', $user->id)->update([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'whatsapp' => $request->whatsapp,
            'gender' => $request->gender,
            'age' => $request->age,
            'address' => $request->address,
        ]);

        return redirect()->route('admin.profile.index')
            ->with('success', 'Profil berhasil diperbarui');
    }

    /**
     * Show form to change password.
     *
     * @return \Illuminate\Http\Response
     */
    public function showChangePasswordForm()
    {
        return view('admin.profile.change-password');
    }

    /**
     * Change admin password.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function changePassword(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:6|confirmed',
        ]);

        // Verify current password
        if (!Hash::check($request->current_password, $user->password)) {
            return redirect()->back()
                ->with('error', 'Password saat ini tidak valid');
        }

        // Update password
        User::where('id', $user->id)->update([
            'password' => Hash::make($request->new_password)
        ]);

        return redirect()->route('admin.profile.index')
            ->with('success', 'Password berhasil diubah');
    }

    /**
     * Display customer profile.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function showCustomerProfile($id)
    {
        $customer = Customer::with('user')->findOrFail($id);
        return view('admin.profile.customer', compact('customer'));
    }

    /**
     * Show the form for editing the customer profile.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function editCustomerProfile($id)
    {
        $customer = Customer::with('user')->findOrFail($id);
        return view('admin.profile.edit-customer', compact('customer'));
    }

    /**
     * Update the customer profile.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function updateCustomerProfile(Request $request, $id)
    {
        $customer = Customer::findOrFail($id);
        $user = User::findOrFail($customer->user_id);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                Rule::unique('users')->ignore($user->id),
            ],
            'phone' => 'required|string|max:15',
            'whatsapp' => 'nullable|string|max:15',
            'gender' => 'nullable|in:male,female',
            'age' => 'nullable|integer',
            'current_address' => 'nullable|string',
            'origin_address' => 'nullable|string',
            'category' => 'required|in:member,non_member',
        ]);

        // Update user data
        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'whatsapp' => $request->whatsapp,
            'gender' => $request->gender,
            'age' => $request->age,
            'address' => $request->current_address,
        ]);

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
        ]);

        return redirect()->route('admin.profile.customer', $customer->id)
            ->with('success', 'Profil customer berhasil diperbarui');
    }

    /**
     * Reset customer password.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function resetCustomerPassword($id)
    {
        $customer = Customer::findOrFail($id);
        $user = User::findOrFail($customer->user_id);

        // Generate random password
        $newPassword = substr(md5(rand()), 0, 8);

        // Update user password
        User::where('id', $user->id)->update([
            'password' => Hash::make($newPassword)
        ]);

        return redirect()->route('admin.profile.customer', $customer->id)
            ->with('success', "Password customer berhasil direset. Password baru: {$newPassword}");
    }
}
