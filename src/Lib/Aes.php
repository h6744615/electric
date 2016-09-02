<?php

namespace Windward\Lib;

class Aes
{

    private $cipher;
    
    public function __construct()
    {
        $this->cipher = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '', MCRYPT_MODE_CBC, '');
    }

    public function encrypt($data, $key, $iv)
    {
        if (is_array($data)) {
            foreach ($data as $k => $v) {
                $data[$k] = $this->encrypt($v, $key);
            }
        } elseif (strlen($data)) {
            //$pad = 16 - (strlen($data) % 16);
            //$data =  $data . str_repeat(chr($pad), $pad);
            if (mcrypt_generic_init($this->cipher, $key, $iv) != -1) {
                $cipherText = mcrypt_generic($this->cipher, $data);
                mcrypt_generic_deinit($this->cipher);
                return base64_encode($cipherText);
            } else {
                die('mcrypt init error');
            }
        }

        return $data;
    }

    public function decrypt($data, $key, $iv)
    {
        if (is_array($data)) {
            foreach ($data as $k => $v) {
                $data[$k] = $this->decrypt($v, $key);
            }
        } elseif (strlen($data)) {
            $data = base64_decode($data);

            if (mcrypt_generic_init($this->cipher, $key, $iv) != -1) {
                $data = mdecrypt_generic($this->cipher, $data);
                mcrypt_generic_deinit($this->cipher);
                return trim($data);
            } else {
                die('mcrypt init error');
            }
        }

        return $data;
    }
}
