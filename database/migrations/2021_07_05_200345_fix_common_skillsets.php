<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class FixCommonSkillsets extends Migration
{
    /**
     * to fix legacy tables for HIJRAH
     * 
     * @return void
     */
    public function up()
    {
        try {
        Schema::table('common_skillsets', function (Blueprint $table) {
            $table->renameColumn('added_by', 'updated_by');

            $table->dropColumn('skillgroup'); //empty column
            $table->dropColumn('skilltype'); //empty column
            $table->integer('created_by')->nullable();
        });
    } catch (Exception $e) {
        report($e);

    
    }

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

    }
}
