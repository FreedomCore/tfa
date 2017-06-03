<?php namespace FreedomCore\TFA\Providers\Time;

use FreedomCore\TFA\Interfaces\Time;

/**
 * Class MachineTime
 * @package FreedomCore\TFA\Providers\Time
 */
class MachineTime implements Time {

    /**
     * Get Current Machine Time
     * @return int
     */
    public function getTime() {
        return time();
    }

}