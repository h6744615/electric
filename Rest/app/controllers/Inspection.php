<?php

namespace App\Controllers;


class Inspection extends Demo
{
    private $model = null;

    public function __construct(\Windward\Core\Container $container)
    {
        parent::__construct($container);
        $this->model = $container->model('Demo');
    }

    /*
     * 线路列表
     *
     *
     * */
    public function indexAction()
    {
        $post = $this->request->getPost();
        $token = $post['token'];
        $res = $this->checkToken($token);
        if ($res['result']) {
            return $this->success('ok');
        }
        return $this->error($res['code']);
    }

    /*
     * 新建巡视线路
     *
     *
     * */

    public function  createInspectionTaskAction()
    {
        $post = $this->request->getPost();
        $token = $post['token'];
        $res = $this->checkToken( $token);
        if (!$res['result']) {
            return $this->error($res['code']);
        }
        $group_id = $post['group_id'] ? $post['group_id'] :$res['data']['group_id'] ;
        if($group_id){
            $staff = $this->model->get('user','id,user_name',['group_id'=>$group_id,'is_busy'=>0],false);
        }
          print_r($staff) ;die ;
        if($post['g']){


        }
        if($res['group_id']){

            $this->model->get('user','*',['le'],false);

        }
        $type = $this->model->getSetting('inspection_type',true);
        $cate = $this->model->getSetting('inspection_cate',true);
        $group = $this->model->get('user_group','id,group_name,master_id',[],false);
        $staff = $this->model->get('user','id,user_name',[''],false);
        $data = compact('type','cate','group','staff');

        return $this->success($data);
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
