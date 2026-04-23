---
status: completed
created_at: 2026-02-23
files_edited:
  - app/Console/Commands/CreateNewActionWithValidation.php
  - stubs/action.stub
  - stubs/request.stub
rationale: Documentazione del flusso make:action-request per estrazione in libreria composer.
supporting_docs: []
---

# Pattern "Request Action Test" (comando `make:action-request`)

## Sintesi

Comando artigianale che genera trio **Action + Form Request + Test Pest** per un modello/versione. È pensato per l'architettura Action-first (lorisleiva/actions) e per assicurare convalidazione e copertura di test da subito.

## Comando

```bash
php artisan make:action-request {model} {name} {version=v1}
```

- `model`: nome del dominio (cartella) sotto `app/Actions/{version}/{Model}` e `app/Http/Requests/{version}/{Model}`.
- `name`: nome specifico dell'Action (es. `Index`, `Store`).
- `version` (default `v1`): versione API e namespace.

## Flusso interno (CreateNewActionWithValidation)

File: `app/Console/Commands/CreateNewActionWithValidation.php`

1. Legge `model`, `name`, `version` dagli argomenti.
2. `parent::handle()` crea l'**Action** usando lo stub personalizzato (`stubs/action.stub`).
3. `createFormRequests()` invoca `make:request` -> genera **Form Request** namespaced `App\Http\Requests\{version}\{Model}\{Name}Request`.
4. `createTest()` invoca `make:test --pest` -> genera **Feature test Pest** in `tests/Feature/Http/Actions/{version}/{Model}/{Name}Test.php`.
5. Durante la build dell'Action, il metodo `addRequest()` sostituisce i placeholder dello stub con la `Request` generata, assicurando tipizzazione in `asController()`.

## Stub generati

### Action (`stubs/action.stub`)

```php
<?php

namespace {{ namespace }};

use {{ requestClassWithNamespace }};
use Lorisleiva\Actions\Concerns\AsAction;
use Illuminate\Http\Resources\Json\ResourceCollection;

class {{ class }}
{
    use AsAction;

    public function handle(
        // ...
    )
    {
        // ...
    }

    public function asController({{ requestClass }} $request): ResourceCollection
    {
        $this->handle(
            // ...
        );

        return  ; // always return a response with a Resource
    }
}
```

### Form Request (`stubs/request.stub`)

```php
<?php

namespace {{ namespace }};

use Illuminate\Foundation\Http\FormRequest;

class {{ class }} extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            // insert rules
        ];
    }
}
```

## Percorsi risultanti (output atteso)

- Action: `app/Actions/{version}/{Model}/{Name}.php`
- Form Request: `app/Http/Requests/{version}/{Model}/{Name}Request.php`
- Test Pest: `tests/Feature/Http/Actions/{version}/{Model}/{Name}Test.php`

## Note operative per la futura libreria

- Conservare i placeholder per il binding della Request ({{ requestClassWithNamespace }} e {{ requestClass }}).
- Iniettare sempre la Request tipizzata in `asController()` e restituire una `ResourceCollection` (o Resource) come da stub.
- Ampliare lo stub `handle()` con dipendenze (modello, servizi) e applicare validazione/autorizzazione nella Form Request.
- Prevedere nel test generato i casi di successo, autorizzazione e validazione; estendere le expectation rispetto allo stub Pest di default.
- Mantenere l'architettura versionata (cartelle `v1`, `v2`, ecc.) per facilitare breaking changes.

## Codice sorgente del comando

File: `app/Console/Commands/CreateNewActionWithValidation.php`

```php
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Console\GeneratorCommand;

class CreateNewActionWithValidation extends GeneratorCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:action-request {model : The name of the model} {name : The name of the action} {version=v1 : The version of the action (optional) default: v1}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new action with validation request';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $model = $this->argument('model');
        $action = $this->argument('name');
        $version = $this->argument('version');
        parent::handle();
        $this->createFormRequests($model, $action, $version);
        $this->createTest($model, $action, $version);
    }

    public function addRequest(string $content): string
    {
        $requestNamespaced = 'App\\Http\\Requests\\'.$this->argument('version').'\\'.$this->argument('model').'\\'.$this->argument('name').'Request';
        $requestClass = $this->argument('name').'Request';

        return str_replace(
            [
                '{{ requestClassWithNamespace }}',
                '{{ requestClass }}',
            ],
            [
                $requestNamespaced,
                $requestClass,
            ],
            $content
        );

    }

    protected function buildClass($name): string
    {
        return $this->addRequest(parent::buildClass($name));
    }

    protected function getStub(): string
    {
        return $this->laravel->basePath('/stubs/action.stub');
    }

    protected function getDefaultNamespace($rootNamespace): string
    {
        return $rootNamespace.'\\Actions\\'.$this->argument('version').'\\'.$this->argument('model');
    }

    protected function createFormRequests(string $model, string $action, string $version): void
    {
        $this->call('make:request', [
            'name' => "$version\\$model\\$action".'Request',
        ]);
    }

    protected function createTest(string $model, string $action, string $version): void
    {
        $this->call('make:test', [
            '--pest',
            'name' => "Tests\\Feature\\Http\\Actions\\$version\\$model\\$action".'Test',
        ]);
    }
}
```

## Stub completi

### action.stub

```php
<?php

namespace {{ namespace }};

use {{ requestClassWithNamespace }};
use Lorisleiva\Actions\Concerns\AsAction;
use Illuminate\Http\Resources\Json\ResourceCollection;

class {{ class }}
{
    use AsAction;

    public function handle(
        // ...
    )
    {
        // ...
    }

    public function asController({{ requestClass }} $request): ResourceCollection
    {
        $this->handle(
            // ...
        );

        return  ; // always return a response with a Resource
    }
}
```

### request.stub

```php
<?php

namespace {{ namespace }};

use Illuminate\Foundation\Http\FormRequest;

class {{ class }} extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            // insert rules
        ];
    }
}
```

## Dipendenze principali (composer.json)

### require

- php ^8.4
- laravel/framework ^12.0
- lorisleiva/laravel-actions ^2.9

### require-dev

- brianium/paratest ^7.16
- fakerphp/faker ^1.23
- jasonmccreary/laravel-test-assertions ^2.8
- laravel/boost ^2.1
- laravel/pint ^1.24
- pestphp/pest ^4.2
