<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    /**
     * Redirect to the appropriate dashboard based on user role
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // Log for debugging
        Log::info('DashboardController redirecting user', [
            'user_id' => $user->id,
            'role' => $user->role
        ]);

        // Redirect based on role
        if ($user->role === 'super_admin') {
            return redirect()->route('admin.dashboard.super');
        } elseif ($user->role === 'owner') {
            // Using the route name that actually exists
            return redirect()->route('admin.dashboard.owner');
        } elseif ($user->role === 'admin_pool') {
            return redirect()->route('admin.dashboard.admin_pool');
        } elseif ($user->role === 'customer') {
            return redirect()->route('customer.dashboard');
        }

        // Fallback for any undefined roles
        return redirect('/');
    }
}
