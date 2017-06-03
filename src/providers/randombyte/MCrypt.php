<?php namespace FreedomCore\TFA\Providers\RandomByte;

use FreedomCore\TFA\Exceptions\RandomByteException;
use FreedomCore\TFA\Interfaces\RandomByte;

class MCrypt implements RandomByte {

    /**
     * Source Of Random Bytes
     * @var int
     */
    private $randomSource;

    /**
     * MCrypt constructor.
     * @param int $source
     */
    public function __construct($source = MCRYPT_DEV_URANDOM) {
        $this->randomSource = $source;
    }

    /**
     * Get Selected Amount Of Random Bytes
     * @param int $bytesCount
     * @return string
     * @throws RandomByteException
     */
    public function getBytes($bytesCount) {
        $result = mcrypt_create_iv($bytesCount, $this->randomSource);
        if ($result === false)
            throw new RandomByteException('mcrypt_create_iv returned an invalid value');
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