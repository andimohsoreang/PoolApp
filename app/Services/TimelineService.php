<?php

namespace App\Services;

use App\Models\Table;
use App\Models\Transaction;
use App\Models\Reservation;
use Carbon\Carbon;

class TimelineService
{
    public static function getTimelineData($tableId, $date)
    {
        $table = Table::with('room')->findOrFail($tableId);

        // Ambil dari transactions (walk-in/admin)
        $transactions = Transaction::with('customer')
            ->where('table_id', $table->id)
            ->where('status', '!=', 'cancelled')
            ->whereDate('start_time', $date)
            ->orderBy('start_time')
            ->get()
            ->map(function($trx) {
                return [
                    'customer_name' => $trx->customer ? $trx->customer->name : '-',
                    'start_time' => $trx->start_time->format('H:i'),
                    'end_time' => $trx->end_time->format('H:i'),
                    'status' => $trx->status,
                    'source' => 'walkin'
                ];
            });

        // Ambil dari reservations (customer)
        $reservations = Reservation::with('customer')
            ->where('table_id', $table->id)
            ->whereIn('status', ['pending', 'approved', 'paid'])
            ->whereDate('start_time', $date)
            ->orderBy('start_time')
            ->get()
            ->map(function($res) {
                return [
                    'customer_name' => $res->customer ? $res->customer->name : '-',
                    'start_time' => $res->start_time->format('H:i'),
                    'end_time' => $res->end_time->format('H:i'),
                    'status' => $res->status,
                    'source' => 'reservation'
                ];
            });

        // Gabungkan dan urutkan
        $allBookings = $transactions->concat($reservations)
            // Remove duplicates where reservation and transaction overlap same time slot
            ->unique(function($booking) {
                return $booking['start_time'].'|'.$booking['end_time'];
            })
            ->sortBy('start_time')
            ->values();

        $totalHoursUsed = 0;
        $dateStr = is_string($date) ? $date : $date->format('Y-m-d');
        foreach ($allBookings as $booking) {
            $start = Carbon::parse($dateStr . ' ' . $booking['start_time']);
            $end = Carbon::parse($dateStr . ' ' . $booking['end_time']);
            $totalHoursUsed += $end->diffInMinutes($start) / 60;
        }

        return [
            'table' => $table,
            'transactions' => $allBookings,
            'totalHoursUsed' => $totalHoursUsed,
        ];
    }
}
