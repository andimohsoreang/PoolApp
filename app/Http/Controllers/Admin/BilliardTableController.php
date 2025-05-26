<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Room;
use App\Models\Table;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BilliardTableController extends Controller
{
    /**
     * Display a listing of the tables.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = Table::with('room');

        // Apply filters if they exist
        if ($request->filled('table_number')) {
            $query->where('table_number', 'like', '%' . $request->table_number . '%');
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('room_id')) {
            $query->where('room_id', $request->room_id);
        }

        $tables = $query->latest()->get();
        $rooms = Room::where('status', true)->get();

        return view('admin.tables.index', compact('tables', 'rooms'));
    }

    /**
     * Show the form for creating a new table.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $rooms = Room::where('status', true)->get();
        return view('admin.tables.create', compact('rooms'));
    }

    /**
     * Store a newly created table in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'table_number' => 'required|string|max:50',
            'room_id' => 'required|exists:rooms,id',
            'status' => 'required|in:normal,rusak,maintenance',
            'capacity' => 'required|integer|min:1',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        Table::create([
            'table_number' => $request->table_number,
            'room_id' => $request->room_id,
            'status' => $request->status,
            'capacity' => $request->capacity,
            'description' => $request->description,
        ]);

        return redirect()->route('admin.billiard-tables.index')
            ->with('success', 'Meja biliar berhasil ditambahkan.');
    }

    /**
     * Display the specified table.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $table = Table::with('room', 'prices')->findOrFail($id);
        return view('admin.tables.show', compact('table'));
    }

    /**
     * Show the form for editing the specified table.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $table = Table::findOrFail($id);
        $rooms = Room::where('status', true)->get();
        return view('admin.tables.edit', compact('table', 'rooms'));
    }

    /**
     * Update the specified table in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'table_number' => 'required|string|max:50',
            'room_id' => 'required|exists:rooms,id',
            'status' => 'required|in:normal,rusak,maintenance',
            'capacity' => 'required|integer|min:1',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $table = Table::findOrFail($id);
        $table->update([
            'table_number' => $request->table_number,
            'room_id' => $request->room_id,
            'status' => $request->status,
            'capacity' => $request->capacity,
            'description' => $request->description,
        ]);

        return redirect()->route('admin.billiard-tables.index')
            ->with('success', 'Meja biliar berhasil diperbarui.');
    }

    /**
     * Remove the specified table from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $table = Table::findOrFail($id);
        $table->delete();

        return redirect()->route('admin.billiard-tables.index')
            ->with('success', 'Meja biliar berhasil dihapus.');
    }

    /**
     * Get table price for a specific date and time.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPrice(Request $request, $id)
    {
        try {
            $table = Table::findOrFail($id);

            // Get datetime from request
            $datetime = $request->input('datetime');
            if (!$datetime) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datetime parameter is required'
                ]);
            }

            $dateObj = new \DateTime($datetime);
            $dayOfWeek = strtolower($dateObj->format('l')); // day name (monday, tuesday, etc.)
            $timeOfDay = $dateObj->format('H:i:s');

            // Find appropriate price for the date and time
            $price = \App\Models\Price::where('table_id', $table->id)
                ->where(function($q) use ($dayOfWeek) {
                    $q->where('day_type', $dayOfWeek)
                      ->orWhere('day_type', 'all');
                })
                ->where('start_time', '<=', $timeOfDay)
                ->where('end_time', '>=', $timeOfDay)
                ->where('status', true)
                ->first();

            // If no specific price is found, look for a default price
            if (!$price) {
                $price = \App\Models\Price::where('table_id', $table->id)
                    ->where('day_type', 'all')
                    ->where('status', true)
                    ->first();
            }

            if (!$price) {
                return response()->json([
                    'success' => false,
                    'message' => 'No price configuration found for this table at the specified time'
                ]);
            }

            return response()->json([
                'success' => true,
                'price' => [
                    'id' => $price->id,
                    'price_per_hour' => $price->price,
                    'day_type' => $price->day_type,
                    'start_time' => $price->start_time,
                    'end_time' => $price->end_time
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }
}
