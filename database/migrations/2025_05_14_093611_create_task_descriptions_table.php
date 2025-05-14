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
        Schema::create('task_descriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('daily_task_id')->constrained('daily_tasks')->onDelete('cascade');
            $table->string('task_name');
            $table->longText('description');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('task_descriptions');
    }
};
