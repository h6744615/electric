<?php

namespace App\Controllers;

use Base\Config\Pro\Redis;

class User extends Base
{
    private $model = null;


    public function __construct(\Windward\Core\Container $container)
    {
        parent::__construct($container);
        $this->model = $container->model('Demo');
    }

    /*
     * 用戶登錄
     *
     *
     *
     * */
    public function loginAction()
    {
        $post = $this->request->getPost();
        $info = $this->model->get('user', 'id,user_name,worker_num,pwd', $post);
        if ($info) {
            $token = $this->createToken($info['worker_num'], $this->token_key);
            if (!$token) {
                return $this->error('token 产生失败!');
            }
            $info['token'] = $token;
            $redis = new \Redis();
            $redis->connect('localhost',6379);
            $in_redis =  $redis->set('token'.md5($token),123,60*60*24);
            if(!$in_redis){
                return $this->error('redis 进库失败!');
            }
            return $this->success($token);
        }
        return $this->error('用户名或者密码不正确');

    }

    public function registerAction()
    {
        $post = $this->request->getPost();
        $salt = 'ww_electric';

        $token = md5($post['worker_num'] . $post['user_id'] . $salt);

        echo $token;
        die;
        // $this->model->get('user','id,user_name,pwd,');

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

        return $this->success(['userid' => $userid], 'save_succ');
    }

    public function haltAction()
    {
        $this->halt('db_error', 1, 'base');
    }

    public function errorAction()
    {
        $price = $this->request->getPost('price');
        if ($price < 100 || $price > 1000) {
            return $this->error('price_invalid', null, 100, 1000);
        } else {
            return $this->success();
        }
    }

    public function customuriAction()
    {
        return $this->success(['uri' => 'this is custom uri']);
    }
}
