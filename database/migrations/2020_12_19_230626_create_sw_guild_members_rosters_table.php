<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateSwGuildMembersRostersTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('sw_guild_members_rosters', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->string('sw_guild_member_id');
			$table->string('defId');
			$table->integer('relic');
			$table->integer('level');
			$table->integer('stars');
			$table->integer('tier');
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
		Schema::drop('sw_guild_members_rosters');
	}

}
