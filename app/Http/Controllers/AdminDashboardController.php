<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AdminDashboardController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:super_admin,owner,admin_pool');
    }

    /**
     * Show the admin dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        return view('admin.dashboard.index');
    }

    /**
     * Show the super admin dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function superDashboard()
    {
        return view('admin.dashboard.super');
    }

    /**
     * Show the owner dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function ownerDashboard()
    {
        return view('admin.dashboard.owner');
    }

    /**
     * Show the admin pool dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function adminPoolDashboard()
    {
        return view('admin.dashboard.admin_pool');
    }
}
