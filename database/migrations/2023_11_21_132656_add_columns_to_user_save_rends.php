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
        Schema::table('user_save_rends', function (Blueprint $table) {
            $table->foreignId('order_day_id')->nullable()->constrained('order_days','id')->cascadeOnUpdate()->cascadeOnUpdate();
            $table->foreignId('order_hour_id')->nullable()->constrained('order_hours','id')->cascadeOnUpdate()->cascadeOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_save_rends', function (Blueprint $table) {
                $table->foreignId('order_day_id');
                $table->foreignId('order_hour_id');
        });
    }
};
