<?php namespace FreedomCore\TFA\Providers\QRCode;

/**
 * Class Provider
 * @package FreedomCore\TFA\Providers\QRCode
 */
class Provider {

    /**
     * Class Instance Object
     * @var null|Provider
     */
    private static $instance = null;

    /**
     * Get Class Instance Object
     * @return Provider|null
     */
    public static function Instance() {
        if (self::$instance === null) {
            self::$instance = new Provider();
        }
        return self::$instance;
    }

    /**
     * Get QR Code Provider
     * @param mixed $currentProvider
     * @return GoogleQRCode
     */
    public function getProvider($currentProvider) {
        if ($currentProvider !== null)
            return $currentProvider;
        return new GoogleQRCode();
    }
}