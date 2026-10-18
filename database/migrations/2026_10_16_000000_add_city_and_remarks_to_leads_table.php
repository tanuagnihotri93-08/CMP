<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leads', function (Blueprint $table): void {
            if (! Schema::hasColumn('leads', 'city')) {
                $table->string('city', 100)->nullable();
            }

            if (! Schema::hasColumn('leads', 'remarks')) {
                $table->text('remarks')->nullable();
            }
        });
    }

    public function down(): void
    {
        $columns = array_values(array_filter(
            ['city', 'remarks'],
            fn (string $column): bool => Schema::hasColumn('leads', $column)
        ));

        if ($columns !== []) {
            Schema::table('leads', function (Blueprint $table) use ($columns): void {
                $table->dropColumn($columns);
            });
        }
    }
};
