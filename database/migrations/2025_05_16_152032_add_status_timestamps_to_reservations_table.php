<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->foreignId('approved_by')->nullable()->after('status')->constrained('users')->onDelete('set null');
            $table->dateTime('status_approved_at')->nullable()->after('updated_at');
            $table->dateTime('status_paid_at')->nullable()->after('status_approved_at');
            $table->dateTime('status_completed_at')->nullable()->after('status_paid_at');
            $table->dateTime('status_cancelled_at')->nullable()->after('status_completed_at');
            $table->dateTime('status_rejected_at')->nullable()->after('status_cancelled_at');
            $table->dateTime('status_expired_at')->nullable()->after('status_rejected_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropForeign(['approved_by']);
            $table->dropColumn([
                'approved_by',
                'status_approved_at',
                'status_paid_at',
                'status_completed_at',
                'status_cancelled_at',
                'status_rejected_at',
                'status_expired_at',
            ]);
        });
    }
};
