<?php

// use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Staudenmeir\LaravelMigrationViews\Facades\Schema;

class CreateViewGuildSquadsView extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        $query = '
            SELECT gs.id,
                gs.name,
                gs.p1 as icon1,
                gs.p2 as icon2,
                gs.p3 as icon3,
                gs.p4 as icon4,
                gs.p5 as icon5,
                ud.nameKey as p1name,
                ud2.nameKey as p2name,
                ud3.nameKey as p3name,
                ud4.nameKey as p4name,
                ud5.nameKey as p5name,
                gs.priority,
                gs.ordering
                FROM `sw_guild_squads` as gs
                LEFT JOIN
                  `sw_unit_data` as ud ON ud.baseId = gs.p1
                LEFT JOIN
                  `sw_unit_data` as ud2 ON ud2.baseId = gs.p2
                LEFT JOIN
                  `sw_unit_data` as ud3 ON ud3.baseId = gs.p3
                LEFT JOIN
                  `sw_unit_data` as ud4 ON ud4.baseId = gs.p4
                LEFT JOIN
                  `sw_unit_data` as ud5 ON ud5.baseId = gs.p5
          ';

        Schema::createView('view_guild_squads', $query);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropView('view_guild_squads');
    }
}
