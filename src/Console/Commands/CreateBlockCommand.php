<?php

namespace Takt\Score\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Contracts\Console\PromptsForMissingInput;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use Roots\Acorn\Application;

class CreateBlockCommand extends Command implements PromptsForMissingInput
{
    protected string $blockName;

    protected Filesystem $files;

    protected string $jsExtension;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:block {name : The name of the block} {--js} {--P|parent=} {--anchor}';

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
                "Full-site editing support requires <fg=red>Acorn {$this->version}</> or higher."
            );

            return;
        }

        $this->files = new Filesystem();
        $this->blockName = Str::kebab($this->argument('name'));
        $this->jsExtension = $this->option('js') ? 'js' : 'tsx';

        $this->createDirectory();
        $this->createBlockFile();
        $this->createBlockIndexFile();
        $this->createBlockEditFile();
        $this->createBlockViewFile();
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
        return resource_path() . '/blocks';
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
            $path = $path . Str::kebab($this->option('parent')) . '/';
        }

        return $path . $this->blockName;
    }

    /**
     * Return the view destination path.
     *
     * @return string
     */
    public function getViewPath()
    {
        return resource_path() . '/views/blocks';
    }

    /**
     * Create the Block Directory
     */
    protected function createDirectory(): void
    {
        if ($this->files->exists($this->getBlockPath())) {
            throw new Exception(
                'Block directory ' . $this->getBlockPath() . ' already exists.'
            );
        }

        $this->files->makeDirectory($this->getBlockPath());
    }

    protected function createBlockFile(): void
    {
        $file = $this->getBlockPath() . '/block.json';
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
                    $this->blockName,
                    Str::headline($this->blockName),
                    Str::kebab($this->option('parent')) ?? '',
                ],
                $stub
            )
        );

        $this->components->info("The block file has been created at {$file}.");
    }

    protected function createBlockIndexFile(): void
    {
        $file = $this->getBlockPath() . '/index.' . $this->jsExtension;

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
                $this->files->get(__DIR__ . '/stubs/index.stub')
            )
        );

        $this->components->info(
            "The block index file has been created at {$file}."
        );
    }

    protected function createBlockEditFile(): void
    {
        $file =
            $this->getBlockPath() .
            '/' .
            Str::studly($this->blockName) .
            '.' .
            $this->jsExtension;

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
                $this->files->get(__DIR__ . '/stubs/edit.stub')
            )
        );

        $this->components->info(
            "The block edit file has been created at {$file}."
        );
    }

    protected function createBlockViewFile(): void
    {
        $file = $this->getViewPath() . '/' . $this->blockName . '.blade.php';

        if ($this->files->exists($file)) {
            $this->components->warn(
                "The block view file already exists at {$file}."
            );

            return;
        }

        $this->files->put(
            $file,
            str_replace(
                '{{DummyBlockHeadline}}',
                Str::headline($this->blockName),
                $this->files->get(__DIR__ . '/stubs/view.stub')
            )
        );

        $this->components->info(
            "The block view file has been created at {$file}."
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
