<?php

/**
 * 通过json生成页面
 */
namespace Windward\Core\Response;

use Windward\Core\Base;
use Windward\Core\Container;

class Json extends \Windward\Core\Response {
    
    private $payload = array();
    
    /**
     * 传入container
     * 
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        parent::__construct($container);
    }
    
    /**
     * 对模板赋值
     * 
     * @param string $name 名称
     * @param mixed $value 值
     */
    public function set($name, $value)
    {
        $this->payload[$name] = $value;
        return $this;
    }
    
    /**
     * 整体赋值
     * 
     * @param string $payload 值
     */
    public function setPayload($payload)
    {
        $this->payload = $payload;
        return $this;
    }
    
    /**
     * 输出模板或返回模板内容
     * 
     * @param bool $return 是否返回
     * @return string|无
     */
    public function output($return = false)
    {
        $content = json_encode($this->payload);
        if ($return ) {
            return $content;
        }
        header('Content-Type: application/json;charset=utf-8');
        echo $content;
    }
}