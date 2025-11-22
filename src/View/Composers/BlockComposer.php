<?php

namespace Takt\Score\View\Composers;

use Roots\Acorn\View\Composer;

class BlockComposer
{
    /**
     * The view or views that this composer applies to.
     *
     * @var array|string
     */
    protected $views = ['*'];

    /**
     * Bind data to the view.
     *
     * @return void
     */
    public function compose()
    {
        dd(1);
        return [];
    }
}
