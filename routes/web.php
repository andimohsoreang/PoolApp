<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\CustomerDashboardController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Admin\WalkinController;
use App\Http\Controllers\Admin\TransactionController;
use App\Http\Controllers\Customer\ReservationController;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Admin\UserManagementController;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

// Authentication Routes (replacing Auth::routes())
Route::middleware('guest')->group(function () {
    // Login
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);

    // Registration
    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register']);
});

// Logout
Route::post('/logout', [LogoutController::class, 'logout'])->name('logout');

// Dashboard redirector based on role
Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware('auth')
    ->name('dashboard');

// Admin routes
Route::middleware(['auth', 'role:super_admin,owner,admin_pool'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

        // Since we're in the admin.* named route group, these become admin.dashboard.super, etc.
    Route::get('/dashboard-super', [AdminDashboardController::class, 'superDashboard'])->name('dashboard.super');
    Route::get('/dashboard-owner', [AdminDashboardController::class, 'ownerDashboard'])->name('dashboard.owner');
    Route::get('/dashboard-admin-pool', [AdminDashboardController::class, 'adminPoolDashboard'])->name('dashboard.admin_pool');

    // Master Data routes
    Route::resource('rooms', 'App\Http\Controllers\Admin\RoomController');
    Route::resource('billiard-tables', 'App\Http\Controllers\Admin\BilliardTableController');
    Route::get('/tables/{table}/price', 'App\Http\Controllers\Admin\BilliardTableController@getPrice');
    Route::get('/prices/test', 'App\Http\Controllers\Admin\PriceController@testPrice')->name('prices.test');
    Route::get('/prices/get-current-time', 'App\Http\Controllers\Admin\PriceController@getCurrentTime')->name('prices.get-current-time');
    Route::resource('prices', 'App\Http\Controllers\Admin\PriceController');

    // Promo routes - custom routes first
    Route::get('/promos/test', 'App\Http\Controllers\Admin\PromoController@testPromo')->name('promos.test');
    Route::post('/promos/generate-code', 'App\Http\Controllers\Admin\PromoController@generateCode')->name('promos.generate-code');
    Route::resource('promos', 'App\Http\Controllers\Admin\PromoController');

    // Transaction routes
    Route::get('/transactions', [TransactionController::class, 'index'])->name('transactions.index');
    Route::get('/transactions/{id}/edit', [TransactionController::class, 'edit'])->name('transactions.edit');
    Route::put('/transactions/{id}', [TransactionController::class, 'update'])->name('transactions.update');
    Route::post('/transactions/update-payment-status/{id}', [TransactionController::class, 'updatePaymentStatus'])->name('transactions.update-payment-status');
    Route::get('/transactions/tables-status', 'App\Http\Controllers\Admin\TransactionController@getTablesStatus')->name('transactions.tables-status');
    Route::get('/transactions/statistics', 'App\Http\Controllers\Admin\TransactionController@getStatistics')->name('transactions.statistics');
    Route::get('/transactions/generate-invoice/{transaction}', 'App\Http\Controllers\Admin\TransactionController@generateInvoice')->name('transactions.generate-invoice');
    Route::put('/transactions/{transaction}/process-payment', 'App\Http\Controllers\Admin\TransactionController@processPayment')->name('transactions.process-payment');
    Route::put('/transactions/{transaction}/cancel', 'App\Http\Controllers\Admin\TransactionController@cancel')->name('transactions.cancel');
    Route::put('/transactions/{transaction}/complete', 'App\Http\Controllers\Admin\TransactionController@complete')->name('transactions.complete');

    // Owner-only transaction routes
    Route::middleware(['role:owner'])->group(function () {
        Route::get('/transactions/create', [TransactionController::class, 'create'])->name('transactions.create');
        Route::post('/transactions', [TransactionController::class, 'store'])->name('transactions.store');
    });

    // Other transaction routes
    Route::get('/transactions/{transaction}', [TransactionController::class, 'show'])->name('transactions.show');

    // Walk-in routes
    Route::group(['prefix' => 'walkin', 'as' => 'walkin.'], function () {
        Route::get('/', [WalkinController::class, 'index'])->name('index');
        Route::get('/create/{table}', [WalkinController::class, 'createTransaction'])->name('create');
        Route::post('/check-availability', [WalkinController::class, 'checkAvailability'])->name('checkAvailability');
        Route::post('/store', [WalkinController::class, 'storeTransaction'])->name('store');
        Route::get('/confirm-cash/{id}', [WalkinController::class, 'confirmCashPayment'])->name('confirmCashPayment');
        Route::post('/process-cash-payment/{id}', [WalkinController::class, 'processCashPayment'])->name('processCashPayment');
        Route::get('/confirm-e-payment/{id}', [WalkinController::class, 'confirmEPayment'])->name('confirmEPayment');
        Route::post('/process-e-payment', [WalkinController::class, 'processEPayment'])->name('processEPayment');
        Route::get('/success/{id}', [WalkinController::class, 'transactionSuccess'])->name('transactionSuccess');
        Route::get('/table-details', [WalkinController::class, 'getTableDetails'])->name('table-details');
        Route::post('/process-expired-sessions', [WalkinController::class, 'processExpiredSessions'])->name('processExpiredSessions');
        Route::post('/extend/{id}', [WalkinController::class, 'extendTransaction'])->name('extendTransaction');
        Route::post('/stop/{id}', [WalkinController::class, 'stopTransaction'])->name('stopTransaction');
    });

    Route::get('/reservations', 'App\Http\Controllers\Admin\ReservationController@index')->name('reservations.index');

    // User Management routes
    Route::resource('users', UserManagementController::class);

    // Staff Management routes
    Route::resource('staff', 'App\Http\Controllers\Admin\StaffController');

    // Customer Management routes
    Route::resource('customers', 'App\Http\Controllers\Admin\CustomerController');
    Route::put('/customers/{customer}/toggle-status', 'App\Http\Controllers\Admin\CustomerController@toggleStatus')->name('customers.toggle-status');
    Route::get('/customers/{customer}/transactions', 'App\Http\Controllers\Admin\CustomerController@transactions')->name('customers.transactions');
    Route::delete('/customers/{customer}/soft-delete', 'App\Http\Controllers\Admin\CustomerController@softDelete')->name('customers.soft-delete');

    // Admin Profile routes
    Route::get('/profile', 'App\Http\Controllers\Admin\ProfileController@index')->name('profile.index');
    Route::get('/profile/edit', 'App\Http\Controllers\Admin\ProfileController@edit')->name('profile.edit');
    Route::put('/profile/update', 'App\Http\Controllers\Admin\ProfileController@update')->name('profile.update');
    Route::get('/profile/change-password', 'App\Http\Controllers\Admin\ProfileController@showChangePasswordForm')->name('profile.change-password');
    Route::post('/profile/change-password', 'App\Http\Controllers\Admin\ProfileController@changePassword')->name('profile.change-password.update');

    // Reports
    Route::get('/reports/sales', 'App\Http\Controllers\Admin\ReportController@sales')->name('reports.sales');
    Route::get('/reports/tables', 'App\Http\Controllers\Admin\ReportController@tables')->name('reports.tables');
    Route::get('/reports/customers', 'App\Http\Controllers\Admin\ReportController@customers')->name('reports.customers');
    Route::get('/reports/sales/export-csv', 'App\Http\Controllers\Admin\ReportController@exportSalesCsv')->name('reports.export-sales-csv');

    // Financial Reports
    Route::get('/financial/reports', 'App\Http\Controllers\Admin\FinancialController@reports')->name('financial.reports');

    // Settings
    Route::get('/settings', 'App\Http\Controllers\Admin\SettingController@index')->name('settings.index');

    // Food & Beverages
    Route::prefix('food-beverages')->name('food-beverages.')->group(function () {
        Route::get('/orders', [App\Http\Controllers\Admin\FoodBeverageController::class, 'orders'])->name('orders');
        Route::get('/orders/{id}/details', [App\Http\Controllers\Admin\FoodBeverageController::class, 'orderDetails'])->name('orders.details');
        Route::post('/orders/{id}/process', [App\Http\Controllers\Admin\FoodBeverageController::class, 'processOrder'])->name('orders.process');
        Route::post('/orders/{id}/complete', [App\Http\Controllers\Admin\FoodBeverageController::class, 'completeOrder'])->name('orders.complete');
        Route::post('/orders/{id}/cancel', [App\Http\Controllers\Admin\FoodBeverageController::class, 'cancelOrder'])->name('orders.cancel');
        Route::get('/{foodBeverage}/manage-ratings', [App\Http\Controllers\Admin\FoodBeverageController::class, 'manageRatings'])->name('manage-ratings');
        Route::post('/ratings/{ratingId}/toggle-approval', [App\Http\Controllers\Admin\FoodBeverageController::class, 'toggleRatingApproval'])->name('toggle-rating-approval');
        Route::delete('/ratings/{ratingId}', [App\Http\Controllers\Admin\FoodBeverageController::class, 'deleteRating'])->name('delete-rating');
        Route::delete('/images/{image}', [App\Http\Controllers\Admin\FoodBeverageController::class, 'deleteImage'])->name('delete-image');
        Route::post('/images/{image}/set-primary', [App\Http\Controllers\Admin\FoodBeverageController::class, 'setPrimaryImage'])->name('set-primary-image');
    });
    Route::resource('food-beverages', App\Http\Controllers\Admin\FoodBeverageController::class);

    // E-Payment Routes
    Route::get('/transactions/{id}/e-payment', [WalkinController::class, 'showEPayment'])->name('transactions.e-payment');
    Route::post('/transactions/update-payment-status/{id}', [WalkinController::class, 'updatePaymentStatus'])->name('transactions.update-payment-status');

    // Walk-in routes
    Route::get('walkin', [WalkinController::class, 'index'])->name('walkin.index');
    Route::get('walkin/timeline/{table}', [WalkinController::class, 'timeline'])->name('walkin.timeline');
    Route::get('walkin/server-time', [\App\Http\Controllers\Admin\WalkinController::class, 'serverTime'])->name('walkin.serverTime');

    // Reservation management (admin)
    Route::prefix('reservations')->name('reservations.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\ReservationController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\Admin\ReservationController::class, 'create'])->name('create');
        Route::post('/store', [\App\Http\Controllers\Admin\ReservationController::class, 'store'])->name('store');
        Route::get('/pending', [\App\Http\Controllers\Admin\ReservationController::class, 'pending'])->name('pending');
        Route::get('/{reservation}', [\App\Http\Controllers\Admin\ReservationController::class, 'show'])->name('show');
        Route::get('/{reservation}/edit', [\App\Http\Controllers\Admin\ReservationController::class, 'edit'])->name('edit');
        Route::post('/{reservation}/update', [\App\Http\Controllers\Admin\ReservationController::class, 'update'])->name('update');
        Route::post('/{reservation}/approve', [\App\Http\Controllers\Admin\ReservationController::class, 'approve'])->name('approve');
        Route::post('/{reservation}/reject', [\App\Http\Controllers\Admin\ReservationController::class, 'reject'])->name('reject');
        Route::post('/{reservation}/check-payment', [\App\Http\Controllers\Admin\ReservationController::class, 'checkPayment'])->name('check-payment');
    });

    // Notification Routes
    Route::get('/notifications/reservations', 'App\Http\Controllers\Admin\NotificationController@reservations')->name('notifications.reservations');
    Route::post('/notifications/mark-all-read', 'App\Http\Controllers\Admin\NotificationController@markAllAsRead')->name('notifications.mark-all-read');
    Route::post('/notifications/{id}/mark-read', 'App\Http\Controllers\Admin\NotificationController@markAsRead')->name('notifications.mark-read');
    Route::post('/notifications/{id}/mark-unread', 'App\Http\Controllers\Admin\NotificationController@markAsUnread')->name('notifications.mark-unread');
    Route::post('/notifications/reservation', 'App\Http\Controllers\Admin\NotificationController@sendReservationNotification')->name('notifications.send-reservation');
    Route::get('/notifications/count', 'App\Http\Controllers\Admin\NotificationController@getCount')->name('notifications.count');
    Route::resource('notifications', 'App\Http\Controllers\Admin\NotificationController');

    // Socket.IO testing routes
    Route::get('/socket-test', function() {
        return view('admin.socket-test');
    })->name('socket-test');

    Route::post('/send-test-notification', function() {
        // Force response to be JSON regardless of Accept header
        header('Content-Type: application/json');

        try {
            \Illuminate\Support\Facades\Log::info('Test notification requested');

                        // Create simple notification object with only valid fields
            $notification = new \App\Models\Notification();
            $notification->type = 'test';
            $notification->message = 'This is a test notification from the socket test page at ' . now()->format('H:i:s');
            $notification->status = 'unread';
            $notification->is_manual = true;

            // Add data field now that we have it
            $notification->data = [
                'source' => 'socket-test',
                'timestamp' => now()->toIso8601String(),
                'test_message' => 'Test notification with new data column'
            ];

            // Debug notification fields
            \Illuminate\Support\Facades\Log::info('Notification fields', [
                'fillable' => $notification->getFillable(),
                'attributes' => $notification->getAttributes(),
                'has_data_field' => isset($notification->data),
                'data' => $notification->data
            ]);

            $notification->save();

            // Log all reservation data to check if the model exists
            \Illuminate\Support\Facades\Log::info('Available reservations', [
                'count' => \App\Models\Reservation::count()
            ]);

            // Get a reservation for the test notification
            try {
                $mockReservation = \App\Models\Reservation::first();

                if ($mockReservation) {
                                        // Send the real-time notification event
                    \Illuminate\Support\Facades\Log::info('Broadcasting notification event', [
                        'notification_id' => $notification->id,
                        'reservation_id' => $mockReservation->id
                    ]);

                    // Send via Event class
                    event(new \App\Events\NewReservationNotification($notification, $mockReservation));

                    // Also try direct Socket.IO emit for testing
                    try {
                        $socketData = [
                            'notification' => [
                                'id' => $notification->id,
                                'message' => $notification->message,
                                'type' => $notification->type
                            ],
                            'timestamp' => now()->toIso8601String()
                        ];

                                                // Try direct broadcast using Laravel's built-in Redis facade
                        \Illuminate\Support\Facades\Redis::publish('admin-notifications', json_encode([
                            'event' => 'new-reservation',
                            'data' => $socketData
                        ]));

                        \Illuminate\Support\Facades\Log::info('Sent direct Redis broadcast');
                    } catch (\Exception $e) {
                        \Illuminate\Support\Facades\Log::error('Redis broadcast error: ' . $e->getMessage());
                    }
                } else {
                    \Illuminate\Support\Facades\Log::warning('Could not broadcast notification: No reservations found');
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Error broadcasting notification: ' . $e->getMessage());
            }

            // Return a simple JSON response with notification object structure
            echo json_encode([
                'success' => true,
                'message' => 'Test notification created successfully',
                'notification' => [
                    'id' => $notification->id,
                    'message' => $notification->message,
                    'type' => $notification->type,
                    'status' => $notification->status
                ],
                'timestamp' => now()->toIso8601String()
            ]);
            exit;

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error in test notification: ' . $e->getMessage(), [
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            // Return error as direct JSON (bypassing Laravel's response system)
            echo json_encode([
                'success' => false,
                'message' => 'Error in test notification',
                'error' => $e->getMessage()
            ]);
            exit;
        }
    })->name('send-test-notification');

    // Dashboard routes are defined at the top of this middleware group
});

// Customer routes
Route::middleware(['auth', 'role:customer'])->prefix('customer')->name('customer.')->group(function () {
    // Log::info('Customer middleware group accessed');
    Route::get('/dashboard', [CustomerDashboardController::class, 'index'])->name('dashboard');

    Route::get('/profile', 'App\Http\Controllers\Customer\ProfileController@index')->name('profile');
    Route::get('/bookings', 'App\Http\Controllers\Customer\BookingController@index')->name('bookings');
    Route::get('/book', 'App\Http\Controllers\Customer\BookingController@create')->name('book');
    Route::get('/book/{table}', 'App\Http\Controllers\Customer\BookingController@table')->name('book.table');
    Route::get('/transactions', 'App\Http\Controllers\Customer\TransactionController@index')->name('transactions');

    // Food & Beverages Menu
    Route::prefix('menu')->name('food-beverages.')->group(function () {
        Route::get('/', [App\Http\Controllers\Customer\FoodBeverageController::class, 'index'])->name('index');
        Route::get('/{foodBeverage}', [App\Http\Controllers\Customer\FoodBeverageController::class, 'show'])->name('show');
        Route::post('/{foodBeverage}/rate', [App\Http\Controllers\Customer\FoodBeverageController::class, 'submitRating'])->name('rate');
        Route::delete('/{foodBeverage}/rating', [App\Http\Controllers\Customer\FoodBeverageController::class, 'deleteRating'])->name('delete-rating');
    });

    // Reservation routes
    Route::prefix('reservation')->name('reservation.')->group(function () {
        Route::get('/history', [ReservationController::class, 'history'])->name('history');
        Route::get('/', [ReservationController::class, 'index'])->name('index');
        Route::get('/create', [ReservationController::class, 'create'])->name('create');
        Route::post('/', [ReservationController::class, 'store'])->name('store');
        Route::get('/getTableTimeline', [ReservationController::class, 'getTableTimeline'])->name('getTableTimeline');
        Route::get('/timeline', [ReservationController::class, 'timeline'])->name('timeline');
        Route::get('/notifications', [ReservationController::class, 'getNotifications'])->name('notifications');
        Route::get('/pay/{reservation}', [ReservationController::class, 'pay'])->name('pay');
        Route::post('/payment-callback/{reservation}', [ReservationController::class, 'paymentCallback'])->name('payment-callback');
        Route::post('/cancel/{reservation}', [ReservationController::class, 'cancel'])->name('cancel');
        Route::get('/{reservation}/check-status', [ReservationController::class, 'checkStatus'])->name('check-status');
        Route::get('/{reservation}', [ReservationController::class, 'show'])->name('show');
    });

    // Transaction Routes
    Route::prefix('transaction')->name('transaction.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Customer\TransactionController::class, 'index'])->name('index');
        Route::get('/{id}', [\App\Http\Controllers\Customer\TransactionController::class, 'show'])->name('show');
        Route::get('/{id}/preview', [\App\Http\Controllers\Customer\TransactionController::class, 'previewReceipt'])->name('preview');
        Route::get('/{id}/print', [\App\Http\Controllers\Customer\TransactionController::class, 'printReceipt'])->name('print');
    });


});

