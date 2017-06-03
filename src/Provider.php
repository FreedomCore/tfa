<?php namespace FreedomCore\TFA;

use FreedomCore\TFA\Exceptions\TwoFactorAuthenticationException;
use FreedomCore\TFA\Interfaces\QRCode;
use FreedomCore\TFA\Interfaces\RandomByte;
use FreedomCore\TFA\Interfaces\Time;
use FreedomCore\TFA\Providers\Cryptography\Base32;
use FreedomCore\TFA\Providers\Time\ServerTime;

use FreedomCore\TFA\Providers\RandomByte\Provider as RandomProvider;
use FreedomCore\TFA\Providers\Time\Provider as TimeProvider;
use FreedomCore\TFA\Providers\QRCode\Provider as QRProvider;
use FreedomCore\TFA\Providers\Time\UnixTimeDotCom;

/**
 * Class Provider
 * @package FreedomCore\TFA
 */
class Provider {

    /**
     * Token Issuer Name String
     * @var null|string
     */
    private $tokenIssuer = null;
    /**
     * Secure Code Length
     * @var integer|null
     */
    private $codeLength = null;
    /**
     * Issue Period Value
     * @var mixed|null
     */
    private $issuePeriod = null;
    /**
     * Hashing Algorithm Name String
     * @var null|string
     */
    private $hashingAlgorithm = null;

    /**
     * QRCode Provider Instance
     * @var QRProvider|null
     */
    private $qrProviderInstance = null;
    /**
     * QRCode Provider Interface Reference
     * @var QRCode|null
     */
    private $qrProvider = null;

    /**
     * Random Provider Instance
     * @var null|RandomProvider
     */
    private $randomProviderInstance = null;
    /**
     * RandomByte Provider Interface Reference
     * @var RandomByte|null
     */
    private $randomProvider = null;

    /**
     * Time Provider Instance
     * @var null|TimeProvider
     */
    private $timeProviderInstance = null;
    /**
     * Time Provider Interface Reference
     * @var Time|null
     */
    private $timeProvider = null;

    /**
     * Encoder Instance Object
     * @var Base32|null
     */
    private $encoder = null;

    /**
     * Provider constructor.
     * @param null|string $issuer
     * @param int $digits
     * @param int $period
     * @param string $algorithm
     * @param QRCode|null $qrProvider
     * @param RandomByte|null $randomProvider
     * @param Time|null $timeProvider\
     */
    public function __construct($issuer = null, $digits = 6, $period = 30, $algorithm = 'sha1', QRCode $qrProvider = null, RandomByte $randomProvider = null, Time $timeProvider = null) {
        $this->tokenIssuer = $issuer;
        $this->codeLength = Validator::validateCodeLength($digits);
        $this->issuePeriod = Validator::validatePeriod($period);
        $this->hashingAlgorithm = Validator::validateHashingAlgorithm($algorithm);
        $this->qrProvider = $qrProvider;
        $this->randomProvider = $randomProvider;
        $this->timeProvider = $timeProvider;
        $this->encoder = Base32::Instance();

        $this->qrProviderInstance = QRProvider::Instance();
        $this->randomProviderInstance = RandomProvider::Instance();
        $this->timeProviderInstance = TimeProvider::Instance();
        $this->initializeProviders();
    }

    /**
     * Initialize Providers
     */
    private function initializeProviders() {
        $this->randomProvider = $this->randomProviderInstance->getCryptographyProvider($this->randomProvider);
        $this->timeProvider = $this->timeProviderInstance->getProvider($this->timeProvider);
        $this->qrProvider = $this->qrProviderInstance->getProvider($this->qrProvider);
    }

    /**
     * Create New Secret String
     * @param int $bits
     * @param bool $requireSecure
     * @return string
     * @throws TwoFactorAuthenticationException
     */
    public function createSecret($bits = 80, $requireSecure = true) {
        $secretString = '';
        $bytes = ceil($bits / 5);
        if ($requireSecure && !$this->randomProvider->isSecure())
            throw new TwoFactorAuthenticationException('RandomByte Provider is not cryptographically secure!');
        $randomBytes = $this->randomProvider->getBytes($bytes);
        for ($i = 0; $i < $bytes; $i++)
            $secretString .= $this->encoder->getReference()[ord($randomBytes[$i]) & 31];
        return $secretString;
    }

    /**
     * Calculate Code For Given Secret @ Given Time
     * @param string $secret
     * @param null|integer $time
     * @return string
     */
    public function getCode($secret, $time = null) {
        $secretKey = $this->encoder->decode($secret);
        $timeStamp = "\0\0\0\0" . pack('N*', $this->timeProviderInstance->timeSlice($this->issuePeriod, $this->timeProviderInstance->getTime($time)));
        $hashHMAC = hash_hmac($this->hashingAlgorithm, $timeStamp, $secretKey, true);
        $hashPart = substr($hashHMAC, ord(substr($hashHMAC, -1)) & 0x0F, 4);
        $value = unpack('N', $hashPart);
        $value = $value[1] & 0x7FFFFFFF;
        return str_pad($value % pow(10, $this->codeLength), $this->codeLength, '0', STR_PAD_LEFT);
    }

    /**
     * Verify Provided Code
     * @param string $secret
     * @param integer$code
     * @param integer $discrepancy
     * @param null|integer|mixed $time
     * @return bool
     */
    public function verifyCode($secret, $code, $discrepancy = 1, $time = null) {
        $result = false;
        $timeStamp = $this->timeProviderInstance->getTime($time);
        for ($i = -$discrepancy; $i <= $discrepancy; $i++)
            $result |= $this->codeEquals($this->getCode($secret, $timeStamp + ($i * $this->issuePeriod)), $code);
        return (bool)$result;
    }

    /**
     * Check If Provided Codes Are Equal
     * @param string|integer $savedCode
     * @param string|integer $userCode
     * @return bool
     */
    public function codeEquals($savedCode, $userCode) {
        if (function_exists('hash_equals'))
            return hash_equals($savedCode, $userCode);

        if (strlen($savedCode) === strlen($userCode)) {
            $result = 0;
            for ($i = 0; $i < strlen($savedCode); $i++)
                $result |= (ord($savedCode[$i]) ^ ord($userCode[$i]));
            return $result === 0;
        }

        return false;
    }

    /**
     * Validate Current Time To Ensure That Time Is
     * Within Specified Number Of Seconds
     * @param array|null $timeProviders
     * @param int $leniency
     * @throws TwoFactorAuthenticationException
     */
    public function validateTime(array $timeProviders = null, $leniency = 5) {
        if ($timeProviders !== null && !is_array($timeProviders))
            throw new TwoFactorAuthenticationException('No Time Providers Specified!');

        if ($timeProviders === null)
            $timeProviders = [
                new UnixTimeDotCom(),
                new ServerTime()
            ];

        foreach ($timeProviders as $provider) {
            if (!($provider instanceof Time))
                throw new TwoFactorAuthenticationException('Object does not implement Time Interface');

            if (abs($this->timeProvider->getTime() - $provider->getTime()) > $leniency)
                throw new TwoFactorAuthenticationException(sprintf('Time for Time Provider is off by more than %d seconds when compared to %s', $leniency, get_class($provider)));
        }
    }

    /**
     * Convert Image Bytes To Image Data URI
     * @param string $label
     * @param string $secret
     * @param int $size
     * @return string
     * @throws TwoFactorAuthenticationException
     */
    public function imageToData($label, $secret, $size = 200) {
        if (!is_int($size) || $size <= 0)
            throw new TwoFactorAuthenticationException('Size must be an integer and greater than 0!');
        return 'data:'
            . $this->qrProvider->getMimeType()
            . ';base64,'
            . base64_encode($this->qrProvider->getImage($this->getQRText($label, $secret), $size));
    }

    /**
     * Get QR Code Encoded Text
     * @param string $label
     * @param string $secret
     * @return string
     */
    public function getQRText($label, $secret) {
        return 'otpauth://totp/' . rawurlencode($label)
            . '?secret=' . rawurlencode($secret)
            . '&issuer=' . rawurlencode($this->tokenIssuer)
            . '&period=' . intval($this->issuePeriod)
            . '&algorithm=' . rawurlencode(strtoupper($this->hashingAlgorithm))
            . '&digits=' . intval($this->codeLength);
    }
}