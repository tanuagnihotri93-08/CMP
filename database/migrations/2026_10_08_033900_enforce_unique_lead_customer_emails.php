<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tables = ['leads', 'customers'];

        foreach ($tables as $tableName) {
            $hasDuplicates = DB::table($tableName)
                ->selectRaw('LOWER(TRIM(email))')
                ->whereNotNull('email')
                ->whereRaw("TRIM(email) <> ''")
                ->groupByRaw('LOWER(TRIM(email))')
                ->havingRaw('COUNT(*) > 1')
                ->exists();

            if ($hasDuplicates) {
                throw new RuntimeException(
                    "Cannot add the unique {$tableName}.email constraint until duplicate emails are resolved."
                );
            }
        }

        foreach ($tables as $tableName) {
            DB::table($tableName)
                ->whereNotNull('email')
                ->update(['email' => DB::raw("NULLIF(LOWER(TRIM(email)), '')")]);
        }

        foreach ($tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName): void {
                $table->unique('email', "{$tableName}_email_unique");
            });
        }
    }

    public function down(): void
    {
        foreach (['leads', 'customers'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName): void {
                $table->dropUnique("{$tableName}_email_unique");
            });
        }
    }
};
