<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\JsonResponse;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Traits\SquadReportTrait;

/**
 * Class SquadReportJob
 * @package App\Jobs
 */
class SquadReportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, SquadReportTrait;


    /**
     * The number of seconds after which the job's unique lock will be released.
     *
     * @var int
     */
    public $uniqueFor = 200;


    /**
     * Create a new job instance.
     *
     * @param bool $debug
     * @param bool $isCron
     * @param bool $sendToGoogle
     */
    public function __construct(
        Bool $debug = false,
        Bool $isCron = false,
        Bool $sendToGoogle = true
    )
    {
        $this->setDebug($debug)
            ->setIsCron($isCron)
            ->setSendToGoogle($sendToGoogle);
    }

    /**
     * Execute the job.
     *
     * @return null|JsonResponse
     */
    public function handle(): ?JsonResponse
    {
        try {
            $this->getGuildMetaSquads();
        } catch (\Exception $e){
            return response()->json($e);
        }
        return null;
    }
}
