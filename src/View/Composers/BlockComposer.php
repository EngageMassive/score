<?php

namespace Takt\Score\View\Composers;

use Illuminate\View\View;
use Takt\Score\Block;

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
    public function compose(View $view)
    {
        if (!isset($view->getData()['block'])) {
            return;
        }

        $block = new Block($view->getData()['block']);

        $view->with('block', $block);
    }
}
