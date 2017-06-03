<?php namespace FreedomCore\TFA\Providers\Time;

use FreedomCore\TFA\Exceptions\TimeException;
use FreedomCore\TFA\Interfaces\Time;

/**
 * Class UnixTimeDotCom
 * @package FreedomCore\TFA\Providers\Time
 */
class UnixTimeDotCom implements Time {

    /**
     * Get Time
     * @return mixed
     * @throws TimeException
     */
    public function getTime() {
        $json = @json_decode(
                @file_get_contents('http://www.convert-unix-time.com/api?timestamp=now')
        );
        if ($json === null || !is_int($json->timestamp))
            throw new TimeException('Unable to retrieve time from convert-unix-time.com');
        return $json->timestamp;
    }
}