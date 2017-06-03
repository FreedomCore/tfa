<?php namespace FreedomCore\TFA\Providers\Cryptography;

use FreedomCore\TFA\Exceptions\TwoFactorAuthenticationException;

/**
 * Class Base32
 * @package FreedomCore\TFA\Providers\Cryptography
 */
class Base32 {

    /**
     * Dictionary String
     * @var string
     */
    private $dictionary = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567=';

    /**
     * Lookup Array
     * @var array
     */
    private $lookup = [];

    private $reference = null;

    /**
     * Class Instance
     * @var null|Base32
     */
    private static $instance = null;

    /**
     * Array Of Supported Hashing Algorithms
     * @var array
     */
    public static $supportedAlgorithms = ['sha1', 'sha256', 'sha512', 'md5'];

    /**
     * Get Class Instance
     * @return Base32|null
     */
    public static function Instance() {
        if (self::$instance === null) {
            self::$instance = new Base32();
        }
        return self::$instance;
    }

    /**
     * Get Reference String
     * @return array|null
     */
    public function getReference() {
        return $this->reference;
    }

    /**
     * Decode Base32 Encoded String
     * @param string $value
     * @return string
     * @throws TwoFactorAuthenticationException
     */
    public function decode($value) {
        if (strlen($value) === 0) return '';
        if (preg_match('/[^' . preg_quote($this->dictionary) . ']/', $value) !== 0)
            throw new TwoFactorAuthenticationException('Invalid Base32 String');
        $bufferString = '';
        foreach (str_split($value) as $character) {
            if ($character !== '=')
                $bufferString .= str_pad(decbin($this->lookup[$character]), 5, 0, STR_PAD_LEFT);
        }
        $bufferLength = strlen($bufferString);
        $bufferBlocks = trim(chunk_split(substr($bufferString, 0, $bufferLength - ($bufferLength % 8)), 8, ' '));

        $outputString = '';
        foreach (explode(' ', $bufferBlocks) as $block)
            $outputString .= chr(bindec(str_pad($block, 8, 0, STR_PAD_RIGHT)));
        return $outputString;
    }

    /**
     * Base32 constructor.
     */
    protected function __construct() {
        $this->reference = str_split($this->dictionary);
        $this->lookup = array_flip($this->reference);
    }
}