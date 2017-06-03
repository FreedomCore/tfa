<?php namespace FreedomCore\TFA;

use FreedomCore\TFA\Exceptions\TwoFactorAuthenticationException;
use FreedomCore\TFA\Providers\Cryptography\Base32;

/**
 * Class Validator
 * @package FreedomCore\TFA
 */
class Validator {

    /**
     * Validate Requested Code Length Value
     * @param string|integer $codeLength
     * @return mixed
     * @throws TwoFactorAuthenticationException
     */
    public static function validateCodeLength($codeLength) {
        if (!is_int($codeLength) || $codeLength <= 0)
            throw new TwoFactorAuthenticationException('Code length should be greater than zero!');
        return $codeLength;
    }

    /**
     * Validate Issue Period Value
     * @param string|integer $periodValue
     * @return mixed
     * @throws TwoFactorAuthenticationException
     */
    public static function validatePeriod($periodValue) {
        if (!is_int($periodValue) || $periodValue <= 0)
            throw new TwoFactorAuthenticationException('Issue Period should be greater than zero!');
        return $periodValue;
    }

    /**
     * Validate Provided Hashing Algorithm
     * @param string $algorithm
     * @return string
     * @throws TwoFactorAuthenticationException
     */
    public static function validateHashingAlgorithm($algorithm) {
        $algorithm = strtolower(trim($algorithm));
        if (!in_array($algorithm, Base32::$supportedAlgorithms))
            throw new TwoFactorAuthenticationException('Unsupported Algorithm: ' . $algorithm);
        return $algorithm;
    }
}