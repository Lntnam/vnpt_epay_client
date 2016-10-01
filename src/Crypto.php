<?php


namespace Epay;


class Crypto
{
    private $RsaPublicKey;
    private $RsaPrivateKey;

    public function GetPublicKeyFromCertFile($filePath)
    {
        $fp = fopen($filePath, "r");
        $pub_key = fread($fp, filesize($filePath));
        fclose($fp);
        openssl_get_publickey($pub_key);

        $this->RsaPublicKey = $pub_key;
    }

    public function GetPublicKeyFromPemFile($filePath)
    {
        $fp = fopen($filePath, "r");
        $pub_key = fread($fp, filesize($filePath));
        fclose($fp);
        openssl_get_publickey($pub_key);
        $this->RsaPublicKey = $pub_key;
    }

    public function GetPrivateKeyFromPemFile($filePath)
    {
        $fp = fopen($filePath, "r");
        $pub_key = fread($fp, filesize($filePath));
        fclose($fp);
        $this->RsaPrivateKey = $pub_key;
    }

    public function GetPrivate_Public_KeyFromPfxFile($filePath, $PassPhrase)
    {
        $p12cert = [];
        $fp = fopen($filePath, "r");
        $p12buf = fread($fp, filesize($filePath));
        fclose($fp);
        openssl_pkcs12_read($p12buf, $p12cert, $PassPhrase);
        $this->RsaPrivateKey = $p12cert['pkey'];
        $this->RsaPublicKey = $p12cert['cert'];
    }

    function encrypt($source)
    {
        //path holds the certificate path present in the system
        $j = 0;
        $x = strlen($source) / 10;
        $y = floor($x);
        $crt = '';
        for ($i = 0; $i < $y; $i++) {
            $cryptText = '';

            openssl_public_encrypt(substr($source, $j, 10), $cryptText, $this->RsaPublicKey);
            $j = $j + 10;
            $crt .= $cryptText;
            $crt .= ":::";
        }
        if ((strlen($source) % 10) > 0) {
            openssl_public_encrypt(substr($source, $j), $cryptText, $this->RsaPublicKey);
            $crt .= $cryptText;
        }

        return ($crt);
    }

    function decrypt($cryptText)
    {
        $tt = explode(":::", $cryptText);
        $cnt = count($tt);
        $i = 0;
        $str = '';
        while ($i < $cnt) {
            openssl_private_decrypt($tt[$i], $str1, $this->RsaPrivateKey);
            $str .= $str1;
            $i++;
        }

        return $str;
    }
}
