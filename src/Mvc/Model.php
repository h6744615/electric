<?php

namespace Windward\Mvc;

Class Model extends \Windward\Core\Base {

    protected $dbConnection;
    protected $pdo;
    protected $transactionLevel = 0;

    function setDbConnection(\medoo $dbConnection) {
        $this->dbConnection = $dbConnection;
    }

    public function setPdo(\PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function exec($sql) {
        return $this->pdo->exec($sql);
    }

    public function begin() {
        if ($this->transactionLevel === 0) {
            $this->pdo->beginTransaction();
        } else {
            $this->exec("SAVEPOINT LEVEL{$this->transactionLevel}");
        }
        $this->transactionLevel++;
    }

    public function rollback() {
        $this->transactionLevel--;
        if ($this->transactionLevel === 0) {
            $this->pdo->rollBack();
        } else {
            $this->exec("ROLLBACK TO SAVEPOINT LEVEL{$this->transactionLevel}");
        }
    }

    public function commit() {
        $this->transactionLevel--;
        if ($this->transactionLevel === 0) {
            $this->pdo->commit();
        } else {
            $this->exec("RELEASE SAVEPOINT LEVEL{$this->transactionLevel}");
        }
    }

    protected function query($sql, $params = null) {
        $stmt = $this->pdo->prepare($sql);
        if ($params) {
            $rs = $stmt->execute($params);
        } else {
            $rs = $stmt->execute();
        }

        return $rs ? $stmt : null;
    }

    public function fetchOne($sql, $params = null) {
        $stmt = $this->query($sql, $params);
        if ($stmt) {
            return $stmt->fetch();
        }
        return array();
    }

    public function fetchAll($sql, $params = null) {
        $stmt = $this->query($sql, $params);
        if ($stmt) {
            return $stmt->fetchAll();
        }
        return array();
    }

    public function get($table = '', $fields = '*', $cond = array()) {
        $sql = "select {$fields} from {$table} where 1 = 1";

        $params = array();
        foreach ($cond as $key => $val) {
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
            if (preg_match('/^\[in\]/', $key)) {
                $key = substr($key, 4);
                if (is_array($val)) {
                    $val = implode(',', $val);
                } else {
                    $val = (string) $val;
                }
                $sql .= " and {$key} in (" . $val . ")";
                continue;
            }
            //other todo

            $sql .= " and $key = :cond_{$key}";
            $params[":cond_{$key}"] = $val;
        }

        $stmt = $this->query($sql, $params);
        if ($stmt) {
            return $stmt->fetch();
        }
        return array();
    }

    public function update($table = '', $data = array(), $cond = array()) {
        if (!$table || !$data) {
            return false;
        }

        $vals = array();
        $sql = "update {$table} set ";
        foreach ($data as $key => $val) {
            if (preg_match('/^\[eq\]/', $key)) {
                $key = substr($key, 4);
                $sql .= "{$key} = " . $val . ",";
                continue;
            }

            $sql .= "{$key} = :{$key},";
            $vals[":{$key}"] = $val;
        }
        $sql = rtrim($sql, ',');
        $sql .= ' where 1 = 1';
        foreach ($cond as $key => $val) {
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
            if (preg_match('/^\[in\]/', $key)) {
                $key = substr($key, 4);
                if (is_array($val)) {
                    $val = implode(',', $val);
                } else {
                    $val = (string) $val;
                }
                $sql .= " and {$key} in (" . $val . ")";
                continue;
            }
            //other todo

            $sql .= " and $key = :cond_{$key}";
            $vals[":cond_{$key}"] = $val;
        }
        $sql = rtrim($sql, ',');

        $stmt = $this->pdo->prepare($sql);
        if ($stmt->execute($vals) === false) {
            return false;
        }

        return true;
    }

    public function insert($table = '', $data = array(), $ignore = false) {
        if (!$table || !$data) {
            return false;
        }

        $vals = array();
        $sql = "insert into {$table} set ";
        if ($ignore) {
            $sql = "insert ignore into {$table} set ";
        }

        foreach ($data as $key => $val) {
            if (preg_match('/^\[eq\]/', $key)) {
                $key = substr($key, 4);
                $sql .= "{$key} = " . $val . ",";
                continue;
            }

            $sql .= "{$key} = :{$key},";
            $vals[":{$key}"] = $val;
        }
        $sql = rtrim($sql, ',');

        $stmt = $this->pdo->prepare($sql);
        if ($stmt->execute($vals) === false) {
            return false;
        }

        return $this->pdo->lastInsertId();
    }

    public function replace($table = '', $data = array()) {
        if (!$table || !$data) {
            return false;
        }

        $vals = array();
        $sql = "replace into {$table} set ";

        foreach ($data as $key => $val) {
            if (preg_match('/^\[eq\]/', $key)) {
                $key = substr($key, 4);
                $sql .= "{$key} = " . $val . ",";
                continue;
            }

            $sql .= "{$key} = :{$key},";
            $vals[":{$key}"] = $val;
        }
        $sql = rtrim($sql, ',');

        $stmt = $this->pdo->prepare($sql);
        if ($stmt->execute($vals) === false) {
            return false;
        }

        return $this->pdo->lastInsertId();
    }

    public function delete($table = '', $cond = array()) {
        if (!$table || !$cond) {
            return false;
        }

        $vals = array();
        $sql = "delete from {$table} where 1 = 1 ";

        foreach ($cond as $key => $val) {
            if (preg_match('/^func:/', $val)) {
                $sql .= " and {$key} = " . substr($val, 5);
                continue;
            }

            $sql .= " and $key = :cond_{$key}";
            $vals[":cond_{$key}"] = $val;
        }

        $sql = rtrim($sql, ',');

        $stmt = $this->pdo->prepare($sql);
        if ($stmt->execute($vals) === false) {
            return false;
        }

        return true;
    }

}
