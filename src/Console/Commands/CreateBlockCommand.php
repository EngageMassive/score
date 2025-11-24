<?php

namespace Takt\Score\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Contracts\Console\PromptsForMissingInput;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use RecursiveIteratorIterator;
use Roots\Acorn\Application;

class CreateBlockCommand extends Command implements PromptsForMissingInput
{
    protected string $blockName;

    protected string $blockPath;

    protected Filesystem $files;

    protected string $jsExtension;

    protected bool $blade = false;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:block {name : The name of the block} {--js} {--blade} {--P|parent=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new block.';

    /**
     * The required Acorn version.
     */
    protected string $version = '4.2.0';

    /**
     * The editor style token.
     */
    protected string $styleToken = '}, 100);';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        if (!$this->isValidAcornVersion()) {
            $this->components->error(
                "Full-site editing support requires <fg=red>Acorn {$this->version}</> or higher.",
            );

            return;
        }

        $this->files = new Filesystem();
        $this->blockName = Str::camel($this->argument('name'));
        $this->jsExtension = $this->option('js') ? 'js' : 'tsx';
        $this->blade = $this->option('blade');
        $this->blockPath = $this->getBlockPath();

        $this->createDirectory();
        $this->createBlockFile();
        $this->createBlockIndexFile();
        $this->createBlockEditFile();
        $this->createBlockViewFile();
        $this->createIconFile();
    }

    /**
     * Prompt for missing input arguments using the returned questions.
     *
     * @return array
     */
    protected function promptForMissingArgumentsUsing()
    {
        return [
            'name' => ['What is the name of the block?', 'ExampleBlock'],
        ];
    }

    /**
     * Return the blocks path.
     *
     * @return string
     */
    public function getBlocksPath()
    {
        return base_path('blocks');
    }

    /**
     * Return the final block path.
     *
     * @return string
     */
    public function getBlockPath()
    {
        $path = $this->getBlocksPath() . '/';

        if (!empty($this->option('parent'))) {
            $path = $this->getParentPath() . '/';
        }

        return $path . Str::ucfirst($this->blockName);
    }

    protected function getParentPath()
    {
        $blocks = new RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator(
                $this->getBlocksPath(),
                ($flags = \FilesystemIterator::SKIP_DOTS),
            ),
            \RecursiveIteratorIterator::SELF_FIRST,
        );

        foreach ($blocks as $dir) {
            if (
                $dir->isDir() &&
                Str::of($this->option('parent'))
                    ->camel()
                    ->ucfirst()
                    ->is($dir->getFilename())
            ) {
                return $dir->getPathname();
            }
        }

        throw new Exception(
            'Parent ' . $this->option('parent') . ' does not exist.',
        );
    }

    /**
     * Create the Block Directory
     */
    protected function createDirectory(): void
    {
        if ($this->files->exists($this->blockPath)) {
            throw new Exception(
                'Block directory ' . $this->blockPath . ' already exists.',
            );
        }

        $this->files->makeDirectory($this->blockPath);
    }

    protected function createIconFile(): void
    {
        $file = $this->blockPath . '/icon.svg';

        $this->files->put(
            $file,
            $this->files->get(__DIR__ . '/stubs/icon.stub'),
        );

        $this->components->info(
            "The block icon file has been created at {$file}.",
        );
    }

    protected function createBlockFile(): void
    {
        $file = $this->blockPath . '/block.json';
        $stub = !empty($this->option('parent'))
            ? $this->files->get(__DIR__ . '/stubs/child-block.stub')
            : $this->files->get(__DIR__ . '/stubs/block.stub');

        $this->files->put(
            $file,
            str_replace(
                [
                    '{{DummyBlock}}',
                    '{{DummyBlockHeadline}}',
                    '{{DummyParentBlock}}',
                ],
                [
                    Str::kebab($this->blockName),
                    Str::headline($this->blockName),
                    Str::kebab($this->option('parent')) ?? '',
                ],
                $stub,
            ),
        );

        $this->components->info("The block file has been created at {$file}.");
    }

    protected function createBlockIndexFile(): void
    {
        $file = $this->blockPath . '/index.' . $this->jsExtension;

        $this->files->put(
            $file,
            str_replace(
                [
                    '{{DummyBlock}}',
                    '{{DummyBlockHeadline}}',
                    '{{DummyBlockCamel}}',
                ],
                [
                    $this->blockName,
                    Str::headline($this->blockName),
                    Str::studly($this->blockName),
                ],
                $this->files->get(__DIR__ . '/stubs/index.stub'),
            ),
        );

        $this->components->info(
            "The block index file has been created at {$file}.",
        );
    }

    protected function createBlockEditFile(): void
    {
        $file = $this->blockPath . '/edit.' . $this->jsExtension;

        $this->files->put(
            $file,
            str_replace(
                [
                    '{{DummyBlock}}',
                    '{{DummyBlockHeadline}}',
                    '{{DummyBlockCamel}}',
                ],
                [
                    $this->blockName,
                    Str::headline($this->blockName),
                    Str::studly($this->blockName),
                ],
                $this->files->get(__DIR__ . '/stubs/edit.stub'),
            ),
        );

        $this->components->info(
            "The block edit file has been created at {$file}.",
        );
    }

    protected function createBlockViewFile(): void
    {
        $file =
            $this->blockPath .
            '/view' .
            ($this->blade ? '.blade' : '') .
            '.php';

        if ($this->files->exists($file)) {
            $this->components->warn(
                "The block view file already exists at {$file}.",
            );

            return;
        }

        $this->files->put(
            $file,
            str_replace(
                '{{DummyBlockHeadline}}',
                Str::headline($this->blockName),
                $this->files->get(__DIR__ . '/stubs/view.stub'),
            ),
        );

        $this->components->info(
            "The block view file has been created at {$file}.",
        );
    }

    /**
     * Determine if the current Acorn version is supported.
     */
    protected function isValidAcornVersion(): bool
    {
        $version = Application::VERSION;

        if (Str::contains($version, 'dev')) {
            return true;
        }

        return version_compare($version, $this->version, '>=');
    }
}
