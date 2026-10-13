<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();

            $table
                ->foreignId('project_id')
                ->constrained()
                ->cascadeOnDelete();

            $table
                ->foreignId('assigned_to')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('title');

            $table->text('description')->nullable();

            $table->enum('priority', [
                'Low',
                'Medium',
                'High',
            ])->default('Medium');

            $table->enum('status', [
                'Pending',
                'In Progress',
                'Completed',
            ])->default('Pending');

            $table->date('due_date')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
