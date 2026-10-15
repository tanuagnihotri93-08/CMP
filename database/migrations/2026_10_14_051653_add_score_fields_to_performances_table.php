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
    Schema::table('performances', function (Blueprint $table) {

        $table->foreignId('user_id')
            ->after('id')
            ->constrained()
            ->cascadeOnDelete();

        $table->integer('lead_score')
            ->default(0);

        $table->integer('followup_score')
            ->default(0);

        $table->integer('activity_score')
            ->default(0);

        $table->integer('conversion_score')
            ->default(0);

        $table->integer('total_score')
            ->default(0);

    });
}

    /**
     * Reverse the migrations.
     */
   public function down(): void
{
    Schema::table('performances', function (Blueprint $table) {

        $table->dropForeign(['user_id']);

        $table->dropColumn([
            'user_id',
            'lead_score',
            'followup_score',
            'activity_score',
            'conversion_score',
            'total_score',
        ]);

    });
}
};
