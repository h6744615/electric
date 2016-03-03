<?php

namespace Windward\Extend\Pager\Adapter;

use Windward\Mvc\Model;
use Pagerfanta\Adapter\AdapterInterface;

class ModelAdapter implements AdapterInterface
{

    private $sql;
    private $model;
    
    /**
     * Constructor.
     *
     * @param array $sql The sql.
     */
    public function __construct(Model $model, $sql)
    {
        $selectSql = preg_replace(
            '/([\w]+)\s*(?=limit)limit\s*\d+\s*,\d+$/i', 
            '${1}', 
            $sql
        );
        $this->model = $model;
        $this->sql = $selectSql;
    }

    /**
     * {@inheritdoc}
     */
    public function getNbResults()
    {
        $countSql = preg_replace(
            '/select.*?from(.*)/is',
            'select count(*) as cnt from ${1}', 
            $this->sql
        );
        $rs = $this->model->fetchOne($countSql);
        return $rs['cnt'];
    }

    /**
     * {@inheritdoc}
     */
    public function getSlice($offset, $length)
    {
        $offset = (int)$offset;
        $length = (int)$length;
        return$this->model->fetchAll($this->sql . ' LIMIT ' . $offset . ', ' . $length);
    }

}
