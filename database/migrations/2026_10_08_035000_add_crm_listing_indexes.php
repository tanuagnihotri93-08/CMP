<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leads', function (Blueprint $table): void {
            $table->index(
                ['assigned_to_user_id', 'status', 'created_at'],
                'leads_assignee_status_created_idx'
            );
        });

        Schema::table('customers', function (Blueprint $table): void {
            $table->index(
                ['assigned_to_user_id', 'status', 'created_at'],
                'customers_assignee_status_created_idx'
            );
        });

        Schema::table('follow_ups', function (Blueprint $table): void {
            $table->index(
                ['assigned_to_user_id', 'status', 'date'],
                'followups_assignee_status_date_idx'
            );
        });

        Schema::table('activities', function (Blueprint $table): void {
            $table->index(
                ['assigned_to_user_id', 'status', 'activity_date'],
                'activities_assignee_status_date_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::table('activities', function (Blueprint $table): void {
            $table->dropIndex('activities_assignee_status_date_idx');
        });

        Schema::table('follow_ups', function (Blueprint $table): void {
            $table->dropIndex('followups_assignee_status_date_idx');
        });

        Schema::table('customers', function (Blueprint $table): void {
            $table->dropIndex('customers_assignee_status_created_idx');
        });

        Schema::table('leads', function (Blueprint $table): void {
            $table->dropIndex('leads_assignee_status_created_idx');
        });
    }
};
