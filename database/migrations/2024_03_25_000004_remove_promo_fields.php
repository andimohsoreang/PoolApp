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
        // Remove promo fields from transactions table
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropForeign(['promo_id']);
            $table->dropColumn(['promo_id', 'discount']);
        });

        // Remove promo fields from reservations table
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropForeign(['promo_id']);
            $table->dropColumn(['promo_id', 'promo_code', 'discount']);
        });

        // Remove promo fields from transaction_details table
        Schema::table('transaction_details', function (Blueprint $table) {
            $table->dropForeign(['promo_id']);
            $table->dropColumn(['promo_id']);
        });

        // Drop promo_tables table
        Schema::dropIfExists('promo_tables');

        // Drop promos table
        Schema::dropIfExists('promos');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recreate promos table
        Schema::create('promos', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->text('description')->nullable();
            $table->enum('discount_type', ['percentage', 'fixed'])->default('percentage');
            $table->decimal('discount_value', 10, 2)->default(0);
            $table->decimal('minimum_price', 10, 2)->default(0);
            $table->decimal('maximum_discount', 10, 2)->nullable();
            $table->datetime('valid_from')->nullable();
            $table->datetime('valid_until')->nullable();
            $table->integer('usage_limit')->nullable();
            $table->integer('usage_count')->default(0);
            $table->enum('applies_to', ['all', 'table', 'room'])->default('all');
            $table->foreignId('table_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('room_id')->nullable()->constrained()->nullOnDelete();
            $table->string('day_restriction')->nullable()->comment('Comma-separated list of days (e.g., monday,tuesday)');
            $table->time('time_restriction_start')->nullable();
            $table->time('time_restriction_end')->nullable();
            $table->boolean('status')->default(true);
            $table->timestamps();
        });

        // Recreate promo_tables table
        Schema::create('promo_tables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('promo_id')->constrained()->onDelete('cascade');
            $table->foreignId('table_id')->constrained()->onDelete('cascade');
            $table->timestamps();
            $table->unique(['promo_id', 'table_id']);
        });

        // Add promo fields back to transactions table
        Schema::table('transactions', function (Blueprint $table) {
            $table->foreignId('promo_id')->nullable()->after('total_price')->constrained();
            $table->decimal('discount', 10, 2)->default(0)->after('promo_id');
        });

        // Add promo fields back to reservations table
        Schema::table('reservations', function (Blueprint $table) {
            $table->string('promo_code')->nullable()->after('price_per_hour');
            $table->foreignId('promo_id')->nullable()->after('promo_code')->constrained();
            $table->decimal('discount', 10, 2)->default(0)->after('promo_id');
        });

        // Add promo fields back to transaction_details table
        Schema::table('transaction_details', function (Blueprint $table) {
            $table->foreignId('promo_id')->nullable()->after('subtotal')->constrained();
        });
    }
};