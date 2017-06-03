<?php namespace FreedomCore\TFA\Providers\RandomByte;

use FreedomCore\TFA\Exceptions\RandomByteException;
use FreedomCore\TFA\Interfaces\RandomByte;

class OpenSSL implements RandomByte {

    /**
     * Ensure That Generated Value Is
     * Cryptographically Strong
     * @var bool
     */
    private $requireStrong;

    /**
     * OpenSSL constructor.
     * @param bool $requireStrong
     */
    public function __construct($requireStrong = true) {
        $this->requireStrong = $requireStrong;
    }

    /**
     * Get Selected Amount Of Random Bytes
     * @param int $bytesCount
     * @return string
     * @throws RandomByteException
     */
    public function getBytes($bytesCount) {
        $result = openssl_random_pseudo_bytes($bytesCount, $cryptographicallyStrong);
        if ($this->requireStrong && ($cryptographicallyStrong === false))
            throw new RandomByteException('openssl_random_pseudo_bytes returned non-cryptographically strong value');
        if ($result === false)
            throw new RandomByteException('openssl_random_pseudo_bytes returned an invalid value');
        return $result;
    }

    /**
     * Get Value Secure Status
     * @return bool
     */
    public function isSecure() {
        return $this->requireStrong;
    }

}