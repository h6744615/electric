<?php

namespace App\Controllers;

class Demo extends Base
{
    private $model = null;
    
    public function __construct(\Windward\Core\Container $container)
    {
        parent::__construct($container);
        $this->model = $container->model('Demo');
    }
    
    public function indexAction()
    {       
        $sh = $this->request->getPost();
        $questions = $this->model->questionList($sh);
        return $this->success($questions);
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
}
