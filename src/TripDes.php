<?php


namespace lntn\Epay;


class TripDes
{
    private $desKey;

    public function __construct($key)
    {
        $this->desKey = $key;
    }

    public function decrypt($text)
    {
        $key = $this->desKey;
        $size = mcrypt_get_iv_size(MCRYPT_3DES, MCRYPT_MODE_ECB);
        $iv = mcrypt_create_iv($size, MCRYPT_RAND);
        $decrypted = mcrypt_decrypt(MCRYPT_3DES, $key, $text, MCRYPT_MODE_ECB, $iv);

        return rtrim($this->pkcs5_unpad($decrypted));
    }

    public function encrypt($text)
    {
        $key = $this->desKey;
        $text = $this->pkcs5_pad($text, 8);  // AES?16????????
        $size = mcrypt_get_iv_size(MCRYPT_3DES, MCRYPT_MODE_ECB);
        $iv = mcrypt_create_iv($size, MCRYPT_RAND);
        $bin = pack('H*', bin2hex($text));
        $encrypted = mcrypt_encrypt(MCRYPT_3DES, $key, $bin, MCRYPT_MODE_ECB, $iv);

        return $encrypted;
    }

    function pkcs5_pad($text, $blockSize)
    {
        $pad = $blockSize - (strlen($text) % $blockSize);

        return $text . str_repeat(chr($pad), $pad);
    }

    function pkcs5_unpad($text)
    {
        $pad = ord($text{strlen($text) - 1});
        if ($pad > strlen($text))
            return false;
        if (strspn($text, chr($pad), strlen($text) - $pad) != $pad)
            return false;

        return substr($text, 0, -1 * $pad);
    }
}
