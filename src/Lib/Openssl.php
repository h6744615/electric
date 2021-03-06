<?php

namespace Windward\Lib;

class Openssl
{

    private $publicKey = '';
    private $privateKey = '';

    public function getPublicKey()
    {
        return $this->publicKey;
    }

    public function getPrivateKey()
    {
        return $this->privateKey;
    }

    public function setPublicKey($pk = '')
    {
        $this->publicKey = $pk;
    }

    public function setPrivateKey($pk = '')
    {
        $this->privateKey = $pk;
    }

    public function initKeyByFile($privateKeyFile, $passphrase = '')
    {
        $this->initKey('file://' . $privateKeyFile, $passphrase);
    }

    public function initKeyByString($privateKeyString, $passphrase = '')
    {
        $this->initKey($privateKeyString, $passphrase);
    }

    public function initKey($privateKeyFile = '', $passphrase = '')
    {
        if (!$privateKeyFile) {
            return false;
        }
        
        $privateKey = '';
        $res = openssl_pkey_get_private($privateKeyFile, $passphrase);
        openssl_pkey_export($res, $privateKey);
        $this->privateKey = $privateKey;
        
        $tmp = openssl_pkey_get_details($res);
        $this->publicKey = $tmp['key'];
    }

    public function encrypt($data, $publicKey = '')
    {
        if (!$publicKey) {
            $publicKey = $this->publicKey;
        }
        $encrypt = '';
        openssl_public_encrypt($data, $encrypt, $publicKey);
        return base64_encode($encrypt);
    }

    public function decrypt($data, $privateKey = '')
    {
        if (!$privateKey) {
            $privateKey = $this->privateKey;
        }
        $decrypt = '';
        openssl_private_decrypt(base64_decode($data), $decrypt, $privateKey);
        return $decrypt;
    }
}
