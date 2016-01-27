<?php

namespace Windward\Extend;

class Database extends \medoo {

    public function paginate($table, $column = '*', $where = null, $join = null, $curr = 1, $pagesize = 20) {
        if (is_numeric($where)) {
            $curr = $where;
            $where = NULL;
            if (is_numeric($join)) {
                $pagesize = $join;
            }
            $join = NULL;
        }
        if (is_numeric($join)) {
            $pagesize = $curr;
            $curr = $join;
            $join = NULL;
        }
        if(is_null($where)) {
            $count = $this->count($table, '*');
        } else {
            $count = $this->count($table, $join, '*', $where);
        }
        
        if ($count == 0) {
            return array(
                'total_page' => 0,
                'total_items' => 0,
                'items' => array(),
            );
        }
        if ($pagesize == 0) {
            $pagesize = 0;
        }
        $totalPage = ceil($count / $pagesize * 1.0);
        $from = ($curr - 1) * $pagesize;
        $where['LIMIT'] = array($from, $pagesize);
        
        if(is_null($join)) {
            $items = $this->select($table, $column, $where);

        } else {
            $items = $this->select($table, $join, $column, $where);
        }
        return array(
            'total_page' => $totalPage,
            'total_items' => $count + 0,
            'items' => $items,
        );
    }

}
