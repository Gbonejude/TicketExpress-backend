<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;

class MakeActionCommand extends Command
{
    protected $signature = 'make:action
                            {name : The action name with optional version and domain (e.g. V1/Auth/SendOtp)}
                            {--reversible : Implement the ReversibleAction interface instead}';

    protected $description = 'Create a new action class in app/Actions/ (supports versioned paths like V1/Auth/SendOtp)';

    public function __construct(private readonly Filesystem $files)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $name = $this->normalizeName($this->argument('name'));
        $path = $this->getPath($name);

        if ($this->files->exists($path)) {
            $this->components->error("Action [{$name}] already exists.");

            return self::FAILURE;
        }

        $this->ensureDirectoryExists(dirname($path));
        $this->files->put($path, $this->buildClass($name));

        $relativePath = Str::after($path, base_path().'/');
        $this->components->info("Action [{$relativePath}] created successfully.");

        return self::SUCCESS;
    }

    private function normalizeName(string $name): string
    {
        $name = str_replace('\\', '/', $name);

        return Str::endsWith($name, 'Action') ? $name : "{$name}Action";
    }

    private function getPath(string $name): string
    {
        return app_path('Actions/'.str_replace('\\', '/', $name).'.php');
    }

    private function getNamespace(string $name): string
    {
        $segments = explode('/', $name);
        array_pop($segments);

        return empty($segments)
            ? 'App\\Actions'
            : 'App\\Actions\\'.implode('\\', $segments);
    }

    private function getClassName(string $name): string
    {
        $segments = explode('/', $name);

        return end($segments);
    }

    private function buildClass(string $name): string
    {
        $stub = $this->option('reversible')
            ? $this->getReversibleStub()
            : $this->files->get($this->getStubPath());

        return str_replace(
            ['{{ namespace }}', '{{ class }}'],
            [$this->getNamespace($name), $this->getClassName($name)],
            $stub
        );
    }

    private function getStubPath(): string
    {
        $custom = base_path('stubs/action.stub');

        return $this->files->exists($custom) ? $custom : __DIR__.'/stubs/action.stub';
    }

    private function getReversibleStub(): string
    {
        $custom = base_path('stubs/action.reversible.stub');

        $path = $this->files->exists($custom) ? $custom : __DIR__.'/stubs/action.reversible.stub';

        return $this->files->get($path);
    }

    private function ensureDirectoryExists(string $path): void
    {
        if (! $this->files->isDirectory($path)) {
            $this->files->makeDirectory($path, 0755, true, true);
        }
    }
}
