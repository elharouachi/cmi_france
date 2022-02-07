<?php

namespace App\Logger;

trait LoggableTrait
{
    private $isLogged = false;

    /**
     * Indicates if this object has been logged
     * @return bool
     */
    public function isLogged(): bool
    {
        return $this->isLogged;
    }

    /**
     * Marks this object as logged
     */
    public function markAsLogged()
    {
        $this->isLogged = true;
    }
}
