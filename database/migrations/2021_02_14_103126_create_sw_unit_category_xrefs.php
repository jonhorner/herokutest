<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSwUnitCategoryXrefs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sw_unit_category_xrefs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('sw_unit_categories_id');
            $table->integer('sw_unit_data_id');
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
        Schema::dropIfExists('sw_unit_category_xrefs');
    }
}
