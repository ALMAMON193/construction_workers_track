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
        Schema::create('daily_task_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('daily_task_id')->constrained()->onDelete('cascade'); // Link to daily_tasks
           $table->string('task_number');
           $table->string('task_description');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_task_items');
    }
};
