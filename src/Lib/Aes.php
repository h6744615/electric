<?php

namespace Windward\Lib;

class Aes extends \Windward\Core\Base
{

    private $cipher;
    
    public function __construct(\Windward\Core\Container $container)
    {
        parent::__construct($container);
        $this->cipher = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '', MCRYPT_MODE_CBC, '');
    }

    public function encrypt($data, $key, $iv)
    {
        if (is_array($data)) {
            foreach ($data as $k => $v) {
                $data[$k] = $this->encrypt($v, $key);
            }
        } else if (strlen($data)) {
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
        } else if (strlen($data)) {
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
