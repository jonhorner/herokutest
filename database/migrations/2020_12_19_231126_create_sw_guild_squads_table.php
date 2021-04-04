<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateSwGuildSquadsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('sw_guild_squads', function(Blueprint $table)
		{
			$table->bigIncrements('id');
			$table->string('name');
			$table->string('p1');
			$table->string('p2');
			$table->string('p3');
			$table->string('p4');
			$table->string('p5');
			$table->integer('priority');
			$table->integer('ordering');
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
		Schema::drop('sw_guild_squads');
	}

}
