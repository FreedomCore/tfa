<?php namespace FreedomCore\TFA\Providers\Time;

use FreedomCore\TFA\Exceptions\TimeException;
use FreedomCore\TFA\Interfaces\Time;

/**
 * Class ServerTime
 * @package FreedomCore\TFA\Providers\Time
 */
class ServerTime implements Time {

    /**
     * Server Address String
     * @var null|string
     */
    protected $serverAddress = null;

    /**
     * Contains Request Options
     * @var array
     */
    protected $requestOptions = [];

    /**
     * Expected Time Format
     * @var null|string
     */
    protected $expectedFormat = null;

    /**
     * ServerTime constructor.
     * @param string $url
     * @param string $format
     * @param array|null $options
     */
    public function __construct($url = 'https://google.com', $format = 'D, d M Y H:i:s O+', array $options = null) {
        $this->serverAddress = $url;
        $this->expectedFormat = $format;
        $this->requestOptions = $options;
        $this->parseOptions();
    }

    /**
     * Get Server Time
     * @return int
     * @throws TimeException
     */
    public function getTime() {
        try {
            $context = stream_context_create($this->requestOptions);
            $fileHandler = fopen($this->serverAddress, 'rb', false, $context);
            $responseHeaders = stream_get_meta_data($fileHandler);
            fclose($fileHandler);

            foreach ($responseHeaders['wrapper_data'] as $header) {
                if (strcasecmp(substr($header, 0, 5), 'Date:') === 0)
                    return \DateTime::createFromFormat($this->expectedFormat, trim(substr($header, 5)))->getTimestamp();
            }
            throw new TimeException(sprintf('Invalid or no "Date:" header found'), $this->serverAddress);
        } catch (\Exception $exception) {
            throw new TimeException(sprintf('Unable to retrieve time from %s (%s)', $this->serverAddress, $exception->getMessage()));
        }
    }

    /**
     * Parse Request Options
     */
    private function parseOptions() {
        if ($this->requestOptions === null) {
            $this->requestOptions = [
                'http'  =>  [
                    'method'            =>  'HEAD',
                    'follow_location'   =>  false,
                    'ignore_errors'     =>  true,
                    'max_redirects'     =>  0,
                    'request_fulluri'   =>  true,
                    'header'            =>  [
                        'Connection: close',
                        'User-agent: FreedomCore TFA Provider (https://github.com/freedomcore/tfa)'
                    ]
                ]
            ];
        }
    }
}