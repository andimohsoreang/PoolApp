<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Transaction;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FixMissingPayments extends Command
{
    protected $signature = 'transactions:fix-missing-payments';
    protected $description = 'Fix transactions that are marked as paid but missing payment records';

    public function handle()
    {
        $transactions = Transaction::where('status', 'paid')
            ->whereDoesntHave('payment')
            ->get();

        $this->info("Found {$transactions->count()} transactions without payment records");

        foreach ($transactions as $transaction) {
            DB::beginTransaction();

            try {
                Payment::create([
                    'transaction_id' => $transaction->id,
                    'payment_method' => $transaction->payment_method ?? 'e_payment',
                    'total_amount' => $transaction->total_price,
                    'amount_paid' => $transaction->total_price,
                    'change_amount' => 0,
                    'status' => 'paid',
                    'payment_date' => $transaction->created_at,
                    'payment_details' => json_encode([
                        'transaction_id' => $transaction->id,
                        'payment_method' => $transaction->payment_method ?? 'e_payment',
                        'payment_date' => $transaction->created_at->format('Y-m-d H:i:s')
                    ]),
                ]);

                DB::commit();
                $this->info("Created payment record for transaction #{$transaction->id}");
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Error fixing missing payment', [
                    'transaction_id' => $transaction->id,
                    'error' => $e->getMessage()
                ]);
                $this->error("Failed to create payment record for transaction #{$transaction->id}: {$e->getMessage()}");
            }
        }

        $this->info('Done!');
    }
}
