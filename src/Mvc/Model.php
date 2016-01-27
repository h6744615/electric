<?php

namespace Windward\Mvc;

Class Model extends \Windward\Core\Base {

    protected $dbConnection;

    function setDbConnection(\medoo $dbConnection) {
        $this->dbConnection = $dbConnection;
    }

}
