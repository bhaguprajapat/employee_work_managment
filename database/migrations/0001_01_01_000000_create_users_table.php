<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 120);
            $table->string('email', 190)->unique();
            $table->string('password');
            $table->enum('role', ['admin', 'manager', 'employee'])->default('employee');
            // Reporting line: an employee belongs to one manager. Removing a manager
            // releases their team instead of deleting it.
            $table->foreignId('manager_id')->nullable()
                ->constrained('users')->nullOnDelete();
            $table->string('job_title', 120)->nullable();
            $table->boolean('is_active')->default(true);
            $table->rememberToken();
            $table->timestamps();

            $table->index(['role', 'is_active']);
        });

        Schema::create('password_reset_tokens', function (Blueprint $table): void {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
    }
};
