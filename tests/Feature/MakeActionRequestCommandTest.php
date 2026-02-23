<?php

declare(strict_types=1);

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Artisan;

beforeEach(function (): void {
    $this->filesystem = new Filesystem;
    $this->basePath = base_path();

    // Clean up test directories before each test
    cleanupTestFiles();
});

afterEach(function (): void {
    // Clean up test directories after each test
    cleanupTestFiles();
});

describe('make:action-request command', function (): void {
    it('generates action, request, and test files with default options', function (): void {
        $exitCode = Artisan::call('make:action-request', [
            'model' => 'Post',
            'name' => 'Store',
            'version' => 'v1',
        ]);

        expect($exitCode)->toBe(0);

        $actionFile = app_path('Actions/v1/Post/StorePostAction.php');
        $requestFile = app_path('Http/Requests/v1/Post/StorePostRequest.php');
        $testFile = base_path('tests/Feature/Http/Actions/v1/Post/StorePostActionTest.php');

        expect(file_exists($actionFile))->toBeTrue();
        expect(file_exists($requestFile))->toBeTrue();
        expect(file_exists($testFile))->toBeTrue();

        // Verify action file content
        $actionContent = file_get_contents($actionFile);
        expect($actionContent)->toContain('namespace App\Actions\v1\Post;');
        expect($actionContent)->toContain('class StorePostAction');
        expect($actionContent)->toContain('use AsAction;');
        expect($actionContent)->toContain('operationId: store-post');
        expect($actionContent)->toContain('path: /api/v1/posts');
    });

    it('generates files with simple endpoint preset', function (): void {
        $exitCode = Artisan::call('make:action-request', [
            'model' => 'User',
            'name' => 'Create',
            'version' => 'v1',
            '--preset' => 'simple-endpoint',
        ]);

        expect($exitCode)->toBe(0);

        $actionFile = app_path('Actions/v1/User/CreateUserAction.php');
        $requestFile = app_path('Http/Requests/v1/User/CreateUserRequest.php');
        $testFile = base_path('tests/Feature/Http/Actions/v1/User/CreateUserActionTest.php');

        expect(file_exists($actionFile))->toBeTrue();
        expect(file_exists($requestFile))->toBeTrue();
        expect(file_exists($testFile))->toBeTrue();

        // Verify simple preset content
        $actionContent = file_get_contents($actionFile);
        expect($actionContent)->toContain('Simple Endpoint');
        expect($actionContent)->toContain('handle(array $data): array');
        expect($actionContent)->toContain('$this->handle($request->validated());');

        $requestContent = file_get_contents($requestFile);
        expect($requestContent)->toContain("'name' => ['required', 'string', 'max:255']");

        $testContent = file_get_contents($testFile);
        expect($testContent)->toContain('Simple Endpoint');
        expect($testContent)->toContain('returns 422 for validation errors');
    });

    it('generates action with resource collection return type', function (): void {
        Artisan::call('make:action-request', [
            'model' => 'Article',
            'name' => 'List',
            'version' => 'v2',
            '--resource' => 'collection',
        ]);

        $actionFile = app_path('Actions/v2/Article/ListArticleAction.php');
        $actionContent = file_get_contents($actionFile);

        expect($actionContent)->toContain('use Illuminate\Http\Resources\Json\ResourceCollection;');
        expect($actionContent)->toContain('@return ResourceCollection<array<string, mixed>>');
        expect($actionContent)->toContain('): ResourceCollection');
    });

    it('generates action with resource return type', function (): void {
        Artisan::call('make:action-request', [
            'model' => 'Product',
            'name' => 'Show',
            'version' => 'v1',
            '--resource' => 'resource',
        ]);

        $actionFile = app_path('Actions/v1/Product/ShowProductAction.php');
        $actionContent = file_get_contents($actionFile);

        expect($actionContent)->toContain('use Illuminate\Http\Resources\Json\JsonResource;');
        expect($actionContent)->toContain('@return JsonResource');
        expect($actionContent)->toContain('): JsonResource');
    });

    it('honors custom paths from config', function (): void {
        config(['action-request.actions_path' => 'app/Domain/Actions']);
        config(['action-request.requests_path' => 'app/Domain/Requests']);
        config(['action-request.tests_path' => 'tests/Domain/Actions']);

        Artisan::call('make:action-request', [
            'model' => 'Order',
            'name' => 'Process',
            'version' => 'v1',
        ]);

        $actionFile = app_path('Domain/Actions/v1/Order/ProcessOrderAction.php');
        $requestFile = app_path('Domain/Requests/v1/Order/ProcessOrderRequest.php');
        $testFile = base_path('tests/Domain/Actions/v1/Order/ProcessOrderActionTest.php');

        expect(file_exists($actionFile))->toBeTrue();
        expect(file_exists($requestFile))->toBeTrue();
        expect(file_exists($testFile))->toBeTrue();
    });

    it('honors custom namespace from config', function (): void {
        config(['action-request.namespace' => 'Domain\\']);

        Artisan::call('make:action-request', [
            'model' => 'Invoice',
            'name' => 'Generate',
            'version' => 'v1',
        ]);

        $actionFile = app_path('Actions/v1/Invoice/GenerateInvoiceAction.php');
        $actionContent = file_get_contents($actionFile);

        expect($actionContent)->toContain('namespace Domain\Actions\v1\Invoice;');
    });

    it('creates directories recursively', function (): void {
        Artisan::call('make:action-request', [
            'model' => 'DeepNested',
            'name' => 'Test',
            'version' => 'v3',
        ]);

        $actionFile = app_path('Actions/v3/DeepNested/TestDeepNestedAction.php');

        expect(file_exists($actionFile))->toBeTrue();
    });
});

function cleanupTestFiles(): void
{
    $paths = [
        app_path('Actions'),
        app_path('Domain'),
        app_path('Http/Requests/v1'),
        app_path('Http/Requests/v2'),
        app_path('Http/Requests/v3'),
        base_path('tests/Feature/Http/Actions'),
        base_path('tests/Domain'),
    ];

    $filesystem = new Filesystem;

    foreach ($paths as $path) {
        if ($filesystem->isDirectory($path)) {
            $filesystem->deleteDirectory($path);
        }
    }
}
