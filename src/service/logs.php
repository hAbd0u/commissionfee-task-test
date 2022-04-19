<?php

declare(strict_types=1);

namespace CommissionFees\Service;

class Logs
{
    private $isDebugMode = false;
    private $error_description = '';

    public function __construct()
    {
    }

    /**
     * Set the debugging mode.
     *
     * @param bool $enabled Set the debugging mode
     */
    public function setDebugMode(bool $enabled): void
    {
        $this->isDebugMode = $enabled;
    }

    /**
     * Print the message passed.
     *
     * @param string $message Print the message
     */
    public function printDebugMessage(string $message): void
    {
        $this->error_description = $message;
        if ($this->isDebugMode) {
            echo $message . PHP_EOL;
        }
    }

    /**
     * Get the text description of the last error.
     *
     * @return string A description for last error occurred
     */
    public function getLastError()
    {
        return $this->error_description;
    }
}
