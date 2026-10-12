<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_work_updates', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('task_id')->constrained('tasks')->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained('users')->restrictOnDelete();
            $table->date('work_date');
            $table->text('completed_work');
            $table->text('todays_progress');
            $table->text('pending_work')->nullable();
            $table->text('blockers')->nullable();
            // Optional 0-100 value; range is enforced by validation in the application.
            $table->unsignedTinyInteger('progress_percentage')->nullable();
            // created_at doubles as the submission timestamp.
            $table->timestamps();

            // One update per employee, task and day.
            $table->unique(['task_id', 'employee_id', 'work_date'], 'daily_updates_unique_per_day');
            $table->index(['employee_id', 'work_date']);
            $table->index('work_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_work_updates');
    }
};
