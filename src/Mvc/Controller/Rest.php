<?php

namespace Windward\Mvc\Controller;

use Windward\Core\Response\Json as JsonResponse;
use Windward\Core\Response\Plain as PlainResponse;

class Rest extends \Windward\Mvc\Controller
{
    
    private $output;
    
    public function afterHandle()
    {
        if ($this->logger) {
            $this->logger->log(
                'api',
                'URL:',
                $this->request->getServer('REQUEST_URI'),
                'Header:',
                $this->httpHeaders(),
                'Post:',
                $this->request->getPost(),
                'Output:',
                $this->getOutput()
            );
        }
    }
    
    public function setOutput(&$output)
    {
        $this->output = $output;
    }
    
    public function getOutput()
    {
        return $this->output;
    }

    public function error($key, $className = null)
    {
        if (is_null($className)) {
            $className = (new \ReflectionClass($this))->getShortName();
        }
        $key = 'controller.' . strtolower($className) . '.' . $key;
        $error = $this->getLanguage()->error($key);
        $result = array(
            'status' => '0',
            'code' => $error['code'],
            'msg' => $error['msg'] ?: $key,
            'data' => new \stdClass,
            'need_relogin' => 0,
        );
        \Windward\Extend\Util::stringValues($result);
        $this->setOutput($result);
        $response = new JsonResponse($this->container);
        return $response->setPayload($result);
    }

    public function halt($key, $needLogin = 1, $className = null)
    {
        if (is_null($className)) {
            $className = (new \ReflectionClass($this))->getShortName();
        }
        $key = 'controller.' . strtolower($className) . '.' . $key;
        $error = $this->getLanguage()->error($key);
        $result = array(
            'status' => '0',
            'code' => $error['code'],
            'msg' => $error['msg'] ?: $key,
            'data' => new \stdClass,
            'need_relogin' => $needLogin,
        );
        \Windward\Extend\Util::stringValues($result);
        $this->setOutput($result);
        $response = new JsonResponse($this->container);
        $response->setPayload($result);
        $response->output();
        die();
    }

    public function success($data = null, $key = null, $className = null, $needLogin = 0)
    {
        $info = array();
        if ($key) {
            if (is_null($className)) {
                $className = (new \ReflectionClass($this))->getShortName();
            }
            $key = 'controller.' . strtolower($className) . '.' . $key;
            $info = $this->getLanguage()->info($key);
        }
        
        $result = array(
            'status' => '1',
            'code' => 0,
            'msg' => isset($info['msg']) ? $info['msg'] : '',
            'data' => $data ? $data : new \stdClass(),
            'need_relogin' => $needLogin,
        );
        \Windward\Extend\Util::stringValues($result);
        $this->setOutput($result);
        $response = new JsonResponse($this->container);
        return $response->setPayload($result);
    }

    public function plainSuccess($content)
    {
        $response = new PlainResponse($this->container);
        $response->setContentType(PlainResponse::CONTENT_TYPE_JSON);
        return $response->setContent($content);
    }

    public function httpHeaders()
    {
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

    public function error404Action()
    {
        $json = new \Windward\Core\Response\Json($this->container);
        $json->setPayload(array(
            'status' => '1',
            'code' => '404',
            'msg' => 'Not Found',
            'data' => new \stdClass,
            'need_relogin' => 0,
        ));
        $json->output();
    }
}
