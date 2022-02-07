<?php

namespace App\Logger;

interface Loggable
{
    /**
     * Indicates if this object has been logged
     * @return bool
     */
    public function isLogged(): bool;

    /**
     * Marks this object as logged
     */
    public function markAsLogged();
}
