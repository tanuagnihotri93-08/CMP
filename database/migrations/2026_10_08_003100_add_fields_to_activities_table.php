<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('activities', function (Blueprint $table) {
            $table->foreignId('lead_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type')->default('Note');
            $table->string('subject')->default('');
            $table->text('description')->nullable();
            $table->dateTime('activity_date')->nullable();
            $table->string('status')->default('pending');
        });
    }

    public function down(): void
    {
        Schema::table('activities', function (Blueprint $table) {
            $table->dropForeign(['lead_id']);
            $table->dropForeign(['customer_id']);
            $table->dropColumn([
                'lead_id',
                'customer_id',
                'type',
                'subject',
                'description',
                'activity_date',
                'status',
            ]);
        });
    }
};
