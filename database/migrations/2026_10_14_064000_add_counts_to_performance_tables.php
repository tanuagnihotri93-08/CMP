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
    foreach (['performances', 'performance_histories'] as $tableName) {
        Schema::table($tableName, function (Blueprint $table) {
            $table->unsignedInteger('leads_count')->default(0);
            $table->unsignedInteger('customers_count')->default(0);
            $table->unsignedInteger('activities_count')->default(0);
            $table->unsignedInteger('pending_followups_count')->default(0);
        });
    }
}

public function down(): void
{
    foreach (['performances', 'performance_histories'] as $tableName) {
        Schema::table($tableName, function (Blueprint $table) {
            $table->dropColumn([
                'leads_count',
                'customers_count',
                'activities_count',
                'pending_followups_count',
            ]);
        });
    }
}
  
};
