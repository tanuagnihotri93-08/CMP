<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('performance_feedbacks', function (Blueprint $table): void {
            $table->integer('rating')->nullable()->after('message');
        });
    }

    public function down(): void
    {
        Schema::table('performance_feedbacks', function (Blueprint $table): void {
            $table->dropColumn('rating');
        });
    }
};
