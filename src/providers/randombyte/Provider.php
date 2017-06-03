<?php namespace FreedomCore\TFA\Providers\RandomByte;

use FreedomCore\TFA\Exceptions\TwoFactorAuthenticationException;

/**
 * Class Provider
 * @package FreedomCore\TFA\Providers\RandomByte
 */
class Provider {

    /**
     * Class Instance
     * @var null|Provider
     */
    private static $instance = null;

    /**
     * Get RandomByte Provider Instance
     * @return Provider|null
     */
    public static function Instance() {
        if (self::$instance === null) {
            self::$instance = new Provider();
        }
        return self::$instance;
    }

    /**
     * Get Cryptography Provider
     * @param mixed $selectedProvider
     * @return Basic|Hash|MCrypt|OpenSSL
     * @throws TwoFactorAuthenticationException
     */
    public function getCryptographyProvider($selectedProvider) {
        if ($selectedProvider !== null)
            return $selectedProvider;

        if (function_exists('openssl_random_pseudo_bytes'))
            return new OpenSSL();

        if (function_exists('random_bytes'))
            return new Basic();

        if (function_exists('hash'))
            return new Hash();

        if (function_exists('mcrypt_create_iv'))
            return new MCrypt();

        throw new TwoFactorAuthenticationException('No cryptography providers available!');
    }

    /**
     * Provider constructor.
     */
    protected function __construct() { }
}