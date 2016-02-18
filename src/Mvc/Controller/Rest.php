<?php

namespace Windward\Mvc\Controller;

use Windward\Core\Container;
use Windward\Core\Response\Json as JsonResponse;

Class Rest extends \Windward\Mvc\Controller {

    public function error($key) {
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
    
    public function halt($key,$needLogin = 1) {
        $key = 'controller.' . strtolower((new \ReflectionClass($this))->getShortName()) . '.' . $key;
        $error = $this->getLanguage()->error($key);
        $result = array(
            'status' => 'NG',
            'code' => $error['code'],
            'msg' => $error['msg'],
            'data' => new \stdClass,
            'need_login' => $needLogin,
        );
        $response = new JsonResponse($this->container);
        $response->setPayload($result);
        $response->output();
        die();
    }

    public function success($data = null, $msg = '') {
        $result = array(
            'status' => 'OK',
            'code' => 0,
            'msg' => $msg,
            'data' => $data ? $data : new \stdClass(),
            'need_login' => 0,
        );
        $response = new JsonResponse($this->container);
        return $response->setPayload($result);
    }

    public function httpHeaders() {
        $header = array(
            'APP_VERSION' => $this->request->getServer('HTTP_APP_VERSION'),
            'DEVICE_UUID' => $this->request->getServer('HTTP_DEVICE_UUID'),
            'DEVICE_MODEL' => $this->request->getServer('HTTP_DEVICE_MODEL'),
            'DEVICE_VERSION' => $this->request->getServer('HTTP_DEVICE_VERSION'),
            'DEVICE_TOKEN' => $this->request->getServer('HTTP_DEVICE_TOKEN'),
            'APP_TOKEN' => $this->request->getServer('HTTP_APP_TOKEN'),
        );

        return $header;
    }

}
