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
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('role');
            $table->string('current_location')->nullable();
            $table->float('lat')->nullable();
            $table->float('long')->nullable();
            $table->enum('status', ['check_in', 'check_out']);
            $table->date('date');
            $table->string('check_in')->nullable();
            $table->string('check_out')->nullable();
            $table->string('total_hours')->nullable();
            $table->string('type')->default('chacking_history');
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
