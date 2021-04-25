<?php


namespace App\Constants;


class Constants
{
    public const GOOGLE_SHEET_CRON_PERIOD_MINS = 120;

    /**
     * Squad Types
     */
    public const ID_META_SQUAD = 1;
    public const ID_CRANCOR_SQUAD = 2;

    /**
     * Meta Report
     */
    public const GALACTIC_LEGENDS = ['GLREY','SUPREMELEADERKYLOREN','GRANDMASTERLUKE','SITHPALPATINE'];
    public const GALACTIC_LEGENDS_KEY = ['GLREY'=>'REY',
        'SUPREMELEADERKYLOREN'=>'SLKR',
        'GRANDMASTERLUKE' => 'GML',
        'SITHPALPATINE'=>'SEE'
    ];
    public const GAS = 'GENERALSKYWALKER';
    public const JKL = 'JEDIKNIGHTLUKE';
}
