<?php
namespace Windward\Mvc;

use Windward\Core\Container;

Class View Extends \Smarty {
    
    private $container;

    public $templateExtension = '.phtml';

    public function __construct($templateDir, $othersBaseDir)
    {
        parent::__construct();
        $this->setTemplateDir($templateDir);

        $umask = umask(0);
        $compileDir = $othersBaseDir . '/compile';
        if (!is_dir($compileDir)) {
            mkdir($compileDir, 0777, true);
        }
        $this->setCompileDir($compileDir);

        $configDir = $othersBaseDir . '/config';
        if (!is_dir($configDir)) {
            mkdir($configDir, 0777, true);
        }
        $this->setConfigDir($configDir);

        $cacheDir = $othersBaseDir . '/cache';
        if (!is_dir($cacheDir)) {
            mkdir($cacheDir, 0777, true);
        }
        $this->setCacheDir($cacheDir);
        $this->left_delimiter = '<{';
        $this->right_delimiter = '}>';

        $this->addPluginsDir(__DIR__ . '/View/Smarty/Plugins');
        $this->addTemplateDir(__DIR__ . '/View/Smarty/Templates');
        
        umask($umask);
    }

    public function setContainer(Container $container)
    {
        $this->container = $container;
    }

    public function display($template = null, $cacheId = null, $compileId = null, $parent = null)
    {
        if (is_null($template) && $this->container instanceof Container) {
            $template = ucfirst($this->container->request->getNormalizedUri());
        }
        $template .= $this->templateExtension;
        parent::display($template, $cacheId, $compileId, $parent);
    }

    public function fetch($template = null, $cacheId = null, $compileId = null, $parent = null)
    {
        if (is_null($template) && $this->container instanceof Container) {
            $template = $this->container->request->getNormalizedUri();
        }
        $template .= $this->templateExtension;
        return parent::fetch($template, $cacheId, $compileId, $parent);
    }
}