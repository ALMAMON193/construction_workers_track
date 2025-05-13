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
        Schema::create('employee_checkings', function (Blueprint $table) {
           $table->id();
            $table->time('check_in')->nullable();
            $table->time('checkout')->nullable();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->dateTime('total_hours')->nullable();
            $table->date('date');
            $table->string('role');
            $table->enum('status', ['check_in', 'check_out']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_checkings');
    }
};
