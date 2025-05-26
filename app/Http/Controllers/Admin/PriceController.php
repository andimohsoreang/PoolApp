<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Price;
use App\Models\Table;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class PriceController extends Controller
{
    /**
     * Display a listing of the prices.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = Price::with('table', 'table.room');

        // Apply filters if they exist
        if ($request->filled('table_id')) {
            $query->where('table_id', $request->table_id);
        }

        if ($request->filled('day_type')) {
            $query->where('day_type', $request->day_type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status == '1' ? true : false);
        }

        if ($request->filled('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }

        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        $prices = $query->latest()->get();
        $tables = Table::whereHas('room', function ($q) {
            $q->where('status', true);
        })->where('status', 'normal')->get();

        return view('admin.prices.index', compact('prices', 'tables'));
    }

    /**
     * Show the form for creating a new price.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $tables = Table::whereHas('room', function ($q) {
            $q->where('status', true);
        })->where('status', 'normal')->get();

        return view('admin.prices.create', compact('tables'));
    }

    /**
     * Store a newly created price in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'table_id' => 'required|exists:tables,id',
            'start_time' => 'required',
            'end_time' => 'required',
            'price' => 'required|numeric|min:0',
            'day_type' => 'required|in:weekday,weekend',
            'valid_from' => 'nullable|date',
            'valid_until' => 'nullable|date|after_or_equal:valid_from',
            'status' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        Price::create([
            'table_id' => $request->table_id,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'price' => $request->price,
            'day_type' => $request->day_type,
            'valid_from' => $request->valid_from,
            'valid_until' => $request->valid_until,
            'status' => $request->has('status') ? true : false,
        ]);

        // Redirect based on request source
        if ($request->has('redirect_to_table')) {
            return redirect()->route('admin.billiard-tables.show', $request->table_id)
                ->with('success', 'Harga berhasil ditambahkan.');
        }

        return redirect()->route('admin.prices.index')
            ->with('success', 'Harga berhasil ditambahkan.');
    }

    /**
     * Display the specified price.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $price = Price::with('table', 'table.room')->findOrFail($id);
        return view('admin.prices.show', compact('price'));
    }

    /**
     * Show the form for editing the specified price.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        try {
            $price = Price::findOrFail($id);
            $tables = Table::whereHas('room', function ($q) {
                $q->where('status', true);
            })->where('status', 'normal')->get();

            // Return JSON for AJAX requests
            if (request()->ajax()) {
                return response()->json([
                    'status' => 'success',
                    'price' => $price
                ]);
            }

            return view('admin.prices.edit', compact('price', 'tables'));
        } catch (\Exception $e) {
            if (request()->ajax()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
                    'error_code' => $e->getCode()
                ], 500);
            }

            return redirect()->route('admin.prices.index')
                ->with('error', 'Terjadi kesalahan saat mengambil data harga: ' . $e->getMessage());
        }
    }

    /**
     * Update the specified price in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'table_id' => 'required|exists:tables,id',
            'start_time' => 'required',
            'end_time' => 'required',
            'price' => 'required|numeric|min:0',
            'day_type' => 'required|in:weekday,weekend',
            'valid_from' => 'nullable|date',
            'valid_until' => 'nullable|date|after_or_equal:valid_from',
            'status' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $price = Price::findOrFail($id);
        $tableId = $price->table_id;

        $price->update([
            'table_id' => $request->table_id,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'price' => $request->price,
            'day_type' => $request->day_type,
            'valid_from' => $request->valid_from,
            'valid_until' => $request->valid_until,
            'status' => $request->has('status') ? true : false,
        ]);

        // Redirect based on request source
        if ($request->has('redirect_to_table')) {
            return redirect()->route('admin.billiard-tables.show', $tableId)
                ->with('success', 'Harga berhasil diperbarui.');
        }

        return redirect()->route('admin.prices.index')
            ->with('success', 'Harga berhasil diperbarui.');
    }

    /**
     * Remove the specified price from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        try {
            // Log the deletion attempt
            Log::info('Attempting to delete price', [
                'price_id' => $id,
                'request_data' => $request->all(),
                'user_id' => auth()->id() ?? 'not authenticated'
            ]);

            $price = Price::findOrFail($id);
            Log::info('Price found', [
                'price' => $price->toArray()
            ]);

            $tableId = $price->table_id;
            $deleted = $price->delete();

            Log::info('Price deletion result', [
                'success' => $deleted,
                'price_id' => $id
            ]);

            if (!$deleted) {
                throw new \Exception('Failed to delete price record');
            }

            // Redirect based on referrer
            if ($request->has('redirect_to_table')) {
                return redirect()->route('admin.billiard-tables.show', $tableId)
                    ->with('success', 'Harga berhasil dihapus.');
            }

            return redirect()->route('admin.prices.index')
                ->with('success', 'Harga berhasil dihapus.');
        } catch (\Exception $e) {
            Log::error('Error deleting price', [
                'price_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->route('admin.prices.index')
                ->with('error', 'Gagal menghapus harga: ' . $e->getMessage());
        }
    }

    /**
     * Test price calculation based on time, date and table.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function testPrice(Request $request)
    {
        $tables = Table::whereHas('room', function ($q) {
            $q->where('status', true);
        })->where('status', 'normal')->get();

        $result = null;

        if ($request->filled(['table_id', 'selected_date', 'selected_time'])) {
            $tableId = $request->table_id;
            $selectedDate = Carbon::parse($request->selected_date);
            $selectedTime = $request->selected_time;
            $duration = $request->filled('duration') ? intval($request->duration) : 2; // Default 2 hours

            // Determine if it's a weekday or weekend
            $dayType = $selectedDate->isWeekend() ? 'weekend' : 'weekday';

            // Find the applicable price
            $price = Price::where('table_id', $tableId)
                ->where('status', true)
                ->where('day_type', $dayType)
                ->where(function ($query) use ($selectedDate) {
                    $query->whereNull('valid_from')
                        ->orWhere('valid_from', '<=', $selectedDate->format('Y-m-d'));
                })
                ->where(function ($query) use ($selectedDate) {
                    $query->whereNull('valid_until')
                        ->orWhere('valid_until', '>=', $selectedDate->format('Y-m-d'));
                })
                ->where(function ($query) use ($selectedTime) {
                    // Handle time ranges that cross midnight
                    $query->where(function ($q) use ($selectedTime) {
                        // Case 1: Normal time range (start_time < end_time), e.g., 08:00-16:00
                        $q->whereRaw("TIME(start_time) <= TIME(end_time)")
                          ->whereRaw("TIME(?) BETWEEN TIME(start_time) AND TIME(end_time)", [$selectedTime]);
                    })
                    ->orWhere(function ($q) use ($selectedTime) {
                        // Case 2: Overnight time range (start_time > end_time), e.g., 18:00-06:00
                        $q->whereRaw("TIME(start_time) > TIME(end_time)")
                          ->where(function ($sq) use ($selectedTime) {
                              // Either time is after start_time or before end_time
                              $sq->whereRaw("TIME(?) >= TIME(start_time)", [$selectedTime])
                                 ->orWhereRaw("TIME(?) <= TIME(end_time)", [$selectedTime]);
                          });
                    });
                })
                ->first();

            $result = [
                'table_id' => $tableId,
                'selected_date' => $selectedDate->format('Y-m-d'),
                'selected_time' => $selectedTime,
                'duration' => $duration,
                'day_type' => $dayType,
                'price' => $price,
            ];
        }

        return view('admin.prices.test', compact('tables', 'result'));
    }

    /**
     * Get current server date and time.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCurrentTime()
    {
        $now = Carbon::now();

        return response()->json([
            'current_date' => $now->format('Y-m-d'),
            'current_time' => $now->format('H:i'),
            'timestamp' => $now->timestamp,
            'formatted' => $now->format('d F Y H:i:s')
        ]);
    }
}
