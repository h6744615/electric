<?php

namespace App\Controllers;

class Demo extends Base
{
    public  $token_key = 'ww1225';
    private $model = null;
    
    public function __construct(\Windward\Core\Container $container)
    {
        parent::__construct($container);
        $this->model = $container->model('Demo');
    }
    
    public function indexAction()
    {     
        $sh = $this->request->getPost();
        $questions = $this->model->getTypes('id,name');
        if(   $questions ){
             //return $this->success($questions,'ok'); 
        }
        
        return $this->error('notHappy');
      
    }
    
    public function saveAction()
    {
        $post = $this->request->getPost();
        $errors = [];
        
        if ($this->model->validInput($post, $errors) === false) {
            return $this->error($errors);
        }
        
        $userid = $this->model->save($post);
        if ($userid === false) {
            return $this->error('save_failed');
        }
        
        return $this->success(['userid' => $userid],'save_succ');
    }
    
    public function haltAction()
    {
        $this->halt('db_error', 1, 'base');
    }
    
    public function errorAction()
    {
        $price = $this->request->getPost('price');
        if ($price < 100 || $price > 1000) {
            return $this->error('price_invalid',null, 100,1000);
        } else {
            return $this->success();
        }
    }
    
    public function customuriAction()
    {
        return $this->success(['uri' => 'this is custom uri']);
    }
    /*
    * 验证用户token
    *
    *
    * */

    public function  checkToken($token)
    {
        if (!$token) {
            return $msg = ['result' => 0, 'code' => '请传入token'];
        }
        if (!$this->redis->get('token' . md5($token))) {
            return $msg = ['result' => 0, 'code' => 'token失效,请重新登录'];
        }

        $worker_num = $this->decodeToken($token);

        $user_info = $this->model->getUserInfo($worker_num);

        if (!$user_info['user_id']) {
            $msg = ['result' => 0, 'code' => 'token不正确,请重新登录'];
        } else {
            $msg = ['result' => 1, 'data' => $user_info];
        }
        return $msg;


    }

    /*
     *
     *
     *
     * */
    public function createToken($data, $key)
    {
        $map = md5($key);
        $x = 0;
        $char = '';
        $str = '';
        $len = strlen($data);
        $l = strlen($map);

        for ($i = 0; $i < $len; $i++) {
            if ($x == $l) {
                $x = 0;
            }
            $char .= $map{$x};

            $x++;
        }

        for ($i = 0; $i < $len; $i++) {
            $asc = (ord($data{$i}) + (ord($char{$i}) + 3));
            $str .= chr($asc);
        }
        return base64_encode($str);
    }

    /*
     * token 解碼
     *@param $token
     *
     * */
    function decodeToken($token)
    {

        $key = $this->token_key;
        $map = md5($key);  //加密字典
        $x = 0;
        $data = base64_decode($token);
        $len = strlen($data);
        $l = strlen($key);
        $char = '';
        $str = '';

        for ($i = 0; $i < $len; $i++) {
            if ($x == $l) {
                $x = 0;
            }
            $char .= $map[$i];
            $x++;
        }

        for ($i = 0; $i < $len; $i++) {
            $asc = ord($data[$i]) - ord($map[$i]) - 3;
            $str .= chr($asc);
        }
        return $str;
    }
}
