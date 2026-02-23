---
name: action-request-development
description: Costruire endpoint action-first con il comando make:action-request generando Action, Form Request e test Pest.
---

# Digitalygo Action Request Development

## Quando usare questa skill

Usa questa skill quando devi creare endpoint API action-first tramite il comando
`make:action-request`, generando i tre file: Action, Form Request e test Pest.
Supporta preset "simple-endpoint" e i flag `--resource`, `--with-lint`,
`--with-test`.

## Convenzioni chiave

- Namespace base: `Digitalygo\\ActionRequest` (package) + `App\\` (app host).
- Percorsi default generati:
  `app/Actions/{version}/{Model}/{Name}Action.php`,
  `app/Http/Requests/{version}/{Model}/{Name}Request.php`,
  `tests/Feature/Http/Actions/{version}/{Model}/{Name}ActionTest.php`.
- Versioning API: argomento `{version}` (default `v1`).
- Resource flag: `--resource=none|resource|collection` imposta return type e use
  statements negli stub.
- Preset: `--preset=simple-endpoint` usa stub minimali (handle ritorna
  array/validated data, Request minimale, test minimo).
- Stub personalizzabili via publish in `stubs/action-request/`.

## Comando principale

```bash
php artisan make:action-request {model} {name} {version=v1} \
  [--preset=simple-endpoint] [--resource=none|resource|collection] \
  [--with-lint] [--with-test]
```

## Parametri & flag

- `model`: dominio (es. `Post`).
- `name`: azione (es. `Store`).
- `version`: namespace/versione API (default `v1`).
- `--preset`: `simple-endpoint` per stub minimali.
- `--resource`: `none` (array), `resource` (JsonResource),
  `collection` (ResourceCollection).
- `--with-lint`: esegue Pint sui file generati.
- `--with-test`: esegue Pest sul test generato.

## Struttura generata (default)

- Action: usa `AsAction`, `asController(FormRequest)` restituisce `JsonResponse`;
  include commenti OpenAPI (operationId/path/method).
- Request: estende `FormRequest`, `authorize()` true, `rules()` da completare
  (preset semplice include regola `name`).
- Test Pest: dataset per success/validation; preset semplice copre 200 e 422.

## Percorsi e namespace configurabili

- Config pubblicabile `config/action-request.php`: `namespace`, `actions_path`,
  `requests_path`, `tests_path`, `default_version`, `response_type`,
  `preset_default`, flag `with_lint`, `with_test`.
- Il comando legge prima la config, poi i flag CLI.

## Best practice d'uso

- Popola le `rules()` nella Form Request; non lasciare TODO nei PR.
- Per Resource/Collection, aggiungi le API Resources e aggiorna `return`
  dell'Action.
- Aggiorna lo snippet OpenAPI nei commenti con schema request/response e status.
- Esegui `--with-lint` e `--with-test` per feedback immediato.

## Esempi

```bash
# Endpoint standard con return array
php artisan make:action-request Post Store v1

# Endpoint con JsonResource
php artisan make:action-request Post Show v1 --resource=resource

# Endpoint semplice minimale
php artisan make:action-request User Create v1 --preset=simple-endpoint

# Con lint e test immediati
php artisan make:action-request Order Process v1 --with-lint --with-test
```

## Note di integrazione

- Pubblica stubs/config se vuoi override: `php artisan vendor:publish
  --tag=action-request-stubs` e `--tag=action-request-config`.
- Dipendenze richieste dal pacchetto: `digitalygo/laravel-action-request`
  (include `lorisleiva/laravel-actions`, `laravel/boost`).
