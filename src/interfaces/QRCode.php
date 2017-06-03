<?php namespace FreedomCore\TFA\Interfaces;

/**
 * Interface QRCode
 * @package FreedomCore\TFA\Interfaces
 */
interface QRCode {

    /**
     * Get QR Code Image
     * @param string $qrText
     * @param mixed $imageSize
     * @return mixed
     */
    public function getImage($qrText, $imageSize);

    /**
     * Get Image MIME Type
     * @return mixed
     */
    public function getMimeType();

}