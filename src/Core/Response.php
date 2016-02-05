<?php
  
namespace Windward\Core;

abstract class Response extends Base {
    abstract public function output($return = false);
}