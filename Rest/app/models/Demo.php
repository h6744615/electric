<?php

namespace App\Models;

class Demo extends Base
{
    public $token_key = 'ww1225';
    public function validInput($data, &$errors)
    {
        $config = [
            'name' => array(
                array('isNotNull', 'name_empty'),
            ),
            'email' => array(
                array('isNotNull', 'email_empty'),
                array('isNotExixts', 'email_is_exixts', ['id' => $data['id']]),
            ),
        ];

        $validator = new \App\Extend\Validator($this->container);
        if (!$result = $validator->validate($config, $data, false)) {
            $errors = $validator->error;
            $this->language->validator('demo', $errors);
        }

        return $result;
    }

    public function getTypes($fields = '*', $cond = [])
    {
        $types = $this->get('types', $fields, $cond, false);

        if ($types) {
            foreach ($types as $k => $v) {
                $data[$v['id']] = $v['name'];
            };
        };
        return $data;
    }

    public function save($data)
    {
        $id = (int)$data['id'];

        $this->begin();

        $id && ($one = $this->get('users', 'id', ['id' => $id], true, null, true));
        if ($id && !$one) {
            $this->rollback();
            return false;
        }

        $user = [
            'name' => $data['name'],
            'email' => $data['email'],
        ];
        if ($id) {
            if ($this->update('users', $user, ['id' => $id]) === false) {
                $this->rollback();
                return false;
            }
        } else {
            if (($id = $this->insert('users', $user)) === false) {
                $this->rollback();
                return false;
            }
        }

        $this->commit();
        return $id;
    }

    /*
     *
     * @description 根据账号获取用户信息
     * @status
     * @param string  $field查询字段
     * @param  string $table 制定表
     * @param array $cond 查询条件
     * @return array
     *
     * */
    public function  getUserInfo($worker_num)
    {
        if ($worker_num) {
            return $this->fetchOne('select u.id as user_id,u.user_name,g.id as group_id,g.group_name,g.level_id from user as u INNER JOIN  user_group as g on u.group_id=g.id where u.worker_num=' . $worker_num);
        }

    }

    public function  getInspectionRecord($worker_num)
    {
        if ($worker_num) {
            return $this->fetchOne('select u.id,u.user_name,g.group_name,g.level_id from user as u INNER JOIN  user_group as g on u.group_id=g.id where u.worker_num=' . $worker_num);
        }

    }

    /*
     *
     *获取配置
     *
     * */
    public function  getSetting($key, $is_array = false)
    {
        $value = $this->get('setting', 'setting_value', ['setting_key' => $key]);
        if ($is_array && $value) {

            return json_decode($value['setting_value'], true);

        }
        return $value['setting_value'];
    }



}
