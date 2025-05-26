<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FinancialController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:owner');
    }
    /**
     * Display the financial reports page.
     *
     * @return \Illuminate\View\View
     */
    public function reports(Request $request)
    {
        // Default to current month if not specified
        $dateRange = $request->input('date_range', 'this_month');

        // Calculate date ranges based on selection
        $startDate = null;
        $endDate = null;

        switch ($dateRange) {
            case 'today':
                $startDate = Carbon::today();
                $endDate = Carbon::today()->endOfDay();
                break;
            case 'yesterday':
                $startDate = Carbon::yesterday();
                $endDate = Carbon::yesterday()->endOfDay();
                break;
            case 'this_week':
                $startDate = Carbon::now()->startOfWeek();
                $endDate = Carbon::now()->endOfWeek();
                break;
            case 'last_week':
                $startDate = Carbon::now()->subWeek()->startOfWeek();
                $endDate = Carbon::now()->subWeek()->endOfWeek();
                break;
            case 'last_month':
                $startDate = Carbon::now()->subMonth()->startOfMonth();
                $endDate = Carbon::now()->subMonth()->endOfMonth();
                break;
            case 'this_year':
                $startDate = Carbon::now()->startOfYear();
                $endDate = Carbon::now()->endOfYear();
                break;
            case 'custom':
                $startDate = $request->filled('start_date') ? Carbon::parse($request->input('start_date')) : Carbon::now()->startOfMonth();
                $endDate = $request->filled('end_date') ? Carbon::parse($request->input('end_date'))->endOfDay() : Carbon::now()->endOfMonth()->endOfDay();
                break;
            case 'this_month':
            default:
                $startDate = Carbon::now()->startOfMonth();
                $endDate = Carbon::now()->endOfMonth();
                break;
        }

        // Get financial summary data
        $totalRevenue = Transaction::where('status', 'paid')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->sum('total_price');

        // Get count of tables
        $tableCount = DB::table('billiard_tables')->count();

        // Get average revenue per table
        $avgRevenuePerTable = $tableCount > 0 ? $totalRevenue / $tableCount : 0;

        // Get total hours rented
        $totalHoursRented = Transaction::where('status', 'paid')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->sum(DB::raw('TIMESTAMPDIFF(HOUR, start_time, end_time)'));

        // Get monthly revenue data for chart
        $monthlyRevenue = Transaction::where('status', 'paid')
            ->whereYear('created_at', Carbon::now()->year)
            ->selectRaw('MONTH(created_at) as month, SUM(total_price) as total')
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->keyBy('month')
            ->map(function ($item) {
                return $item->total;
            });

        // Fill in missing months with zero
        $chartData = [];
        $chartLabels = [];

        for ($i = 1; $i <= 12; $i++) {
            $chartData[] = $monthlyRevenue[$i] ?? 0;
            $chartLabels[] = Carbon::create(null, $i, 1)->format('M');
        }

        // Revenue distribution by service type
        $revenueByType = Transaction::where('status', 'paid')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('transaction_type, SUM(total_price) as total')
            ->groupBy('transaction_type')
            ->get();

        // Format distribution data for chart
        $distributionData = [];
        $distributionLabels = [];

        foreach ($revenueByType as $revenue) {
            $distributionData[] = $revenue->total;
            $distributionLabels[] = ucfirst(str_replace('_', ' ', $revenue->transaction_type));
        }

        // Get recent transactions
        $recentTransactions = Transaction::with(['customer', 'table'])
            ->where('status', 'paid')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('admin.financial.reports', compact(
            'dateRange',
            'startDate',
            'endDate',
            'totalRevenue',
            'avgRevenuePerTable',
            'totalHoursRented',
            'chartData',
            'chartLabels',
            'distributionData',
            'distributionLabels',
            'recentTransactions'
        ));
    }
}
