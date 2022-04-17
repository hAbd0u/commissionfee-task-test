<?php

declare(strict_types=1);

namespace CommissionFees\Service;

class Logs
{
    private $error_description = '';

    public function __construct()
    {
        $this->isDebugMode = false;
    }

    public function setDebugMode(bool $enabled): void
    {
        $this->isDebugMode = $enabled;
    }

    public function printDebugMessage(string $message): void
    {
        if ($this->isDebugMode) {
            echo $message.PHP_EOL;
        }
    }

    /**
     * Set the text description of an error.
     *
     * @return string A description of an error
     */
    public function setErrorMessage(string $message): void
    {
        $this->error_description = $message;
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
