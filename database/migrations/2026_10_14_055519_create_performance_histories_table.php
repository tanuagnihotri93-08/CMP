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
    Schema::create('performance_histories', function (Blueprint $table) {

        $table->id();

        $table->foreignId('user_id')
            ->constrained()
            ->cascadeOnDelete();

        $table->date('snapshot_date');

        $table->unsignedSmallInteger('lead_score')
            ->default(0);

        $table->unsignedSmallInteger('followup_score')
            ->default(0);

        $table->unsignedSmallInteger('activity_score')
            ->default(0);

        $table->unsignedSmallInteger('conversion_score')
            ->default(0);

        $table->unsignedSmallInteger('total_score')
            ->default(0);

        $table->timestamps();

        $table->unique([
            'user_id',
            'snapshot_date'
        ]);

        $table->index('snapshot_date');

    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('performance_histories');
    }
};
