<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBuildingsTable extends Migration
{

    
    /**
     * Run the migrations.
     *
     * @return void
     */
    
    public function up()
    {

        if(Schema::hasTable('buildings'))
        {
            Schema::rename('buildings', 'bak_buildings');
        }
        ;


        Schema::create('buildings', function (Blueprint $table) {
          $table->increments('id');
          $table->string('building_name');
          $table->decimal('a_latitude', 10,7)->nullable();
          $table->decimal('a_longitude', 10,7)->nullable();
          $table->decimal('b_latitude', 10,7)->nullable();
          $table->decimal('b_longitude', 10,7)->nullable();
          $table->integer('created_by')->nullable();
          $table->integer('updated_by')->nullable();
          $table->timestamps();
      });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('buildings');
    }
}
