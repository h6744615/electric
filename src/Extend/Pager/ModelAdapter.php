<?php

namespace Windward\Extend\Pager\Adapter;

use Windward\Mvc\Model;

class ModelAdapter extends Model implements AdapterInterface
{

    private $sql;

    /**
     * Constructor.
     *
     * @param array $sql The sql.
     */
    public function __construct($sql)
    {
        $selectSql = preg_replace(
            '/([\w]+)\s*(?=limit)limit\s*\d+\s*,\d+$/i', 
            '${1}', 
            $sql
        );
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
        $rs = $this->fetchOne($countSql);
        return $rs['cnt'];
    }

    /**
     * {@inheritdoc}
     */
    public function getSlice($offset, $length)
    {
        $offset = (int)$offset;
        $length = (int)$length;
        return $this->fetchAll($sql . ' LIMIT ' . $offset . ', ' . $length);
    }

}
