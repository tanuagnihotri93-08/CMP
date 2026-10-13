<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['leads', 'customers', 'follow_ups', 'activities'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table): void {
                $table->foreignId('assigned_to_user_id')
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete();
                $table->index('assigned_to_user_id');
            });
        }
    }

    public function down(): void
    {
        foreach (['leads', 'customers', 'follow_ups', 'activities'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table): void {
                $table->dropConstrainedForeignId('assigned_to_user_id');
            });
        }
    }
};
