<?php
use lntn\Epay\EpayClient;
use lntn\Epay\Responses;

class EpayClientTest extends TestCase
{
    public function testLogin()
    {
        /** @var EpayClient $client */
        $client = $this->createClient();

        /**
         * @var Responses\LoginResponse $response
         */
        $response = $client->login($this->password);
        $this->assertEquals(1, $response->getStatus(), 'Response status [' . $response->getStatus() . ']: ' . $response->getMessage());
        $this->assertNotEmpty($response->getSessionId(), 'Session ID is empty');

        return $client;
    }

    /**
     * @depends testLogin
     * @param EpayClient $client
     */
    public function testLogout($client)
    {
        /** @var Responses\EpayResponse $response */
        $response = $client->logout();
        $this->assertEquals(1, $response->getStatus(), 'Response status [' . $response->getStatus() . ']: ' . $response->getMessage());
    }

    /**
     * @dataProvider cardDataProvider
     * @param string $serial
     * @param string $pin
     * @param string $provider
     * @param integer $status
     * @param decimal $responseValue
     */
    public function testChargeCard($serial, $pin, $provider, $status, $responseValue)
    {
        /** @var EpayClient $client */
        $client = $this->createClient();

        /**
         * @var Responses\LoginResponse $response
         */
        $client->login($this->password);

        if ($status != 1) {
            $this->expectException(\Epay\Exceptions\EpayException::class);
        }

        $transactionID = date('Ymdhis');
        $target = 'phpunit';

        $response = $client->chargeCard($transactionID, $target, $serial, $pin, $provider);
        $statusCode = $response->getStatus();
        $this->assertEquals($status, $statusCode);
        if ($status == 1) {
            $this->assertEquals($responseValue, $response->getResponseAmount());
        }

        $client->logout();
    }

    public function cardDataProvider()
    {
        return [
            ['12345678912', '123456789', 'VNP', 1, 100000.0],
            ['12345678912', '123456798', 'VNP', 10, null],
            ['12345678922', '113456789', 'VMS', 1, 10000.00],
            ['PM0050618157', '729521148536', 'VTC', 1, 10000],
            ['12345678942', '113355789', 'FPT', 1, 10000],
            ['12345678953', '123456799', 'VNP', -1, null],
            ['12345678964', '123456899', 'VNP', 4, null],
            ['12345678983', '123451099', 'VNP', 11, null],
            ['12345678993', '123451199', 'VNP', 9, null],
            ['12345678903', '123451299', 'VNP', -10, null],
            ['12345678913', '123451399', 'VMS', -3, null],
            ['12345678911', '12345678911123', 'VTT', 1, 10000],
            ['12345688911', '12345678912123', 'MGC', 1, 10000]
        ];
    }
}
