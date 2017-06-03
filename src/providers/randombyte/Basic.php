<?php namespace FreedomCore\TFA\Providers\RandomByte;

use FreedomCore\TFA\Interfaces\RandomByte;

/**
 * Class Basic
 * @package FreedomCore\TFA\Providers\RandomByte
 */
class Basic implements RandomByte {

    /**
     * Get Selected Amount Of Random Bytes
     * @param int $bytesCount
     * @return string
     * @throws RandomByteException
     */
    public function getBytes($bytesCount) {
        return random_bytes($bytesCount);
    }

    /**
     * Get Value Secure Status
     * @return bool
     */
    public function isSecure() {
        return true;
    }

}