<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table): void {
            $table->id();
            $table->string('title', 150);
            $table->text('description')->nullable();
            $table->date('start_date');
            $table->date('due_date');
            $table->enum('priority', ['low', 'medium', 'high'])->default('medium');
            $table->enum('status', ['todo', 'in_progress', 'completed'])->default('todo');
            // Tasks are owned by an assignee and record who allocated them. Users
            // referenced here cannot be hard-deleted while tasks still point to them.
            $table->foreignId('assigned_to')->constrained('users')->restrictOnDelete();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();

            $table->index('status');
            $table->index('due_date');
            $table->index(['assigned_to', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
