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
        Schema::create('earnings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->unsignedBigInteger('employee_checking_id')->nullable();

            $table->date('earning_date');
            $table->string('role');
            $table->decimal('salary', 8, 2);
            $table->string('working_hours');
            $table->decimal('vat', 5, 2)->default(0);
            $table->decimal('total_salary', 8, 2)->default(0);
            $table->timestamps();

            $table->foreign('employee_checking_id')->references('id')->on('employee_checkings')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('earnings');
    }
};
