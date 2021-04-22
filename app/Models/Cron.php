<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @method static where(string $string, string $string1)
 * @method static find($command)
 * @method static updateOrCreate(array $array, array $array1)
 */
class Cron extends Model
{
    protected $primaryKey = 'command';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['command', 'next_run', 'last_run'];

    /**
     * @param $command
     * @param $minutes
     * @return bool
     */
    public static function shouldIRun($command, $minutes): bool
    {
        $cron = self::find($command);
        $now  = Carbon::now();
        if (
            $cron
            && $cron->next_run > $now->timestamp
        ) {
            return false;
        }
        self::updateOrCreate(
            [
                'command'  => $command
            ],
            [
                'next_run' => Carbon::now()->addMinutes($minutes)->timestamp,
                'last_run' => Carbon::now()->timestamp
            ]
        );
        return true;
    }
}
