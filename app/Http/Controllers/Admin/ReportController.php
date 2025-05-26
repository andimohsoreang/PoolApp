<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Payment;
use App\Models\Table;
use App\Models\Room;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
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
     * Display sales report.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function sales(Request $request)
    {
        // Set default date range if not provided
        $start_date = $request->start_date ? Carbon::parse($request->start_date)->startOfDay() : Carbon::now()->startOfMonth();
        $end_date = $request->end_date ? Carbon::parse($request->end_date)->endOfDay() : Carbon::now()->endOfDay();

        // Filter by transaction type
        $transaction_type = $request->transaction_type;

        // Get sales data
        $query = Transaction::with(['table', 'table.room', 'customer', 'payment'])
            ->where('created_at', '>=', $start_date)
            ->where('created_at', '<=', $end_date)
            ->whereIn('status', ['completed', 'paid']);

        if ($transaction_type) {
            $query->where('transaction_type', $transaction_type);
        }

        $transactions = $query->orderBy('created_at', 'desc')->get();

        // Calculate summary statistics
        $totalRevenue = $transactions->sum('total_price');
        $totalTransactions = $transactions->count();
        $averageTransaction = $totalTransactions > 0 ? $totalRevenue / $totalTransactions : 0;

        // Group data by date for chart
        $dailySales = $transactions->groupBy(function ($transaction) {
            return Carbon::parse($transaction->created_at)->format('Y-m-d');
        })->map(function ($group) {
            return [
                'count' => $group->count(),
                'total' => $group->sum('total_price')
            ];
        });

        // Group by payment method
        $paymentMethods = $transactions->groupBy('payment_method')->map->count();

        // Group by transaction type
        $transactionTypes = $transactions->groupBy('transaction_type')->map->count();

        // Top selling tables
        $topTables = $transactions->groupBy('table_id')
            ->map(function ($group) {
                $table = $group->first()->table;
                return [
                    'table_id' => $table->id,
                    'table_number' => $table->table_number,
                    'room_name' => $table->room->name,
                    'count' => $group->count(),
                    'revenue' => $group->sum('total_price')
                ];
            })
            ->sortByDesc('revenue')
            ->take(5)
            ->values();

        return view('admin.reports.sales', compact(
            'transactions',
            'start_date',
            'end_date',
            'transaction_type',
            'totalRevenue',
            'totalTransactions',
            'averageTransaction',
            'dailySales',
            'paymentMethods',
            'transactionTypes',
            'topTables'
        ));
    }

    /**
     * Display table usage report.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function tables(Request $request)
    {
        // Set default date range if not provided
        $start_date = $request->start_date ? Carbon::parse($request->start_date)->startOfDay() : Carbon::now()->startOfMonth();
        $end_date = $request->end_date ? Carbon::parse($request->end_date)->endOfDay() : Carbon::now()->endOfDay();

        // Filter by room if provided
        $room_id = $request->room_id;

        // Get tables data with their transactions
        $query = Table::with(['room', 'transactions' => function ($query) use ($start_date, $end_date) {
            $query->where('created_at', '>=', $start_date)
                  ->where('created_at', '<=', $end_date)
                  ->whereIn('status', ['completed', 'paid']);
        }]);

        if ($room_id) {
            $query->where('room_id', $room_id);
        }

        $tables = $query->get();

        // Calculate table utilization metrics
        $tableMetrics = $tables->map(function ($table) use ($start_date, $end_date) {
            $transactions = $table->transactions;
            $totalHours = 0;
            $totalRevenue = 0;
            $totalTransactions = $transactions->count();

            foreach ($transactions as $transaction) {
                // Calculate hours used
                $startTime = Carbon::parse($transaction->start_time);
                $endTime = Carbon::parse($transaction->end_time ?: $transaction->created_at);
                $durationInHours = $startTime->diffInMinutes($endTime) / 60;
                $totalHours += $durationInHours;

                // Sum revenue
                $totalRevenue += $transaction->total_price;
            }

            // Calculate average hourly rate
            $averageHourlyRate = $totalHours > 0 ? $totalRevenue / $totalHours : 0;

            // Calculate utilization percentage
            $dateRange = $start_date->diffInDays($end_date) + 1;
            $totalPossibleHours = $dateRange * 24; // Assuming 24 hours possible per day
            $utilizationRate = $totalPossibleHours > 0 ? ($totalHours / $totalPossibleHours) * 100 : 0;

            return [
                'table_id' => $table->id,
                'table_number' => $table->table_number,
                'room_name' => $table->room->name,
                'room_id' => $table->room_id,
                'total_transactions' => $totalTransactions,
                'total_hours' => round($totalHours, 2),
                'total_revenue' => $totalRevenue,
                'average_hourly_rate' => round($averageHourlyRate, 2),
                'utilization_rate' => round($utilizationRate, 2),
            ];
        });

        // Group by room for chart
        $roomUtilization = $tableMetrics->groupBy('room_id')->map(function ($group) {
            $roomName = $group->first()['room_name'];
            $totalHours = $group->sum('total_hours');
            $totalRevenue = $group->sum('total_revenue');
            $totalTables = $group->count();
            $averageUtilization = $group->avg('utilization_rate');

            return [
                'room_name' => $roomName,
                'total_hours' => round($totalHours, 2),
                'total_revenue' => $totalRevenue,
                'total_tables' => $totalTables,
                'average_utilization' => round($averageUtilization, 2),
            ];
        })->values();

        // Get rooms for filter
        $rooms = Room::all();

        return view('admin.reports.tables', compact(
            'tableMetrics',
            'start_date',
            'end_date',
            'room_id',
            'rooms',
            'roomUtilization'
        ));
    }

    /**
     * Display customer report.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function customers(Request $request)
    {
        // Set default date range if not provided
        $start_date = $request->start_date ? Carbon::parse($request->start_date)->startOfDay() : Carbon::now()->startOfMonth();
        $end_date = $request->end_date ? Carbon::parse($request->end_date)->endOfDay() : Carbon::now()->endOfDay();

        // Filter by customer category if provided
        $category = $request->category;

        // Get customers with their transactions in date range
        $query = Customer::with(['user', 'transactions' => function ($query) use ($start_date, $end_date) {
            $query->where('created_at', '>=', $start_date)
                  ->where('created_at', '<=', $end_date)
                  ->whereIn('status', ['completed', 'paid']);
        }]);

        if ($category) {
            $query->where('category', $category);
        }

        $customers = $query->get();

        // Calculate customer metrics
        $customerMetrics = $customers->map(function ($customer) {
            $transactions = $customer->transactions;
            $totalTransactions = $transactions->count();
            $totalSpent = $transactions->sum('total_price');
            $averageSpent = $totalTransactions > 0 ? $totalSpent / $totalTransactions : 0;
            $lastVisit = $transactions->max('created_at');

            return [
                'customer_id' => $customer->id,
                'name' => $customer->name,
                'email' => $customer->email,
                'phone' => $customer->phone,
                'category' => $customer->category,
                'total_transactions' => $totalTransactions,
                'total_spent' => $totalSpent,
                'average_spent' => round($averageSpent, 2),
                'last_visit' => $lastVisit ? Carbon::parse($lastVisit)->format('Y-m-d') : 'Never',
                'status' => $customer->status,
            ];
        });

        // Sort by total spent descending
        $customerMetrics = $customerMetrics->sortByDesc('total_spent')->values();

        // Top spending customers
        $topCustomers = $customerMetrics->take(10);

        // Group by category
        $categoryDistribution = $customers->groupBy('category')
            ->map->count()
            ->toArray();

        // New vs returning customers
        $newCustomers = Customer::where('created_at', '>=', $start_date)
            ->where('created_at', '<=', $end_date)
            ->count();

        $returningCustomers = $customerMetrics->where('total_transactions', '>', 1)->count();

        return view('admin.reports.customers', compact(
            'customerMetrics',
            'start_date',
            'end_date',
            'category',
            'topCustomers',
            'categoryDistribution',
            'newCustomers',
            'returningCustomers'
        ));
    }

    /**
     * Export sales report to CSV.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function exportSalesCsv(Request $request)
    {
        // Set date range
        $start_date = $request->start_date ? Carbon::parse($request->start_date)->startOfDay() : Carbon::now()->startOfMonth();
        $end_date = $request->end_date ? Carbon::parse($request->end_date)->endOfDay() : Carbon::now()->endOfDay();
        $transaction_type = $request->transaction_type;

        // Query transactions
        $query = Transaction::with(['table', 'table.room', 'customer', 'payment'])
            ->where('created_at', '>=', $start_date)
            ->where('created_at', '<=', $end_date)
            ->whereIn('status', ['completed', 'paid']);

        if ($transaction_type) {
            $query->where('transaction_type', $transaction_type);
        }

        $transactions = $query->orderBy('created_at', 'desc')->get();

        // Generate CSV filename
        $filename = 'sales_report_' . $start_date->format('Ymd') . '_' . $end_date->format('Ymd') . '.csv';

        // Create the CSV file
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function () use ($transactions) {
            $file = fopen('php://output', 'w');

            // Add CSV header
            fputcsv($file, [
                'ID', 'Transaction Code', 'Date', 'Customer', 'Table', 'Room',
                'Start Time', 'End Time', 'Duration', 'Type', 'Payment Method',
                'Status', 'Total Price'
            ]);

            // Add data rows
            foreach ($transactions as $transaction) {
                $startTime = Carbon::parse($transaction->start_time);
                $endTime = Carbon::parse($transaction->end_time ?: $transaction->created_at);
                $duration = $startTime->diff($endTime)->format('%H:%I');

                fputcsv($file, [
                    $transaction->id,
                    $transaction->transaction_code,
                    Carbon::parse($transaction->created_at)->format('Y-m-d H:i'),
                    $transaction->customer->name,
                    $transaction->table->table_number,
                    $transaction->table->room->name,
                    $startTime->format('Y-m-d H:i'),
                    $endTime->format('Y-m-d H:i'),
                    $duration,
                    ucfirst($transaction->transaction_type),
                    ucfirst($transaction->payment_method),
                    ucfirst($transaction->status),
                    number_format($transaction->total_price, 0, '.', ',')
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}