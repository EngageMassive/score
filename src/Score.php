<?php

namespace Takt\Score;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\View;
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

        $blocks = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator(
                $path,
                ($flags = \FilesystemIterator::SKIP_DOTS),
            ),
            \RecursiveIteratorIterator::CHILD_FIRST,
        );

        $this->registerBlocks($blocks);
    }

    /**
     * Register all the blocks.
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

            $block = [];

            if (
                file_exists($dir->getPathname() . '/view.php') ||
                file_exists($dir->getPathname() . '/view.blade.php')
            ) {
                $viewFile = $dir->getBaseName() . '.view';

                $block['render_callback'] = function (
                    $attributes,
                    $children,
                    $block,
                ) use ($viewFile) {
                    $blockName = Str::after($block->name, '/');

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
                };
            }

            $block = register_block_type($dir->getPathname(), $block);
        }
    }
}
