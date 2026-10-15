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
    Schema::create('performance_feedbacks', function (Blueprint $table) {
        $table->id();

        $table->foreignId('sender_id')
            ->constrained('users')
            ->cascadeOnDelete();

        $table->foreignId('receiver_id')
            ->constrained('users')
            ->cascadeOnDelete();

        $table->foreignId('performance_history_id')
            ->nullable()
            ->constrained('performance_histories')
            ->nullOnDelete();

        $table->text('message');

        $table->enum('status', [
            'Open',
            'Reviewed'
        ])->default('Open');

        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
   public function down(): void
{
    Schema::dropIfExists('performance_feedbacks');
}
};
