<?php

namespace Windward\Extend;

use Pagerfanta\Pagerfanta;

class Pager extends Pagerfanta
{

    public function setCurrentPage($currentPage)
    {
        if (!is_numeric($currentPage) || !$currentPage) {
            $currentPage = 1;
        }
        $this->setNormalizeOutOfRangePages(true);
        parent::setCurrentPage($currentPage);
    }
}
