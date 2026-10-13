<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('follow_ups', function (Blueprint $table) {
            $table
                ->foreignId('lead_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->dateTime('date')->nullable();

            $table->string('type')->nullable();

            $table->text('remark')->nullable();

            $table
                ->string('status')
                ->default('scheduled');
        });
    }

    public function down(): void
    {
        Schema::table('follow_ups', function (Blueprint $table) {
            $table->dropForeign(['lead_id']);

            $table->dropColumn([
                'lead_id',
                'date',
                'type',
                'remark',
                'status',
            ]);
        });
    }
};
