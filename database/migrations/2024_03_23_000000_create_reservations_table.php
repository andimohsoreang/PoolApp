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
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->onDelete('cascade');
            $table->foreignId('table_id')->constrained()->onDelete('cascade');
            $table->dateTime('start_time');
            $table->dateTime('end_time');
            $table->enum('status', ['pending', 'approved', 'paid', 'completed', 'cancelled', 'rejected', 'expired'])->default('pending');
            $table->boolean('notified')->default(false);
            $table->decimal('total_price', 10, 2)->default(0); // harga final
            $table->integer('duration_hours')->default(0);
            $table->decimal('price_per_hour', 10, 2)->default(0);
            $table->string('payment_token')->nullable();
            $table->dateTime('payment_expired_at')->nullable();
            $table->text('reason')->nullable(); // alasan pembatalan/penolakan
            $table->string('payment_order_id')->nullable(); // order_id Midtrans
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};