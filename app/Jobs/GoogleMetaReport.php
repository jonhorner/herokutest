<?php

namespace App\Jobs;

use App\Traits\MetaReportTrait;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\JsonResponse;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Class GoogleMetaReport
 * @package App\Jobs
 */
class GoogleMetaReport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, MetaReportTrait;

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
     * @param bool $useKeys
     * @param bool $isCron
     * @param bool $sendToGoogle
     */
    public function __construct(
        Bool $debug = false,
        Bool $useKeys = false,
        Bool $isCron = false,
        Bool $sendToGoogle = true
    )
    {
        $this->setDebug($debug)
            ->setUseKeys($useKeys)
            ->setIsCron($isCron)
            ->setSendToGoogle($sendToGoogle);
    }

    /**
     * Execute the job.
     **/
    public function handle(): ?JsonResponse
    {
        return $this->submitDataToServer();
    }

}
