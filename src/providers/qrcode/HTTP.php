<?php namespace FreedomCore\TFA\Providers\QRCode;

use FreedomCore\TFA\Interfaces\QRCode;

/**
 * Class HTTP
 * @package FreedomCore\TFA\Providers\QRCode
 */
abstract class HTTP implements QRCode {

    /**
     * Should We Verify SSL Certificate
     * @var bool
     */
    protected $verifySSL = false;

    /**
     * Get URL Content
     * @param string $urlAddress
     * @return mixed
     */
    protected function getContent($urlAddress) {
        $curlHandler = curl_init();
        curl_setopt_array($curlHandler, [
            CURLOPT_URL => $urlAddress,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_DNS_CACHE_TIMEOUT => 10,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => $this->verifySSL,
            CURLOPT_USERAGENT => 'FreedomCore TFA Client'
        ]);
        $response = curl_exec($curlHandler);
        curl_close($curlHandler);
        return $response;
    }

}