<?php
namespace Windward\Extend;

class Database extends \Windward\Core\Base {

    private $pdo;
    private $identifierQuoter = '';

    private $options = array(
        'host' => '',
        'port' => '',
        'dbname' => '',
        'unix_socket' => '',
        'charset' => '',
    );

    public function __construct(array $options)
    {
        foreach ($this->options as $key => $value) {
            if (isset($options[$key])) {
                $this->options[$key] = $options[$key];
            } else {
                unset($this->options);
            }
        }   
        $type = strtolower($options['type']);
        switch ($type) {
            case 'mariadb':
            case 'mysql':
                $dsn = 'mysql:' . join(';', $this->options);
                $this->identifierQuoter = '`';
                break;
            default:
                # code...
                break;
        }
        $this->pdo = new \Pdo($dsn, $options['username'], $options['password']);
    }

    public function quoteIdentifier($identifier)
    {   
        if ($identifier === '*') {
            return $identifier;
        }
        if (is_array($identifier)) {
            return join(', ', array_map(array($this, 'quoteIdentifier'), $identifier));
        } 
        if(strpos($identifier, '.') !== false) {
            return join('.', array_map(array($this, 'quoteIdentifier'), explode('.', $identifier)));
        }
        return $this->identifierQuoter 
                . str_replace($this->identifierQuoter, $this->identifierQuoter . $this->identifierQuoter, $identifier)
                . $this->identifierQuoter ;
    }
}