<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::table('leads', function (Blueprint $table) {


            if (!Schema::hasColumn('leads', 'company_name')) {

                $table->string('company_name')->nullable();

            }


            if (!Schema::hasColumn('leads', 'email')) {

                $table->string('email')->nullable();

            }


            if (!Schema::hasColumn('leads', 'phone')) {

                $table->string('phone')->nullable();

            }


            if (!Schema::hasColumn('leads', 'source')) {

                $table->string('source')->nullable();

            }


            if (!Schema::hasColumn('leads', 'status')) {

                $table->string('status')->default('new');

            }


            if (!Schema::hasColumn('leads', 'notes')) {

                $table->text('notes')->nullable();

            }


        });
    }



    public function down(): void
    {

        Schema::table('leads', function (Blueprint $table) {


            $columns = [
                'company_name',
                'email',
                'phone',
                'source',
                'status',
                'notes'
            ];


            foreach($columns as $column){

                if(Schema::hasColumn('leads',$column)){

                    $table->dropColumn($column);

                }

            }


        });

    }

};
