<?php
namespace Windward\Mvc;

use Windward\Core\Container;
use Windward\Core\Language;

class Controller extends \Windward\Core\Base
{
    
    protected $container;
    private $language = null;
    
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function getLanguage()
    {
        if (!is_null($this->language)) {
            return $this->language;
        }
        return $this->language = new Language('controller');
    }

    public function error404Action()
    {
        $this->view->display('404');
    }
    
    function redirect($url, $js = false, $msg = null, $delay = 0)
    {
        if ($js) {
            $output = '<html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8">';
            $output.= '<script type="text/javascript">';
            $output.= "alert('{$msg}');";
            $output.= "document.location='{$url}';";
            $output.= '</script>';
            $output.= '</head></html>';
            exit($output);
        } else if (headers_sent()) {
            $output = '<html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8">';
            $output.= "<meta http-equiv=\"refresh\" content=\"{$delay};URL={$url}\" />";
            $output.= '</head></html>';
            exit($output);
        } else {
            header("Location: {$url}");
            exit();
        }
    }
}
