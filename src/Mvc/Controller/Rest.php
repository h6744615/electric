<?php
namespace Windward\Mvc\Controller;

use Windward\Core\Container;
use Windward\Core\Response\Json as JsonResponse;

Class Rest extends \Windward\Mvc\Controller {
    
    public function error($key)
    {
        $key = 'controller.' . strtolower((new \ReflectionClass($this))->getShortName()) . '.' . $key;
        $error = $this->getLanguage()->error($key);
        $result = array(
            'status' => 'NG',
            'code' => $error['code'],
            'msg' => $error['msg'],
            'data' => new \stdClass,
            'need_login' => 0,
        );
        $response = new JsonResponse($this->container);
        return $response->setPayload($result);
    }

    public function success($data, $msg = '')
    {
        $result = array(
            'status' => 'OK',
            'code' => 0,
            'msg' => $msg,
            'data' => $data,
            'need_login' => 0,
        );
        $response = new JsonResponse($this->container);
        return $response->setPayload($result);
    }
}