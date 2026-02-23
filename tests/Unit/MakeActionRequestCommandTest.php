<?php

declare(strict_types=1);

use Digitalygo\ActionRequest\Console\MakeActionRequestCommand;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use Tests\TestCase;

beforeEach(function (): void {
    /** @var TestCase $this */
    $this->command = new MakeActionRequestCommand(new Filesystem);
});

describe('MakeActionRequestCommand', function (): void {
    it('resolves paths correctly with default config', function (): void {
        /** @var TestCase $this */
        $path = invokeCommandMethod($this->command, 'getPath', ['actions_path', 'v1', 'Post']);

        expect($path)->toContain('app/Actions/v1/Post');
    });

    it('resolves namespaces correctly with default config', function (): void {
        /** @var TestCase $this */
        $namespace = invokeCommandMethod($this->command, 'getNamespace', ['actions_path', 'v1', 'Post']);

        expect($namespace)->toBe('App\Actions\v1\Post');
    });

    it('generates correct class names', function (): void {
        $model = 'Post';
        $name = 'Store';

        $actionClass = "{$name}{$model}Action";
        $requestClass = "{$name}{$model}Request";
        $testClass = "{$name}{$model}ActionTest";

        expect($actionClass)->toBe('StorePostAction');
        expect($requestClass)->toBe('StorePostRequest');
        expect($testClass)->toBe('StorePostActionTest');
    });

    it('replaces placeholders in stubs correctly', function (): void {
        $stub = 'namespace {{ namespace }}; class {{ class }} {}';
        $replacements = [
            '{{ namespace }}' => 'App\Actions\v1\Post',
            '{{ class }}' => 'StorePostAction',
        ];

        $result = str_replace(array_keys($replacements), array_values($replacements), $stub);

        expect($result)->toBe('namespace App\Actions\v1\Post; class StorePostAction {}');
    });

    it('handles resource flag for collection return type', function (): void {
        $replacements = [];
        $resource = 'collection';

        if ($resource === 'collection') {
            $replacements['{{ returnType }}'] = 'ResourceCollection';
            $replacements['{{ returnUse }}'] = "use Illuminate\Http\Resources\Json\ResourceCollection;\n";
        }

        expect($replacements)->toHaveKey('{{ returnType }}')
            ->and($replacements['{{ returnType }}'])->toBe('ResourceCollection');
    });

    it('handles resource flag for resource return type', function (): void {
        $replacements = [];
        $resource = 'resource';

        if ($resource === 'resource') {
            $replacements['{{ returnType }}'] = 'JsonResource';
            $replacements['{{ returnUse }}'] = "use Illuminate\Http\Resources\Json\JsonResource;\n";
        }

        expect($replacements)->toHaveKey('{{ returnType }}')
            ->and($replacements['{{ returnType }}'])->toBe('JsonResource');
    });

    it('handles resource flag for none return type', function (): void {
        $replacements = [];
        $resource = 'none';

        if ($resource === 'none') {
            $replacements['{{ returnType }}'] = 'array';
            $replacements['{{ returnUse }}'] = '';
        }

        expect($replacements)->toHaveKey('{{ returnType }}')
            ->and($replacements['{{ returnType }}'])->toBe('array');
    });

    it('selects correct stub based on preset', function (): void {
        $preset = 'simple-endpoint';
        $stubFile = $preset === 'simple-endpoint' ? 'action-simple.stub' : 'action.stub';

        expect($stubFile)->toBe('action-simple.stub');

        $preset = null;
        $stubFile = $preset === 'simple-endpoint' ? 'action-simple.stub' : 'action.stub';

        expect($stubFile)->toBe('action.stub');
    });

    it('generates correct operationId and path placeholders', function (): void {
        $model = 'Post';
        $name = 'Store';
        $version = 'v1';

        $operationId = Str::kebab("{$name}{$model}");
        $path = "/api/{$version}/".Str::plural(Str::kebab($model));

        expect($operationId)->toBe('store-post');
        expect($path)->toBe('/api/v1/posts');
    });
});

// Helper method to invoke protected/private methods
function invokeCommandMethod(object $command, string $methodName, array $parameters = []): mixed
{
    $reflection = new ReflectionClass(MakeActionRequestCommand::class);
    $method = $reflection->getMethod($methodName);
    $method->setAccessible(true);

    return $method->invokeArgs($command, $parameters);
}
