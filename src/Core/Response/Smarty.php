<?php

/**
 * 通过Smarty生成页面
 */

namespace Windward\Core\Response;

use Windward\Core\Base;
use Windward\Core\Container;

class Smarty extends \Windward\Core\Response {

    private $view;
    private $tpl = '';
    
    /**
     * 传入container
     * 
     * @param Container $container
     */
    public function __construct(Container $container) {
        parent::__construct($container);
        $this->view = $this->container->view;
    }

    /**
     * 对模板赋值
     * 
     * @param string $name 名称
     * @param mixed $value 值
     */
    public function set($name, $value) {
        $this->view->assign($name, $value);
        return $this;
    }

    /**
     * 输出模板或返回模板内容
     * 
     * @param bool $return 是否返回
     * @return string|无
     */
    public function output($return = false) {
        if ($return) {
            return $this->view->fetch();
        }
        header('Content-Type: text/html;charset=utf-8');
        $this->view->display($this->tpl ? $this->tpl : null);
        $this->tpl ? $this->setTpl() : null;
    }
    
    public function setTpl($tpl = '') {
        $this->tpl = $tpl;
    }

}
