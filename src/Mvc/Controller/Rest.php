<?php

namespace Windward\Mvc\Controller;

use Windward\Core\Response\Json as JsonResponse;
use Windward\Core\Response\Plain as PlainResponse;

class Rest extends \Windward\Mvc\Controller
{
    
    private $output;
    
    public function afterHandle(&$response)
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
                'Files:',
                $_FILES,
                'Output:',
                $response->output(true)
            );
        }
    }
    
    public function beforeHandle()
    {

    }

    public function needEncrypt()
    {
        return true;
    }

    public function error($key, $className = null)
    {
        $vars = array_slice(func_get_args(), 2);
        if (is_null($className)) {
            $className = (new \ReflectionClass($this))->getShortName();
        }
        $error = array();
        if ($key) {
            if (is_null($className)) {
                $className = (new \ReflectionClass($this))->getShortName();
            }
            $iKey = 'controller.' . strtolower($className) . '.' . $key;
            $error = $this->getLanguage()->error($iKey, $vars);
        }
        $result = array(
            'status' => '0',
            'code' => $error['code'],
            'msg' => $error['msg'] ?: $key,
            'data' => new \stdClass,
            'need_relogin' => 0,
        );
        $response = new JsonResponse($this->container);
        $response->setPayload($result);
        $this->afterHandle($response);
        $response->output();
        die();
    }

    public function halt($key, $needLogin = 1, $className = null)
    {
        $error = array();
        if ($key) {
            if (is_null($className)) {
                $className = (new \ReflectionClass($this))->getShortName();
            }
            $iKey = 'controller.' . strtolower($className) . '.' . $key;
            $error = $this->getLanguage()->error($iKey);
        }
        $result = array(
            'status' => '0',
            'code' => $error['code'],
            'msg' => $error['msg'] ?: $key,
            'data' => new \stdClass,
            'need_relogin' => $needLogin,
        );
        $response = new JsonResponse($this->container);
        $response->setPayload($result);
        $this->afterHandle($response);
        $response->output();
        die();
    }

    public function success($data = null, $key = null, $className = null, $needLogin = 0)
    {
        $msg = $key;
        if ($key) {
            if (is_null($className)) {
                $className = (new \ReflectionClass($this))->getShortName();
            }
            $iKey = 'controller.' . strtolower($className) . '.' . $key;
            $msg = $this->getLanguage()->info($iKey);
        }
        
        $result = array(
            'status' => '1',
            'code' => 0,
            'msg' => $msg,
            'data' => $data ? $data : new \stdClass(),
            'need_relogin' => $needLogin,
        );
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
            'APP_ROLE' => $this->request->getServer('HTTP_APP_ROLE'),
            'CONTENT_TYPE' => $this->request->getServer('HTTP_CONTENT_TYPE'),
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
    
    
    
    /*
     * 记录api访问日志到平台系统
     */
    public function logApi($platformUrl, $platformId)
    {
        if (!$this->request) {
            return true;
        }
        
        $curl = new \Windward\Lib\Curl($this->container);
        $curl->setUrl($platformUrl . '/service/log');
        
        $post = array(
            'platform' => $platformId,
            'uri' => $this->request->getSchemaHost() . $this->request->getServer('REQUEST_URI'),
            'get' => $this->request->getQuery(),
            'post' => $this->request->getPost(),
            'header' => $this->httpHeaders(),
            'access_time' => time(),
        );
        $curl->setPostDatas($post);
        $curl->request(true);
    }
}
