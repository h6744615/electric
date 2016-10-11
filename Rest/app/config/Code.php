<?php

namespace App\Config;

class_alias('\Base\Config\\' . ucfirst(APP_ENV) . '\Code', "BaseCodeConfig");

class Code extends \BaseCodeConfig
{
    
}
