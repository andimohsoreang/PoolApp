<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Notification;
use App\Models\Reservation;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class NotificationController extends Controller
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
     * Display a listing of the notifications.
     */
    public function index(Request $request)
    {
        $query = Notification::with(['user', 'reservation'])
            ->orderBy('created_at', 'desc');

        // Filter by type if provided
        if ($request->has('type') && $request->type != '') {
            $query->where('type', $request->type);
        }

        // Filter by status if provided
        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }

        // Filter for manual notifications if selected
        if ($request->has('manual') && $request->manual == '1') {
            $query->where('is_manual', true);
        }

        $notifications = $query->paginate(15);

        // Get counts for sidebar
        $unreadCount = Notification::where('status', 'unread')->count();
        $reservationCount = Notification::where('type', 'reservation')->count();
        $manualCount = Notification::where('is_manual', true)->count();

        return view('admin.notifications.index', compact(
            'notifications',
            'unreadCount',
            'reservationCount',
            'manualCount'
        ));
    }

    /**
     * Show the form for creating a new manual notification.
     */
    public function create()
    {
        // Get users to send notification to
        $users = User::where('role', 'customer')->get();

        return view('admin.notifications.create', compact('users'));
    }

    /**
     * Store a newly created manual notification in storage.
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'type' => 'required|string',
            'message' => 'required|string',
            'user_id' => 'required|exists:users,id',
        ]);

        $notification = Notification::create([
            'type' => $request->type,
            'message' => $request->message,
            'user_id' => $request->user_id,
            'is_manual' => true,
            'status' => 'unread',
        ]);

        return redirect()->route('admin.notifications.index')
            ->with('success', 'Manual notification created successfully.');
    }

    /**
     * Display the specified notification.
     */
    public function show(string $id)
    {
        $notification = Notification::with(['user', 'reservation'])->findOrFail($id);

        // Mark as read if unread
        if ($notification->status === 'unread') {
            $notification->markAsRead();
        }

        return view('admin.notifications.show', compact('notification'));
    }

    /**
     * Show the form for editing the specified notification.
     */
    public function edit(string $id)
    {
        $notification = Notification::findOrFail($id);

        // Only allow editing manual notifications
        if (!$notification->is_manual) {
            return redirect()->route('admin.notifications.index')
                ->with('error', 'Only manual notifications can be edited.');
        }

        $users = User::where('role', 'customer')->get();

        return view('admin.notifications.edit', compact('notification', 'users'));
    }

    /**
     * Update the specified notification in storage.
     */
    public function update(Request $request, string $id)
    {
        $notification = Notification::findOrFail($id);

        // Only allow updating manual notifications
        if (!$notification->is_manual) {
            return redirect()->route('admin.notifications.index')
                ->with('error', 'Only manual notifications can be updated.');
        }

        $this->validate($request, [
            'type' => 'required|string',
            'message' => 'required|string',
            'user_id' => 'required|exists:users,id',
        ]);

        $notification->update([
            'type' => $request->type,
            'message' => $request->message,
            'user_id' => $request->user_id,
        ]);

        return redirect()->route('admin.notifications.index')
            ->with('success', 'Notification updated successfully.');
    }

    /**
     * Remove the specified notification from storage.
     */
    public function destroy(string $id)
    {
        $notification = Notification::findOrFail($id);
        $notification->delete();

        return redirect()->route('admin.notifications.index')
            ->with('success', 'Notification deleted successfully.');
    }

    /**
     * Mark a notification as read.
     */
    public function markAsRead(string $id)
    {
        $notification = Notification::findOrFail($id);
        $notification->markAsRead();

        return redirect()->back()->with('success', 'Notification marked as read.');
    }

    /**
     * Mark a notification as unread.
     */
    public function markAsUnread(string $id)
    {
        $notification = Notification::findOrFail($id);
        $notification->markAsUnread();

        return redirect()->back()->with('success', 'Notification marked as unread.');
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllAsRead()
    {
        Notification::where('status', 'unread')->update([
            'status' => 'read',
            'read_at' => Carbon::now()
        ]);

        return redirect()->back()->with('success', 'All notifications marked as read.');
    }

    /**
     * Display only reservation notifications.
     */
    public function reservations()
    {
        $notifications = Notification::with(['user', 'reservation'])
            ->where('type', 'reservation')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        // Get counts for sidebar
        $unreadCount = Notification::where('status', 'unread')->count();
        $reservationCount = Notification::where('type', 'reservation')->count();
        $manualCount = Notification::where('is_manual', true)->count();

        return view('admin.notifications.reservations', compact(
            'notifications',
            'unreadCount',
            'reservationCount',
            'manualCount'
        ));
    }

    /**
     * Send a reservation notification from admin.
     */
    public function sendReservationNotification(Request $request)
    {
        $this->validate($request, [
            'reservation_id' => 'required|exists:reservations,id',
            'message' => 'required|string',
        ]);

        $reservation = Reservation::with('customer')->findOrFail($request->reservation_id);
        $customer = $reservation->customer;
        $message = $request->message;

        $notification = Notification::create([
            'type' => 'reservation',
            'message' => $message,
            'user_id' => $customer->user_id,
            'reservation_id' => $reservation->id,
            'is_manual' => true,
            'status' => 'unread',
        ]);

        return redirect()->back()->with('success', 'Reservation notification sent to customer.');
    }

    /**
     * Get the count of unread notifications.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCount()
    {
        $unread = Notification::where('status', 'unread')->count();
        $total = Notification::count();
        $reservation = Notification::where('type', 'reservation')->count();
        
        return response()->json([
            'unread' => $unread,
            'total' => $total,
            'reservation' => $reservation
        ]);
    }
}
