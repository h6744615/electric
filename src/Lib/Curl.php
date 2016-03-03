<?php

namespace Windward\Lib;

class Curl extends \Windward\Core\Base {
    private $headers = array();
    private $postDatas = array();
    private $url = '';
    
    public function setUrl($url) {
        $this->url = $url;
    }
    
    public function setHeaders($headers) {
        $tmp = array();
        foreach ($headers as $key => $val) {
            $tmp[] = "{$key}:{$val}";
        }
        $this->headers = $tmp;
    }
    
    public function setPostDatas($postDatas = null) {
        $postDatas ? $this->postDatas = $postDatas : null;
    }
    
    public function request() {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        
        curl_setopt($ch, CURLOPT_HEADER, 0);
        if ($this->headers) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $this->headers);
        }
        
        curl_setopt($ch, CURLOPT_POST, 1);
        if ($this->postDatas) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $this->postDatas);
        }
        
        $output = curl_exec($ch);
        curl_close($ch);
        
        return $output;
    }
}