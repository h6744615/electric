<?php

namespace Windward\Lib;

class Aes
{

    private $cipher;
    private $padding;
    private $dataWithIv;

    public function __construct($padding = false, $dataWithIv = false)
    {
        $this->cipher = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '', MCRYPT_MODE_CBC, '');
        $this->padding = $padding;
        $this->dataWithIv = $dataWithIv;
    }

    public function encrypt($data, $key, $iv = '')
    {
        if ($this->dataWithIv && !$iv) {
            $iv = openssl_random_pseudo_bytes(16);
        }
        if (is_array($data)) {
            foreach ($data as $k => $v) {
                $data[$k] = $this->encrypt($v, $key);
            }
        } elseif (strlen($data)) {
            if ($this->padding) {
                $pad = 16 - (strlen($data) % 16);
                $data =  $data . str_repeat(chr($pad), $pad);
            }
            if (mcrypt_generic_init($this->cipher, $key, $iv) != -1) {
                $cipherText = mcrypt_generic($this->cipher, $data);
                mcrypt_generic_deinit($this->cipher);
                if ($this->dataWithIv) {
                    $cipherText = $iv . $cipherText;
                }
                return base64_encode($cipherText);
            } else {
                die('mcrypt init error');
            }
        }

        return $data;
    }

    public function decrypt($data, $key, $iv = '')
    {
        if (is_array($data)) {
            foreach ($data as $k => $v) {
                $data[$k] = $this->decrypt($v, $key);
            }
        } elseif (strlen($data)) {
            $data = base64_decode($data);
            if ($this->dataWithIv) {
                $iv = substr($data, 0, 16);
                $data = substr($data, 16);
            }
            if (mcrypt_generic_init($this->cipher, $key, $iv) != -1) {
                $data = mdecrypt_generic($this->cipher, $data);
                mcrypt_generic_deinit($this->cipher);
                if ($this->padding) {
                    $padLength = ord(substr($data, -1));
                    $data = substr($data, 0, -$padLength);
                }
                return trim($data);
            } else {
                die('mcrypt init error');
            }
        }

        return $data;
    }
}
