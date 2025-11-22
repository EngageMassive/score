<?php

namespace Takt\Score;

use Illuminate\Support\Str;
use Illuminate\View\View;
use Roots\Acorn\Application;

class Score
{
    /**
     * The application instance.
     *
     * @var \Roots\Acorn\Application
     */
    protected $app;

    /**
     * Create a new Score instance.
     *
     * @param  \Roots\Acorn\Application  $app
     * @return void
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    public function init()
    {
        $path = get_stylesheet_directory() . '/blocks';
        if (!file_exists($path)) {
            return;
        }

        $blocks = new \FilesystemIterator($path);

        $this->registerBlocks($blocks);
    }

    /**
     * Retrieve a random inspirational quote.
     *
     * @return string
     */
    public function registerBlocks($blocks)
    {
        foreach ($blocks as $dir) {
            if (
                !$dir->isDir() ||
                !file_exists($dir->getPathname() . '/block.json')
            ) {
                continue;
            }

            $this->registerBlocks(new \FilesystemIterator($dir->getPathname()));

            $block = register_block_type($dir->getPathname(), [
                'render_callback' => function (
                    $attributes,
                    $children,
                    $block,
                ) use ($dir) {
                    $blockName = Str::after($block->name, '/');

                    $viewFile = $dir->getBaseName() . '.view';

                    try {
                        return View::first(
                            [$viewFile],
                            compact('attributes', 'block', 'children'),
                        );
                    } catch (\Exception $e) {
                        if ($block->block_type->category != 'meta') {
                            return '<span style="text-align: center; font-size: 4rem">View does not exist for ' .
                                $blockName .
                                '</span>';
                        }
                    }
                },
            ]);
        }
    }
}
