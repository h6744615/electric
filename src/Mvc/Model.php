<?php

namespace Windward\Mvc;

use Windward\Core\Logger;

class Model extends \Windward\Core\Base
{

    protected $dbConnection;
    protected $pdo;
    protected $transactionLevel = 0;
    protected $logging = true;
    protected $hiddenParamIndex;
    protected $affectedRow = 0;
        
    public function setPdo(\PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function exec($sql)
    {
        if ($this->logging && $this->logger) {
            $this->logger->log('db', 'SQL:', $sql);
        }
        return $this->pdo->exec($sql);
    }

    public function begin()
    {
        if ($this->transactionLevel === 0) {
            $this->pdo->beginTransaction();
        } else {
            $this->exec("SAVEPOINT LEVEL{$this->transactionLevel}");
        }
        $this->transactionLevel++;
    }

    public function rollback()
    {
        $this->transactionLevel--;
        if ($this->transactionLevel === 0) {
            $this->pdo->rollBack();
        } else {
            $this->exec("ROLLBACK TO SAVEPOINT LEVEL{$this->transactionLevel}");
        }
    }

    public function commit()
    {
        $this->transactionLevel--;
        if ($this->transactionLevel === 0) {
            $this->pdo->commit();
        } else {
            $this->exec("RELEASE SAVEPOINT LEVEL{$this->transactionLevel}");
        }
    }
    
    public function affectedRow()
    {
        return $this->affectedRow;
    }

    public function query($sql, $params = null)
    {
        if ($this->logging && $this->logger) {
            $this->logger->log('db', 'SQL:', $sql, 'PARAMS:', $params);
        }
        $stmt = $this->pdo->prepare($sql);
        if ($params) {
            $rs = $stmt->execute($params);
        } else {
            $rs = $stmt->execute();
        }

        return $rs ? $stmt : null;
    }

    public function fetchOne($sql, $params = null)
    {
        if (!preg_match('#(limit\s+\d+,?\s*\d*\s*$|for\s+update)#iUsm', $sql)) {
            $sql .= " LIMIT 1";
        }
        $stmt = $this->query($sql, $params);
        if ($stmt) {
            return $stmt->fetch();
        }
        return array();
    }

    public function fetchAll($sql, $params = null)
    {
        $stmt = $this->query($sql, $params);
        if ($stmt) {
            return $stmt->fetchAll();
        }
        return array();
    }

    /**
     * 添加排序
     *
     * @param string $field 排序字段和方式 e.g. 'a.asc, b.desc'
     * @param string &$sql SQL语句
     * @param array $config 排序配置 e.g. ['a' => ['field' => 'id', 'type' => 'ASC'], 'b' => ['field' => 'id', 'type' => 'DESC']]
     *
     * @return type
     */
    public function orderBy(&$sql, $field, array $config)
    {
        if (!$field || !$config) {
            return;
        }
        $fields = preg_split('#(,\s*)#iUs', $field);
        $orderBy = '';
        foreach ($fields as $one) {
            $tmp = explode('.', trim($one));
            $type = $tmp[1];
            if (!$type && isset($config['type'])) {
                $type = $config['type'];
            }
            if (strtoupper($type) != 'DESC') {
                $type = 'ASC';
            }
            $orderBy .= $config[$tmp[0]]['field'] . ' ' . $type . ',';
        }
        $orderBy = trim($orderBy, ',');
        if ($orderBy) {
            $sql .= ' ORDER BY ' . $orderBy;
        }
    }

    public function cond(&$sql, $cond = array())
    {
        $params = array();
        foreach ($cond as $key => $val) {
            $this->hiddenParamIndex++;
            $bKey = '__cond_val_' . $this->hiddenParamIndex;
            if (preg_match('/^\[eq\]/', $key)) {
                $key = substr($key, 4);
                $sql .= " and {$key} = " . $val;
                continue;
            }
            if (preg_match('/^\[neq\]/', $key)) {
                $key = substr($key, 5);
                $sql .= " and {$key} != " . $val;
                continue;
            }
            if (preg_match('/^\[<=\]/', $key)) {
                $key = substr($key, 4);
                $sql .= " and {$key} <= " . $val;
                continue;
            }
            if (preg_match('/^\[lk\]/', $key)) {
                $key = substr($key, 4);
                $sql .= " and {$key} like :{$$bKey}";
                $params[$bKey] = $val;
                continue;
            }
            if (preg_match('/^\[in\]/', $key)) {
                $key = substr($key, 4);
                $val = (array)$val;
                $inWhere = '';
                foreach ($val as $k => $v) {
                    $tmp = ':cond_in_' . $this->hiddenParamIndex;
                    $inWhere .=  $tmp . ',';
                    $params[$tmp] = $v;
                    $this->hiddenParamIndex++;
                }
                $inWhere = trim($inWhere, ', ');
                $sql .= " and {$key} in (" . $inWhere . ")";
                continue;
            }
            if (preg_match('/^\[fis\]/', $key)) {
                $key = substr($key, 5);
                $tmp = ':cond_fis_' . $this->hiddenParamIndex;
                $sql .= " and find_in_set({$tmp}, {$key})";
                $params[$tmp] = $val;
                continue;
            }
            if (preg_match('/^func:/', $val)) {
                $sql .= " and {$key} = " . substr($val, 5);
                continue;
            }
            //other todo
            $sql .= " and $key = :{$bKey}";
            $params[$bKey] = $val;
        }
        return $params;
    }

    /*
     * 获取单表的单条/多条数据 todo
     */
    public function get(
        $table = '',
        $fields = '*',
        $cond = array(),
        $single = true,
        $orderby = null,
        $forUpdate = false
    ) {
        $sql = "select {$fields} from {$table} where 1 = 1";

        $params = $this->cond($sql, $cond);
        if ($orderby) {
            $sql .= " order by {$orderby}";
        }
        if ($single) {
            $sql .= ' LIMIT 1';
        }
        if ($forUpdate) {
            $sql .= " FOR UPDATE";
        }
        $stmt = $this->query($sql, $params);
        if ($stmt) {
            return $single ? $stmt->fetch() : $stmt->fetchAll();
        }
        return array();
    }

    public function count($table = '', $cond = array())
    {
        $sql = "select count(*) as cnt from {$table} where 1 = 1";
        $params = $this->cond($sql, $cond);
        $stmt = $this->query($sql, $params);
        if ($stmt) {
            return $stmt->fetch()['cnt'];
        }
        return 0;
    }

    public function formatData($data, &$sql, &$vals, $type = 'update')
    {
        foreach ($data as $key => $val) {
            $this->hiddenParamIndex++;
            if (preg_match('/^\[eq\]/', $key)) {
                $key = substr($key, 4);
                $sql .= "{$key} = " . $val . ",";
                continue;
            }
            $bKey = '__' . $type . '_val_' . $this->hiddenParamIndex;
            $sql .= "{$key} = :{$bKey},";
            $vals[":{$bKey}"] = $val;
        }
        $sql = rtrim($sql, ',');
    }

    public function update($table = '', $data = array(), $cond = array())
    {
        if (!$table || !$data) {
            return false;
        }

        $vals = array();
        $sql = "update {$table} set ";
        
        $this->formatData($data, $sql, $vals, 'update');

        $sql .= ' where 1 = 1';
        $params = $this->cond($sql, $cond);
        $vals = array_merge($vals, $params);
        if ($this->logging && $this->logger) {
            $this->logger->log('db', 'SQL:', $sql, 'PARAM:', $vals);
        }
        $stmt = $this->pdo->prepare($sql);
        if ($stmt->execute($vals) === false) {
            return false;
        }
        
        $this->affectedRow = $stmt->rowCount();
        return true;
    }

    public function insert($table = '', $data = array(), $ignore = false)
    {
        if (!$table || !$data) {
            return false;
        }

        $vals = array();
        $sql = "insert into {$table} set ";
        if ($ignore) {
            $sql = "insert ignore into {$table} set ";
        }
        
        $this->formatData($data, $sql, $vals, 'insert');

        $stmt = $this->pdo->prepare($sql);
        if ($this->logging && $this->logger) {
            $this->logger->log('db', 'SQL:', $sql, 'PARAMS:', $vals);
        }
        if ($stmt->execute($vals) === false) {
            return false;
        }

        return $this->pdo->lastInsertId();
    }

    public function replace($table = '', $data = array())
    {
        if (!$table || !$data) {
            return false;
        }

        $vals = array();
        $sql = "replace into {$table} set ";

        $this->formatData($data, $sql, $vals, 'replace');

        if ($this->logging && $this->logger) {
            $this->logger->log('db', 'SQL:', $sql, 'PARAMS:', $vals);
        }
        $stmt = $this->pdo->prepare($sql);
        if ($stmt->execute($vals) === false) {
            return false;
        }

        return $this->pdo->lastInsertId();
    }

    public function delete($table = '', $cond = array())
    {
        if (!$table || !$cond) {
            return false;
        }

        $sql = "delete from {$table} where 1 = 1 ";
        $params = $this->cond($sql, $cond);
        if ($this->logging && $this->logger) {
            $this->logger->log('db', 'SQL:', $sql, 'PARAMS:', $params);
        }
        $stmt = $this->pdo->prepare($sql);
        if ($stmt->execute($params) === false) {
            return false;
        }
        
        $this->affectedRow = $stmt->rowCount();
        return true;
    }

    public function paginate($sql, $curpage = 1, $limit = 20, $cond = null, $countSql = '')
    {
        $curpage = (int) $curpage;
        $limit = (int) $limit;
        if ($curpage === 0) {
            $curpage = 1;
        }
        if ($limit === 0) {
            $limit = 20;
        }
        $selectSql = preg_replace(
            '/([\w]+)\s*(?=limit)limit\s*\d+\s*,\d+$/i',
            '${1}',
            $sql
        );
        if (!$countSql) {
            $countSql = preg_replace(
                '/select.*?from(.*)/is',
                'select count(*) as cnt from ${1}',
                $selectSql
            );
        }
        $offset = ($curpage - 1) * $limit;
        $selectSql .= " limit {$offset},{$limit}";

        $items = $this->fetchAll($selectSql, $cond);
        $totalItems = (int) $this->fetchOne($countSql, $cond)['cnt'];
        if (!$totalItems) {
            return array(
                'total_page' => 0,
                'current_page' => 0,
                'perpage' => $limit,
                'total_items' => 0,
                'items' => array(),
            );
        }

        $totalPage = ceil($totalItems / $limit);
        return array(
            'total_page' => $totalPage,
            'current_page' => $curpage,
            'perpage' => $limit,
            'total_items' => $totalItems,
            'items' => $items,
        );
    }

    public function setLogging($logging)
    {
        $this->logging = $logging;
    }
}
