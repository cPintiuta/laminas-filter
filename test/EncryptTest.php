<?php

declare(strict_types=1);

namespace LaminasTest\Filter;

use Laminas\Filter\Encrypt as EncryptFilter;
use Laminas\Filter\Encrypt\EncryptionAlgorithmInterface;
use Laminas\Filter\Exception;
use PHPUnit\Framework\TestCase;
use stdClass;

use function extension_loaded;

class EncryptTest extends TestCase
{
    public function setUp(): void
    {
        if (! extension_loaded('mcrypt') && ! extension_loaded('openssl')) {
            self::markTestSkipped('This filter needs the mcrypt or openssl extension');
        }
    }

    /**
     * Ensures that the filter follows expected behavior
     */
    public function testBasicBlockCipher(): void
    {
        $filter         = new EncryptFilter(['adapter' => 'BlockCipher', 'key' => 'testkey']);
        $valuesExpected = [
            'STRING' => 'STRING',
            'ABC1@3' => 'ABC1@3',
            'A b C'  => 'A B C',
            1        => 1,
            -1       => -1,
            1.0      => 1.0,
            -1.0     => -1.0,
        ];

        $enc = $filter->getEncryption();
        $filter->setVector('1234567890123456');
        self::assertSame('testkey', $enc['key']);
        foreach ($valuesExpected as $input => $output) {
            self::assertNotEquals($output, $filter($input));
        }
    }

    /**
     * Ensures that the encryption works fine
     */
    public function testEncryptBlockCipher(): void
    {
        $encrypt = new EncryptFilter(['adapter' => 'BlockCipher', 'key' => 'testkey']);
        $encrypt->setVector('1234567890123456890');
        $encrypted = $encrypt->filter('test');
        // @codingStandardsIgnoreStart
        self::assertSame($encrypted, 'ec133eb7460682b0020b736ad6d2ef14c35de0f1e5976330ae1dd096ef3b4cb7MTIzNDU2Nzg5MDEyMzQ1NoZvxY1JkeL6TnQP3ug5F0k=');
        // @codingStandardsIgnoreEnd
    }

    /**
     * Ensures that the filter follows expected behavior
     */
    public function testBasicOpenssl(): void
    {
        self::markTestSkipped('The OpenSSL Adapter is deprecated and tests will not pass on OpenSSL 3.x');

        if (! extension_loaded('openssl')) {
            self::markTestSkipped('Openssl extension not installed');
        }

        $filter         = new EncryptFilter(['adapter' => 'Openssl']);
        $valuesExpected = [
            'STRING' => 'STRING',
            'ABC1@3' => 'ABC1@3',
            'A b C'  => 'A B C',
        ];

        $filter->setPublicKey(__DIR__ . '/_files/publickey.pem');
        $key = $filter->getPublicKey();
        self::assertSame(
            [
                __DIR__ . '/_files/publickey.pem'
                  => '-----BEGIN CERTIFICATE-----
MIIC3jCCAkegAwIBAgIBADANBgkqhkiG9w0BAQQFADCBtDELMAkGA1UEBhMCTkwx
FjAUBgNVBAgTDU5vb3JkLUhvbGxhbmQxEDAOBgNVBAcTB1phYW5kYW0xFzAVBgNV
BAoTDk1vYmlsZWZpc2guY29tMR8wHQYDVQQLExZDZXJ0aWZpY2F0aW9uIFNlcnZp
Y2VzMRowGAYDVQQDExFNb2JpbGVmaXNoLmNvbSBDQTElMCMGCSqGSIb3DQEJARYW
Y29udGFjdEBtb2JpbGVmaXNoLmNvbTAeFw0wNzA2MDcxNzM1NTNaFw0wODA2MDYx
NzM1NTNaMIG0MQswCQYDVQQGEwJOTDEWMBQGA1UECBMNTm9vcmQtSG9sbGFuZDEQ
MA4GA1UEBxMHWmFhbmRhbTEXMBUGA1UEChMOTW9iaWxlZmlzaC5jb20xHzAdBgNV
BAsTFkNlcnRpZmljYXRpb24gU2VydmljZXMxGjAYBgNVBAMTEU1vYmlsZWZpc2gu
Y29tIENBMSUwIwYJKoZIhvcNAQkBFhZjb250YWN0QG1vYmlsZWZpc2guY29tMIGf
MA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQDKTIp7FntJt1BioBZ0lmWBE8Cyznge
GCHNMcAC4JLbi1Y0LwT4CSaQarbvAqBRmc+joHX+rcURm89wOibRaThrrZcvgl2p
omzu7shJc0ObiRZC8H7pxTkZ1HHjN8cRSQlOHkcdtE9yoiSGSO+zZ9K5ReU1DOsF
FDD4V7XpcNU63QIDAQABMA0GCSqGSIb3DQEBBAUAA4GBAFQ22OU/PAN7rRDr23NS
2XkpSngwZWeHoFW1D2gRvHHRlqg5Q8KZHQAALd5PEFakehdn03NG6yEdnhXpqKT/
5jYy6v3b+zwEvY82EUieMldovdnpsS1EScjjvPfQ1lSgcTHT2QX5MjNv13xLnOgh
PIDs9E7uuizAKDhRRRvho8BS
-----END CERTIFICATE-----
',
            ],
            $key
        );
        foreach ($valuesExpected as $input => $output) {
            self::assertNotEquals($output, $filter($input));
        }
    }

    public function testSettingAdapterManually(): void
    {
        $filter = new EncryptFilter();

        $filter->setAdapter('BlockCipher');
        self::assertSame('BlockCipher', $filter->getAdapter());
        self::assertInstanceOf(EncryptionAlgorithmInterface::class, $filter->getAdapterInstance());

        $this->expectException(Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('does not implement');
        $filter->setAdapter(stdClass::class);
    }

    public function testCallingUnknownMethod(): void
    {
        $this->expectException(Exception\BadMethodCallException::class);
        $this->expectExceptionMessage('Unknown method');
        $filter = new EncryptFilter();
        $filter->getUnknownMethod();
    }

    public function returnUnfilteredDataProvider()
    {
        return [
            [null],
            [new stdClass()],
            [
                [
                    'encrypt me',
                    'encrypt me too, please',
                ],
            ],
        ];
    }

    /**
     * @dataProvider returnUnfilteredDataProvider
     */
    public function testReturnUnfiltered($input): void
    {
        $encrypt = new EncryptFilter(['adapter' => 'BlockCipher', 'key' => 'testkey']);
        $encrypt->setVector('1234567890123456890');

        $encrypted = $encrypt->filter($input);
        self::assertSame($input, $encrypted);
    }
}
