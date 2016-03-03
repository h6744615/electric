<?php

namespace Windward\Extend\Pager\View;

use Pagerfanta\View\DefaultView;
use Pagerfanta\Pagerfanta;

class AdminView extends DefaultView
{

    public function getHtml($pager, $uri, $query)
    {
        $routeGenerator = function($page) use ($query, $uri) {
            $query['sh']['page'] = $page;
            return $uri . '?' . http_build_query($query);
        };
        $options = [
            'proximity' => 3,
            'next_message' => '&gt;',
            'previous_message' => '&lt;',
            'css_disabled_class' => 'disable',
            'css_current_class' => 'active',
        ];
        return parent::render($pager, $routeGenerator, $options);
    }

}
