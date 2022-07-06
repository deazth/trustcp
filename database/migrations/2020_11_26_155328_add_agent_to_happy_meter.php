<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAgentToHappyMeter extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */






    public function up()
    {


        Schema::table('happy_meters', function (Blueprint $table) {
             $table->string('agent')->after('sourcefromtrust')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('happy_meters', function (Blueprint $table) {
            $table->dropColumn('agent');
        });
    }
}
