<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSwGuildTokens extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sw_guild_tokens', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('guild_id')->nullable();
            $table->string('allycode')->nullable();
            $table->integer('total_tickets')->nullable();
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
        Schema::dropIfExists('sw_guild_tokens');
    }
}
