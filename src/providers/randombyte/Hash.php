<?php namespace FreedomCore\TFA\Providers\RandomByte;

use FreedomCore\TFA\Exceptions\RandomByteException;
use FreedomCore\TFA\Interfaces\RandomByte;

/**
 * Class Hash
 * @package FreedomCore\TFA\Providers\RandomByte
 */
class Hash implements RandomByte {

    /**
     * Hash Algorithm
     * @var string
     */
    private $hashAlgorithm;

    /**
     * Hash constructor.
     * @param string $algorithm
     * @throws RandomByteException
     */
    public function __construct($algorithm = 'sha256') {
        $availableAlgorithms = array_values(hash_algos());
        if (!in_array($algorithm, $availableAlgorithms, true))
            throw new RandomByteException('Unsupported Algorithm Requested');
        $this->hashAlgorithm = $algorithm;
    }

    /**
     * Get Selected Amount Of Random Bytes
     * @param int $bytesCount
     * @return string
     * @throws RandomByteException
     */
    public function getBytes($bytesCount) {
        $result = '';
        $hash = mt_rand();
        for ($i = 0; $i < $bytesCount; $i++) {
            $hash = hash($this->hashAlgorithm, $hash.mt_rand(), true);
            $result .= $hash[mt_rand(0, sizeof($hash))];
        }
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