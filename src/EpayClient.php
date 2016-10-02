<?php
namespace Epay;

use Epay\Exceptions\AuthenticationException;
use Epay\Exceptions\CryptographyException;
use Epay\Exceptions\EpayException;
use Epay\Responses\ChargingResponse;
use Epay\Responses\EpayResponse;
use Epay\Responses\LoginResponse;
use Exception;
use SoapClient;

class EpayClient
{
    /**
     * Default web service values.
     * Can be replaced by options array in constructor
     */
    private $partnerID;
    private $partnerCode;
    private $MPIN;
    private $options = [
        'WS_URL'          => 'http://charging-test.megapay.net.vn:10001/CardChargingGW_V2.0/services/Services?wsdl',
        'WS_URI'          => 'http://113.161.78.134/VNPTEPAY/',
        'EPAY_PUBLIC_KEY' => '../key/Epay_Public_key.pem',
        'PRIVATE_KEY'     => '../key/private_key.pem',
    ];
    private $soapClient;
    private $username;

    /* In Hex, call HexToByte to use it as cryptography key */
    private $sessionID;

    private static $providers = [
        'VNP' => 'Vinaphone',
        'VMS' => 'Mobifone',
        'VTT' => 'Viettel',
        'FPT' => 'FPT',
        'ZING' => 'Zing',
        'ONC' => 'Oncash',
        'MGC' => 'Megacard',
        'VNM' => 'VietNamMobile'
    ];

    /**
     * @return mixed
     */
    public function getSessionID()
    {
        return $this->sessionID;
    }

    /**
     * @param mixed $sessionID
     */
    public function setSessionID($sessionID)
    {
        $this->sessionID = $sessionID;
    }

    /**
     * @return mixed
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @param mixed $options
     */
    public function setOptions($options)
    {
        $this->options = $options;
    }

    /**
     * @return array
     */
    public static function getProviders()
    {
        return self::$providers;
    }

    /**
     * EpayClient constructor.
     * @param string $username provided by EPAY
     * @param string $partnerID provided by EPAY
     * @param string $partnerCode provided by EPAY
     * @param string $MPIN provided by EPAY
     * @param array $options WS_URL, WS_URI, EPAY_PUBLIC_KEY, PRIVATE_KEY
     */
    public function __construct($username, $partnerID, $partnerCode, $MPIN, $options = [])
    {
        $this->username = $username;
        $this->partnerID = $partnerID;
        $this->partnerCode = $partnerCode;
        $this->MPIN = $MPIN;

        if (!empty($options)) {
            if (isset($options['WS_URL']))
                $this->options['WS_URL'] = $options['WS_URL'];
            if (isset($options['WS_URI']))
                $this->options['WS_URI'] = $options['WS_URI'];
            if (isset($options['EPAY_PUBLIC_KEY']))
                $this->options['EPAY_PUBLIC_KEY'] = $options['EPAY_PUBLIC_KEY'];
            if (isset($options['PRIVATE_KEY']))
                $this->options['PRIVATE_KEY'] = $options['PRIVATE_KEY'];
        }

        ini_set('default_socket_timeout', 60);
//        ini_set("soap.wsdl_cache_enabled", 0);

        $this->soapClient = new SoapClient(null, [
            'location'           => $this->options['WS_URL'],
            'uri'                => $this->options['WS_URI'],
            'connection_timeout' => 100,
            'keep_alive'         => false,
//            'cache_wsdl' => WSDL_CACHE_NONE,
        ]);
    }

    /**
     * @param $password
     * @return LoginResponse
     * @throws CryptographyException
     * @throws \SoapFault
     * @throws EpayException
     */
    public function login($password)
    {
        $RSAClass = new Crypto();

        // Retrieve EPAY public key from file
        $RSAClass->GetPublicKeyFromPemFile($this->options['EPAY_PUBLIC_KEY']);

        try {
            $encryptedPass = $RSAClass->encrypt($password);
        } catch (Exception $ex) {
            throw new CryptographyException($ex->getMessage(), $ex->getCode(), $ex->getPrevious());
        }

        $pass = base64_encode($encryptedPass);
        /**
         * @var object $result
         * @throws \SoapFault
         */
        $result = $this->soapClient->login($this->username, $pass, $this->partnerID);
        $this->soapClient->httpsocket = null;
        if ($result->status != 1) {
            throw new EpayException($result);
        }

        $response = new LoginResponse($result->status, $result->message);
        $response->setTransId($result->transid);

        // Retrieve private key from file
        $RSAClass->GetPrivateKeyFromPemFile($this->options['PRIVATE_KEY']);
        try {
            $decryptedSession = $RSAClass->decrypt(base64_decode($result->sessionid));
        } catch (Exception $ex) {
            throw new CryptographyException($ex->getMessage(), $ex->getCode(), $ex->getPrevious());
        }

        /* Session ID is in Hex */
        $response->setSessionId($decryptedSession);

        $this->sessionID = $response->getSessionId();

        return $response;
    }

    public function logout()
    {
        /** @var object $result */
        $result = $this->soapClient->logout($this->username, $this->partnerID, md5($this->sessionID));
        $this->soapClient->httpsocket = null;
        if ($result->status == 3 || $result->status == 7) {
            throw new AuthenticationException($result);
        }
        elseif ($result->status != 1) {
            throw new EpayException($result);
        }

        return new EpayResponse($result->status, $result->message);
    }

    /**
     * @param $transactionID
     * @param $target
     * @param $cardSerial
     * @param $cardPin
     * @param $cardProvider
     * @return ChargingResponse
     * @throws AuthenticationException
     * @throws CryptographyException
     * @throws EpayException
     * @throws Exception
     */
    public function chargeCard($transactionID, $target, $cardSerial, $cardPin, $cardProvider)
    {
        $cardData = $cardSerial . ":" . $cardPin . ":" . "0" . ":" . $cardProvider;

        /* add partner code to prevent duplication */
        $transactionID = $this->partnerCode . $transactionID;

        if (empty($this->sessionID)) {
            throw new Exception('Please call login first.');
        }

        $tripDes = new TripDes($this->HexToByte($this->sessionID));
        try {
            $MPIN = bin2hex($tripDes->encrypt($this->MPIN));
            $cardData = bin2hex($tripDes->encrypt($cardData));
        } catch (Exception $ex) {
            throw new CryptographyException($ex->getMessage(), $ex->getCode(), $ex->getPrevious());
        }

        /** @var object $result */
        $result = $this->soapClient->cardCharging($transactionID, $this->username, $this->partnerID, $MPIN, $target, $cardData, md5($this->sessionID));
        $this->soapClient->httpsocket = null;

        if ($result->status == 3 || $result->status == 7) {
            throw new AuthenticationException($result);
        }
        elseif ($result->status != 1) {
            throw new EpayException($result);
        }

        $response = new ChargingResponse($result->status, $result->message, $result->transid, $result->amount);
        $response->setResponseAmount($tripDes->decrypt($this->HexToByte($result->responseamount)));

        return $response;
    }

    private function HexToByte($hex)
    {
        $string = '';
        for ($i = 0; $i < strlen($hex) - 1; $i += 2) {
            $string .= chr(hexdec($hex[$i] . $hex[$i + 1]));
        }

        return $string;
    }
}
