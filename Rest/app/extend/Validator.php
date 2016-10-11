<?php
namespace App\Extend;

class Validator extends \Windward\Extend\Validator
{
    
    public function isNotExixts($email, $vars = null)
    {
        $id = (int) $vars['id'];
        $model = $this->container->model('Demo');
        $count = $model->count('users',['email' => $email, '[neq]id' => $id]);
        return $count ? false : true;
    }
}
