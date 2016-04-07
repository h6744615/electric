<?php

namespace Windward\Extend\Pager\Adapter;

use Windward\Mvc\Model;
use Pagerfanta\Adapter\AdapterInterface;

class ModelAdapter implements AdapterInterface
{

    private $sql;
    private $countSql;
    private $model;
    private $resultParams = null;
    private $countParams = null;
    
    /**
     * Constructor.
     *
     * @param string $sql 结果SQL.
     * @param string $countSql 总数SQL.
     * @param string $resultParams 结果绑定参数.
     * @param string $countParams 总数绑定参数.
     */
    public function __construct(Model $model, $sql, $countSql = '', array $resultParams = null, array $countParams = null)
    {
        $selectSql = preg_replace(
            '/([\w]+)\s*(?=limit)limit\s*\d+\s*,\d+$/i', 
            '${1}', 
            $sql
        );
        $this->model = $model;
        $this->sql = $selectSql;
        $this->countSql = $countSql;
        $this->resultParams = $resultParams;
        $this->countParams = $countParams;
    }

    /**
     * {@inheritdoc}
     */
    public function getNbResults()
    {
        $countSql = $this->countSql;
        if (!$countSql) {
            $countSql = preg_replace(
                '/select.*?from(.*)/is',
                'select count(*) as cnt from ${1}', 
                $this->sql
            );
        }
        $rs = $this->model->fetchOne($countSql, $this->countParams ?: $this->resultParams);
        return $rs['cnt'];
    }

    /**
     * {@inheritdoc}
     */
    public function getSlice($offset, $length)
    {
        $offset = (int)$offset;
        $length = (int)$length;
        return$this->model->fetchAll($this->sql . ' LIMIT ' . $offset . ', ' . $length, $this->resultParams);
    }

}
