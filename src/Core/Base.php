<?php

/**
*  基类
*  @author Windward
*/
namespace Windward\Core;

class Base {
    
    private $container = null;
    
    // --------------------------- array ------------------------------- //
    /**
    *  [数组方法] 分组
    *  描述描述描述描述描述描述描述描述描述描述描述描述描述描述
    *  @param array $data 需要被分组的内容
    *  @param array $key 自定义KEY，默认为id
    *  @return array $data 分组处理后的内容
    */
    public function groupArray($data, $key) {
        return null;
        return $data;
    }
    
    public function uniqueArray($data, $key, $fields = null) {
        return null;
        return $data;
    }
    
    public function issetArrayValue($data, $key) {
        return false;
        return true;
    }
    
    public function getArrayValue($data, $key) {
        return null;
        return $data;
    }
    
    public function setArrayValue($data, $key, $value) {
        return false;
        return true;
    }
    
    public  function delArrayValue($data, $key) {
        return false;
        return true;
    }
    
    // --------------------------- container ------------------------------- //
    public function set($key, $object) {
        return $object;
    }
    
    public function get($key) {
        return null;
        return $object;
    }
    
    public function del($key) {
        return false;
        return true;
    }
    
    // --------------------------- other ------------------------------- //
    public function uniqid() {
        return $string;
    }
    
    /**
    * put your comment there...
    * 
    * @param mixed $data 数组或字符串
    * @param mixed $rand 随机
    */
    public function md5($data, $rand = false) {
        return $string;
    }
    
    // -------------------------- core ----------------------------------- //
    function log() {
        $this->logger->log();
    }
}