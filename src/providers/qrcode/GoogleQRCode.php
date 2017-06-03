<?php namespace FreedomCore\TFA\Providers\QRCode;

use FreedomCore\TFA\Exceptions\QRException;

/**
 * Class GoogleQRCode
 * @package FreedomCore\TFA\Providers\QRCode
 */
class GoogleQRCode extends HTTP {

    public $errorCorrectionLevel;
    public $margin;

    /**
     * GoogleQRCode constructor.
     * @param bool $verifySSL
     * @param string $errorCorrectionLevel
     * @param int $margin
     * @throws QRException
     */
    public function __construct($verifySSL = false, $errorCorrectionLevel = 'L', $margin = 1) {
        if (!is_bool($verifySSL))
            throw new QRException('VerifySSL Parameter Must Be Boolean');
        $this->verifySSL = $verifySSL;
        $this->errorCorrectionLevel = $errorCorrectionLevel;
        $this->margin = $margin;
    }

    /**
     * Get Image MIME Type
     * @return string
     */
    public function getMimeType() {
        return 'image/png';
    }

    /**
     * Get Image From URL
     * @param string $qrText
     * @param mixed $imageSize
     * @return mixed
     */
    public function getImage($qrText, $imageSize) {
        return $this->getContent($this->getUrl($qrText, $imageSize));
    }

    /**
     * Get QR Image URL
     * @param string $qrText
     * @param integer $imageSize
     * @return string
     */
    public function getUrl($qrText, $imageSize) {
        return 'https://chart.googleapis.com/chart?cht=qr'
            . '&chs=' . $imageSize . 'x' . $imageSize
            . '&chld=' . $this->errorCorrectionLevel . '|' . $this->margin
            . '&chl=' . rawurlencode($qrText);
    }
}