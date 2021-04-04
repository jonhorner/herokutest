<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateSwUnitDataTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('sw_unit_data', function(Blueprint $table)
		{
			$table->bigIncrements('id');
			$table->text('thumbnailName')->nullable();
			$table->text('nameKey')->nullable();
			$table->text('baseId')->nullable();
			$table->integer('combatType')->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('sw_unit_data');
	}

}
