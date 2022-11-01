<?php

declare(strict_types=1);

namespace LaminasTest\Filter\Encrypt;

use Laminas\Filter\Encrypt\Openssl as OpensslEncryption;
use Laminas\Filter\Exception;
use Laminas\Filter\Exception\RuntimeException;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;

use function extension_loaded;
use function trim;

class OpensslTest extends TestCase
{
    public function setUp(): void
    {
        if (! extension_loaded('openssl')) {
            $this->markTestSkipped('This filter needs the openssl extension');
        }
    }

    /**
     * Ensures that the filter follows expected behavior
     */
    public function testBasicOpenssl(): void
    {
        $filter         = new OpensslEncryption(__DIR__ . '/../_files/publickey.pem');
        $valuesExpected = [
            'STRING' => 'STRING',
            'ABC1@3' => 'ABC1@3',
            'A b C'  => 'A B C',
        ];

        $key = $filter->getPublicKey();
        $this->assertSame(
            [
                __DIR__ . '/../_files/publickey.pem'
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
            $this->assertNotEquals($output, $filter->encrypt($input));
        }
    }

    public function testSetPublicKey(): void
    {
        $filter = new OpensslEncryption();

        $r = $filter->setPublicKey(['private' => __DIR__ . '/../_files/publickey.pem']);
        $this->assertSame($filter, $r);

        $this->expectException(Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('not valid');
        $filter->setPublicKey(123);
    }

    public function testSetPrivateKey(): void
    {
        $filter = new OpensslEncryption();

        $filter->setPrivateKey(['public' => __DIR__ . '/../_files/privatekey.pem']);
        $test = $filter->getPrivateKey();
        $this->assertSame([
            __DIR__ . '/../_files/privatekey.pem' => '-----BEGIN RSA PRIVATE KEY-----
MIICXgIBAAKBgQDKTIp7FntJt1BioBZ0lmWBE8CyzngeGCHNMcAC4JLbi1Y0LwT4
CSaQarbvAqBRmc+joHX+rcURm89wOibRaThrrZcvgl2pomzu7shJc0ObiRZC8H7p
xTkZ1HHjN8cRSQlOHkcdtE9yoiSGSO+zZ9K5ReU1DOsFFDD4V7XpcNU63QIDAQAB
AoGBALr0XY4/SpTnmpxqwhXg39GYBZ+5e/yj5KkTbxW5oT7P2EzFn1vyaPdSB9l+
ndaLxP68zg8dXGBXlC9tLm6dRQtocGupUPB1HOEQbUIlQdiKF/W7/8w6uzLNXdid
qCSLrSJ4cfkYKtS29Xi6qooRw2DOvUFngXy/ELtmTeiBcihpAkEA8+oUesTET+TO
IYM0+l5JrTOpCPZt+aY4JPmWoKz9bshJT/DP2KPgmqd8/Vy+i23yIfOwUxbpwbna
aKzNPi/uywJBANRSl7RNL7jh1BJRQC7+mvUVTE8iQwbyGtIipcLC7bxwhNQzuPKS
P4o/a1+HEVB9Nv1Em7DqKTwBnlkJvaFZ3/cCQQCcvx0SGEkgHqXpG2x8SQOH7t7+
B399I7iI6mxGLWVgQA389YBcdFPujxvfpi49ZBZqgzQY8WyfNlSJWCM9h4gpAkAu
qxzHN7QGmjSn9g36hmH+/rhwKGK9MxfsGkt+/KOOqNi5X8kGIFkxBPGP5LtMisk8
cAkcoMuBcgWhIn/46C1PAkEAzLK/ibrdMQLOdO4SuDgj/2nc53NZ3agl61ew8Os6
d/fxzPfuO/bLpADozTAnYT9Hu3wPrQVLeAfCp0ojqH7DYg==
-----END RSA PRIVATE KEY-----
',
        ], $test);

        $this->expectException(Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('not valid');
        $filter->setPrivateKey(123);
    }

    public function testToString(): void
    {
        $filter = new OpensslEncryption();
        $this->assertSame('Openssl', $filter->toString());
    }

    public function testInvalidDecryption(): void
    {
        $filter = new OpensslEncryption();
        try {
            $filter->decrypt('unknown');
            $this->fail();
        } catch (RuntimeException $e) {
            $this->assertStringContainsString('Please give a private key', $e->getMessage());
        }

        $filter->setPrivateKey(['public' => __DIR__ . '/../_files/privatekey.pem']);
        try {
            $filter->decrypt('unknown');
            $this->fail();
        } catch (RuntimeException $e) {
            $this->assertStringContainsString('Please give an envelope key', $e->getMessage());
        }

        $filter->setEnvelopeKey('unknown');
        try {
            $filter->decrypt('unknown');
            $this->fail();
        } catch (RuntimeException $e) {
            $this->assertStringContainsString('was not able to decrypt', $e->getMessage());
        }
    }

    public function testEncryptionWithoutPublicKey(): void
    {
        $filter = new OpensslEncryption();

        $this->expectException(Exception\RuntimeException::class);
        $this->expectExceptionMessage('without public key');
        $filter->encrypt('unknown');
    }

    public function testMultipleOptionsAtInitiation(): void
    {
        $passphrase = 'test';
        $filter     = new OpensslEncryption([
            'public'     => __DIR__ . '/../_files/publickey_pass.pem',
            'passphrase' => $passphrase,
            'private'    => __DIR__ . '/../_files/privatekey_pass.pem',
        ]);
        $public     = $filter->getPublicKey();
        $this->assertNotEmpty($public);
        $this->assertSame($passphrase, $filter->getPassphrase());
    }

    /**
     * Ensures that the filter allows de/encryption
     */
    public function testEncryptionWithDecryptionWithPackagedKeys(): void
    {
        $filter = new OpensslEncryption();
        $filter->setPublicKey(__DIR__ . '/../_files/publickey_pass.pem');
        $filter->setPackage(true);
        $output = $filter->encrypt('teststring');
        $this->assertNotEquals('teststring', $output);

        $phrase = 'test';
        $filter->setPrivateKey(__DIR__ . '/../_files/privatekey_pass.pem', $phrase);
        $input = $filter->decrypt($output);
        $this->assertSame('teststring', trim($input));
    }

    /**
     * Ensures that the filter allows de/encryption
     */
    public function testEncryptionWithDecryptionAndCompressionWithPackagedKeys(): void
    {
        if (! extension_loaded('bz2')) {
            $this->markTestSkipped('Bz2 extension for compression test needed');
        }

        $filter = new OpensslEncryption();
        $filter->setPublicKey(__DIR__ . '/../_files/publickey_pass.pem');
        $filter->setPackage(true);
        $filter->setCompression('bz2');
        $output = $filter->encrypt('teststring');
        $this->assertNotEquals('teststring', $output);

        $phrase = 'test';
        $filter->setPrivateKey(__DIR__ . '/../_files/privatekey_pass.pem', $phrase);
        $input = $filter->decrypt($output);
        $this->assertSame('teststring', trim($input));
    }

    public function testPassCompressionConfigWillBeUnsetCorrectly(): void
    {
        $filter = new OpensslEncryption([
            'compression' => 'bz2',
        ]);

        $r = new ReflectionProperty($filter, 'keys');
        $r->setAccessible(true);
        $this->assertArrayNotHasKey('compression', $r->getValue($filter));
    }
}
