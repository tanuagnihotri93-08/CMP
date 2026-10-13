<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private array $definitions = [

        'projects.view' => 'View permitted projects',
        'projects.create' => 'Create projects',
        'projects.update' => 'Update permitted projects',
        'projects.delete' => 'Delete permitted projects',

        'tasks.view' => 'View permitted tasks',
        'tasks.create' => 'Create tasks',
        'tasks.update' => 'Update permitted tasks',
        'tasks.delete' => 'Delete permitted tasks',

    ];

    public function up(): void
    {
        foreach ($this->definitions as $name => $description) {

            DB::table('permissions')->updateOrInsert(

                [
                    'name' => $name,
                ],

                [
                    'description' => $description,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]

            );
        }
    }

    public function down(): void
    {
        DB::table('permissions')
            ->whereIn(
                'name',
                array_keys($this->definitions)
            )
            ->delete();
    }
};
