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
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->string('type'); // notification type: reservation, payment, system, etc.
            $table->text('message'); // notification message
            $table->unsignedBigInteger('user_id')->nullable(); // recipient user ID
            $table->unsignedBigInteger('reservation_id')->nullable(); // related reservation ID
            $table->unsignedBigInteger('transaction_id')->nullable(); // related transaction ID
            $table->string('status')->default('unread'); // read/unread status
            $table->boolean('is_manual')->default(false); // flag for manual notifications
            $table->timestamp('read_at')->nullable(); // when the notification was read
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('reservation_id')->references('id')->on('reservations')->onDelete('cascade');
            $table->foreign('transaction_id')->references('id')->on('transactions')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
