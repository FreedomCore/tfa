<?php namespace FreedomCore\TFA\Tests;


use FreedomCore\TFA\Interfaces\RandomByte;
use FreedomCore\TFA\Interfaces\Time;
use FreedomCore\TFA\Provider as EntryPoint;
use FreedomCore\TFA\Providers\Time\MachineTime;
use FreedomCore\TFA\Providers\Time\ServerTime;
use FreedomCore\TFA\Providers\Time\UnixTimeDotCom;
use PHPUnit\Framework\TestCase;

/**
 * Class TFATest
 * @package FreedomCore\TFA\Tests
 */
class TFATest extends TestCase {

    /**
     * Secret Code String
     * @var string
     */
    protected $secretCode = 'K4C54QRWAECUQPRDCCQRRKC3VWPDEUMN';

    /**
     * Authentication Code For Given Timestamp
     * @var int
     */
    protected $authenticationCode = 186332;

    /**
     * When secret key and authentication code generated
     * @var int
     */
    protected $issueTime = 1496641949;

    /**
     * Test if constructor accepts only digits greater than 0
     */
    public function testConstructorInvalidDigits() {
        $this->expectException('\FreedomCore\TFA\Exceptions\TwoFactorAuthenticationException');
        new EntryPoint('Test Case', 0);
    }

    /**
     * Test if exception will be thrown IF issue period is less/equal to zero
     */
    public function testConstructorInvalidIssuePeriod() {
        $this->expectException('\FreedomCore\TFA\Exceptions\TwoFactorAuthenticationException');
        new EntryPoint('Test Case', 6, 0);
    }

    /**
     * Test IF exception is thrown on invalid hashing algorithm
     */
    public function testConstructorHashingAlgorithmValidation() {
        $this->expectException('\FreedomCore\TFA\Exceptions\TwoFactorAuthenticationException');
        new EntryPoint('Test Case', 6, 30, 'invalid algorithm');
    }

    /**
     * Test if getCode function returns expected result
     */
    public function testValidateResultReturnedBy_getCode_Function() {
        $provider = new EntryPoint('Test Case', 6, 30);
        $this->assertEquals($this->authenticationCode, $provider->getCode($this->secretCode, $this->issueTime));
    }

    /**
     * Test IF exception is thrown while using insecure RandomByte Provider
     */
    public function testInsecureProviderExceptionThrown() {
        $this->expectException('\FreedomCore\TFA\Exceptions\TwoFactorAuthenticationException');
        $provider = new EntryPoint('Test Case', 6, 30, 'sha1', null, new TestCaseRandomByte());
        $provider->createSecret(160);
    }

    /**
     * Test IF secure provider not throwing exceptions
     */
    public function testSecureProviderExceptionNotThrown() {
        $provider = new EntryPoint('Test Case', 6, 30, 'sha1', null, new TestCaseRandomByte(true));
        $this->assertEquals('ABCDEFGHIJKLMNOP', $provider->createSecret());
    }

    /**
     * Test IF Time Comparison Function Works For Correct Time
     * And No Exception Is Thrown
     */
    public function testTimeComparisonFunctionWorksForCorrectTime() {
        try {
            $provider = new EntryPoint('Test Case', 6, 30, 'sha1', null, null, new TestCaseTime(time()));
            $provider->validateTime([new TestCaseTime(time() + 4)]);
        } catch (\FreedomCore\TFA\Exceptions\TwoFactorAuthenticationException $exception) {
            $this->fail();
        }
        $this->assertTrue(true);
    }

    /**
     * Test IF Time Comparison Function Fails On Incorrect Time
     */
    public function testTimeComparisonFunctionWorksForIncorrectTime() {
        $this->expectException('\FreedomCore\TFA\Exceptions\TwoFactorAuthenticationException');
        $provider = new EntryPoint('Test Case', 6, 30, 'sha1', null, null, new TestCaseTime(time()));
        $provider->validateTime([new TestCaseTime(time() + 4)], 0);
    }

    /**
     * Check IF default time provider returns correct time
     */
    public function testCheckIfTimeProviderReturnsCorrectTime() {
        try {
            $provider = new EntryPoint('Test Case', 6, 30, 'sha1');
            $provider->validateTime([new TestCaseTime(time())], 1);
        } catch (\FreedomCore\TFA\Exceptions\TwoFactorAuthenticationException $exception) {
            $this->fail();
        }
        $this->assertTrue(true);
    }

    /**
     * Check IF all time providers return correct time
     */
    public function testCheckIfAllTimeProvidersReturnCorrectTime() {
        try {
            $provider = new EntryPoint('Test Case');
            $provider->validateTime([
                new UnixTimeDotCom(),
                new ServerTime(),
                new ServerTime('https://github.com'),
                new MachineTime()
            ]);
        } catch (\FreedomCore\TFA\Exceptions\TwoFactorAuthenticationException $exception) {
            $this->fail();
        }
        $this->assertTrue(true);
    }

    /**
     * Check if verifyCode Function Works Correctly
     */
    public function testCheck_if_verifyCode_function_works_correctly() {
        $provider = new EntryPoint('Test Case', 6, 30);
        $this->assertEquals(true, $provider->verifyCode($this->secretCode, $this->authenticationCode, 1, $this->issueTime));
        $this->assertEquals(true, $provider->verifyCode($this->secretCode, $this->authenticationCode, 0, $this->issueTime + 29));
        $this->assertEquals(false, $provider->verifyCode($this->secretCode, $this->authenticationCode, 0, $this->issueTime + 30));
        $this->assertEquals(false, $provider->verifyCode($this->secretCode, $this->authenticationCode, 0, $this->issueTime - 1));
    }

}

/**
 * Class TestCaseRandomByte
 * @package FreedomCore\TFA\Tests
 */
class TestCaseRandomByte implements RandomByte {

    /**
     * Is Provider Considered To Be Secure
     * @var bool
     */
    private $secure;

    /**
     * InsecureProvider constructor.
     * @param bool $isSecure
     */
    public function __construct($isSecure = false) {
        $this->secure = $isSecure;
    }

    /**
     * @inheritdoc
     * @param int $bytesCount
     * @return string
     */
    public function getBytes($bytesCount) {
        $result = '';
        for ($i = 0; $i < $bytesCount; $i++)
            $result .= chr($i);
        return $result;
    }

    /**
     * @inheritdoc
     * @return bool
     */
    public function isSecure() {
        return $this->secure;
    }

}

/**
 * Class TestCaseTime
 * @package FreedomCore\TFA\Tests
 */
class TestCaseTime implements Time {

    /**
     * Current Time
     * @var integer
     */
    private $time;

    /**
     * TestCaseTime constructor.
     * @param integer $time
     */
    public function __construct($time) {
        $this->time = $time;
    }

    /**
     * @inheritdoc
     * @return integer
     */
    public function getTime() {
        return $this->time;
    }

}