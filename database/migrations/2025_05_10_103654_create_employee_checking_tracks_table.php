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
        Schema::create('employee_checking_tracks', function (Blueprint $table) {
            $table->id();
            $table->dateTime('check_in')->nullable();
            $table->dateTime('checkout')->nullable();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->bigInteger('total_hours');
            $table->string('role');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_checking_tracks');
    }
};
