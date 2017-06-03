<?php namespace FreedomCore\TFA\Providers\Time;

use FreedomCore\TFA\Interfaces\Time;

/**
 * Class Provider
 * @package FreedomCore\TFA\Providers\Time
 */
class Provider {

    /**
     * Class Instance Object
     * @var null|Provider
     */
    private static $instance = null;

    /**
     * Get Time Provider Instance
     * @return Provider|null
     */
    public static function Instance() {
        if (self::$instance === null) {
            self::$instance = new Provider();
        }
        return self::$instance;
    }

    /**
     * Get Current Time
     * @param integer $time
     * @param mixed $currentProvider
     * @return mixed
     */
    public function getTime($time, $currentProvider = null) {
        return ($time === null) ? $this->getProvider($currentProvider)->getTime() : $time;
    }

    /**
     * Get Time Slice
     * @param null|integer $period
     * @param null|integer $time
     * @param int $offset
     * @return int
     */
    public function timeSlice($period = null, $time = null, $offset = 0) {
        return (integer) floor ($time / $period) + ($offset * $period);
    }

    /**
     * Get Time Provider
     * @param MachineTime|ServerTime|Time|null $currentProvider
     * @return MachineTime|ServerTime|Time
     */
    public function getProvider($currentProvider) {
        if ($currentProvider === null)
            return new MachineTime();
        return $currentProvider;
    }

}