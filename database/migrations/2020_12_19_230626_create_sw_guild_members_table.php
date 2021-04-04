<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateSwGuildMembersTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('sw_guild_members', function(Blueprint $table)
		{
			$table->bigIncrements('id');
			$table->string('allyCode')->unique('sw_guild_members_allycode_unique');
			$table->string('active');
			$table->timestamps();
			$table->string('username')->default('null');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('sw_guild_members');
	}

}
