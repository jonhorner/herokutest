<?php

namespace App\Traits;

trait GetterSetterTrait
{

    /**
     * Controls output of debug code
     *
     * @var bool
     */
    private $debug = false;

    /**
     * Control sending data to Google Sheets
     * @var bool
     */
    private $sendToGoogle = false;

    /**
     *
     *
     * @var bool
     */
    private $useKeys = false;

    /**
     * Define whether or not this is run from a cron
     *
     * @var bool
     */
    private $isCron = false;


    /**
     * @return bool
     */
    public function getDebug(): bool
    {
        return $this->debug;
    }

    /**
     * @param mixed $debug
     * @return MetaReportTrait
     */
    public function setDebug($debug): self
    {
        $this->debug = $debug;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getIsCron(): bool
    {
        return $this->isCron;
    }

    /**
     * @param mixed $isCron
     * @return MetaReportTrait
     */
    public function setIsCron($isCron): self
    {
        $this->isCron = $isCron;

        return $this;
    }

    /**
     * @return bool
     */
    public function getSendToGoogle(): bool
    {
        return $this->sendToGoogle;
    }

    /**
     * @param mixed $sendToGoogle
     * @return MetaReportTrait
     */
    public function setSendToGoogle($sendToGoogle): self
    {
        $this->sendToGoogle = $sendToGoogle;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getUseKeys(): bool
    {
        return $this->useKeys;
    }

    /**
     * @param mixed $useKeys
     * @return MetaReportTrait
     */
    public function setUseKeys($useKeys): self
    {
        $this->useKeys = $useKeys;

        return $this;
    }
}
