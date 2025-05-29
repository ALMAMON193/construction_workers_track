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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('employee_id');
            $table->string('name')->nullable();
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');

            $table->string('otp')->nullable();
            $table->string('otp_created_at')->nullable();
            $table->boolean('is_otp_verified')->default(false);
            $table->timestamp('otp_expires_at')->nullable();
            $table->string('reset_password_token')->nullable();
            $table->timestamp('reset_password_token_expire_at')->nullable();
            $table->string('delete_token')->nullable();
            $table->timestamp('delete_token_expires_at')->nullable();
            $table->string('deleted_at')->nullable();
            $table->string('is_verified')->nullable();

            $table->enum('role', ['admin', 'employee'])->default('employee');
            $table->string('phone')->nullable();
            $table->string('country_code')->nullable();
            $table->string('address')->nullable();
            $table->string('working_days')->nullable();
            $table->bigInteger('total_use_storage')->default(0);
            $table->bigInteger('total_use_storage_limit')->default(0);
            $table->decimal('hourly_working_rate', 10, 2)->default(0);
            $table->decimal('hourly_working_rate_vat', 10, 2)->default(0);
            $table->string('avatar')->nullable();
            $table->string('dob')->nullable();
            $table->enum('gender', ['male', 'female'])->nullable();
            $table->decimal('total_sallary_amount', 10, 2)->default(0);

            $table->string('provider')->nullable();
            $table->string('provider_id')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });


        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
