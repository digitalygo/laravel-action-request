<?php

declare(strict_types=1);

namespace Digitalygo\ActionRequest\Console;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;

final class MakeActionRequestCommand extends Command
{
    protected $signature = 'make:action-request
                            {model : The model name (e.g., Post)}
                            {name : The action name (e.g., Store)}
                            {version=v1 : The API version}
                            {--preset= : The preset to use (simple-endpoint)}
                            {--resource=none : Response type (none, resource, collection)}
                            {--with-lint : Run Pint on generated files}
                            {--with-test : Run Pest on generated test}';

    protected $description = 'Generate an Action, Form Request, and Pest test';

    private Filesystem $files;

    public function __construct(Filesystem $files)
    {
        parent::__construct();
        $this->files = $files;
    }

    public function handle(): int
    {
        $model = $this->argument('model');
        $name = $this->argument('name');
        $version = $this->argument('version');
        $preset = $this->option('preset') ?? config('action-request.preset_default');
        $resource = $this->option('resource') ?? config('action-request.response_type', 'none');
        $withLint = $this->option('with-lint') ?? config('action-request.flags.with_lint', false);
        $withTest = $this->option('with-test') ?? config('action-request.flags.with_test', false);

        $actionPath = $this->getPath('actions_path', $version, $model);
        $requestPath = $this->getPath('requests_path', $version, $model);
        $testPath = $this->getPath('tests_path', $version, $model);

        $actionNamespace = $this->getNamespace('actions_path', $version, $model);
        $requestNamespace = $this->getNamespace('requests_path', $version, $model);
        $testNamespace = $this->getNamespace('tests_path', $version, $model);

        $actionClass = "{$name}{$model}Action";
        $requestClass = "{$name}{$model}Request";
        $testClass = "{$name}{$model}ActionTest";

        $actionFile = "{$actionPath}/{$actionClass}.php";
        $requestFile = "{$requestPath}/{$requestClass}.php";
        $testFile = "{$testPath}/{$testClass}.php";

        $this->ensureDirectoryExists($actionPath);
        $this->ensureDirectoryExists($requestPath);
        $this->ensureDirectoryExists($testPath);

        $this->generateAction($actionFile, $actionNamespace, $actionClass, $requestNamespace, $requestClass, $model, $name, $preset, $resource);
        $this->generateRequest($requestFile, $requestNamespace, $requestClass, $model, $name, $preset);
        $this->generateTest($testFile, $testNamespace, $testClass, $actionNamespace, $actionClass, $requestNamespace, $requestClass, $model, $name, $preset);

        $this->info("Generated: {$actionFile}");
        $this->info("Generated: {$requestFile}");
        $this->info("Generated: {$testFile}");

        if ($withLint) {
            $this->runLint([$actionFile, $requestFile, $testFile]);
        }

        if ($withTest) {
            $this->runTest($testFile);
        }

        return self::SUCCESS;
    }

    private function getPath(string $configKey, string $version, string $model): string
    {
        $basePath = config("action-request.{$configKey}", base_path($configKey));
        $fullPath = base_path($basePath);

        return "{$fullPath}/{$version}/{$model}";
    }

    private function getNamespace(string $configKey, string $version, string $model): string
    {
        $baseNamespace = config('action-request.namespace', 'App\\');
        $relativePath = config("action-request.{$configKey}", $configKey);
        $relativePath = ltrim($relativePath, '/\\');

        // Strip leading app/ if present to avoid duplicated App\\app\\ in namespaces
        if (str_starts_with($relativePath, 'app/')) {
            $relativePath = substr($relativePath, 4);
        }
        if (str_starts_with($relativePath, 'app\\')) {
            $relativePath = substr($relativePath, 4);
        }

        $pathNamespace = str_replace(['/', '\\'], '\\', $relativePath);

        return "{$baseNamespace}{$pathNamespace}\\{$version}\\{$model}";
    }

    private function ensureDirectoryExists(string $path): void
    {
        if (! $this->files->isDirectory($path)) {
            $this->files->makeDirectory($path, 0755, true);
        }
    }

    private function generateAction(string $file, string $namespace, string $class, string $requestNamespace, string $requestClass, string $model, string $name, ?string $preset, string $resource): void
    {
        $stubFile = $preset === 'simple-endpoint'
            ? $this->getStubPath('action-simple.stub')
            : $this->getStubPath('action.stub');

        $stub = $this->files->get($stubFile);

        $replacements = [
            '{{ namespace }}' => $namespace,
            '{{ class }}' => $class,
            '{{ requestNamespace }}' => $requestNamespace,
            '{{ requestClass }}' => $requestClass,
            '{{ requestClassWithNamespace }}' => "\\{$requestNamespace}\\{$requestClass}",
            '{{ model }}' => $model,
            '{{ modelVariable }}' => Str::camel($model),
            '{{ actionName }}' => $name,
            '{{ version }}' => $this->argument('version'),
            '{{ operationId }}' => Str::kebab("{$name}{$model}"),
            '{{ path }}' => "/api/{$this->argument('version')}/".Str::plural(Str::kebab($model)),
        ];

        // Handle resource type
        if ($resource === 'collection') {
            $replacements['{{ returnType }}'] = 'ResourceCollection';
            $replacements['{{ returnUse }}'] = "use Illuminate\\Http\\Resources\\Json\\ResourceCollection;\n";
            $replacements['{{ returnComment }}'] = '/**\n     * @return ResourceCollection<array<string, mixed>>\n     */';
        } elseif ($resource === 'resource') {
            $replacements['{{ returnType }}'] = 'JsonResource';
            $replacements['{{ returnUse }}'] = "use Illuminate\\Http\\Resources\\Json\\JsonResource;\n";
            $replacements['{{ returnComment }}'] = '/**\n     * @return JsonResource\n     */';
        } else {
            $replacements['{{ returnType }}'] = 'array';
            $replacements['{{ returnUse }}'] = '';
            $replacements['{{ returnComment }}'] = '/**\n     * @return array<string, mixed>\n     */';
        }

        $content = str_replace(array_keys($replacements), array_values($replacements), $stub);
        $this->files->put($file, $content);
    }

    private function generateRequest(string $file, string $namespace, string $class, string $model, string $name, ?string $preset): void
    {
        $stubFile = $preset === 'simple-endpoint'
            ? $this->getStubPath('request-simple.stub')
            : $this->getStubPath('request.stub');

        $stub = $this->files->get($stubFile);

        $replacements = [
            '{{ namespace }}' => $namespace,
            '{{ class }}' => $class,
            '{{ model }}' => $model,
            '{{ modelVariable }}' => Str::camel($model),
            '{{ actionName }}' => $name,
        ];

        $content = str_replace(array_keys($replacements), array_values($replacements), $stub);
        $this->files->put($file, $content);
    }

    private function generateTest(string $file, string $namespace, string $class, string $actionNamespace, string $actionClass, string $requestNamespace, string $requestClass, string $model, string $name, ?string $preset): void
    {
        $stubFile = $preset === 'simple-endpoint'
            ? $this->getStubPath('test-simple.stub')
            : $this->getStubPath('test.stub');

        $stub = $this->files->get($stubFile);

        $replacements = [
            '{{ namespace }}' => $namespace,
            '{{ class }}' => $class,
            '{{ actionNamespace }}' => $actionNamespace,
            '{{ actionClass }}' => $actionClass,
            '{{ actionClassWithNamespace }}' => "\\{$actionNamespace}\\{$actionClass}",
            '{{ requestNamespace }}' => $requestNamespace,
            '{{ requestClass }}' => $requestClass,
            '{{ requestClassWithNamespace }}' => "\\{$requestNamespace}\\{$requestClass}",
            '{{ model }}' => $model,
            '{{ modelVariable }}' => Str::camel($model),
            '{{ modelPlural }}' => Str::plural(Str::kebab($model)),
            '{{ actionName }}' => $name,
            '{{ actionNameKebab }}' => Str::kebab($name),
            '{{ version }}' => $this->argument('version'),
        ];

        $content = str_replace(array_keys($replacements), array_values($replacements), $stub);
        $this->files->put($file, $content);
    }

    private function getStubPath(string $stub): string
    {
        $customPath = base_path("stubs/action-request/{$stub}");
        if ($this->files->exists($customPath)) {
            return $customPath;
        }

        return __DIR__."/../../stubs/{$stub}";
    }

    private function runLint(array $files): void
    {
        $this->info('Running Pint...');

        $process = new Process(array_merge(['./vendor/bin/pint'], $files));
        $process->setWorkingDirectory(base_path());
        $process->run();

        if ($process->isSuccessful()) {
            $this->info('Pint completed successfully.');
        } else {
            $this->warn('Pint found issues or failed to run.');
            $this->warn($process->getErrorOutput());
        }
    }

    private function runTest(string $file): void
    {
        $this->info('Running Pest...');

        $process = new Process(['./vendor/bin/pest', $file]);
        $process->setWorkingDirectory(base_path());
        $process->run();

        if ($process->isSuccessful()) {
            $this->info('Pest completed successfully.');
        } else {
            $this->warn('Pest found issues or failed to run.');
            $this->warn($process->getErrorOutput());
        }
    }
}
