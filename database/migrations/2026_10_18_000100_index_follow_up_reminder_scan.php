<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('follow_ups', function (Blueprint $table): void {
            $table->index(
                ['status', 'reminder_sent_at', 'date'],
                'followups_reminder_scan_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::table('follow_ups', function (Blueprint $table): void {
            $table->dropIndex('followups_reminder_scan_idx');
        });
    }
};
