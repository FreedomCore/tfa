<?php namespace FreedomCore\TFA\Interfaces;

/**
 * Interface RandomByte
 * @package FreedomCore\TFA\Interfaces
 */
interface RandomByte {

    /**
     * Get Selected Amount Of Random Bytes
     * @param integer $bytesCount
     * @return mixed
     */
    public function getBytes($bytesCount);

    /**
     * Check If Bytes Are Cryptographically Secure
     * @return boolean
     */
    public function isSecure();

}