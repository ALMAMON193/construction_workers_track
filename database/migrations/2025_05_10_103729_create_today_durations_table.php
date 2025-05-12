<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('today_durations', function (Blueprint $table) {
             $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->dateTime('total_duration');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('today_durations');
    }
};
