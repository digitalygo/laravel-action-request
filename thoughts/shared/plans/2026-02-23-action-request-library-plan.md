---
status: draft
created_at: 2026-02-23
rationale: Piano per estrarre il comando make:action-request in libreria Composer installabile, con flusso action-first, stubs pubblicabili e suite di test.
supporting_docs:
  - thoughts/shared/operations/2026-02-23-action-request-pattern.md
  - thoughts/shared/operations/2026-02-23-contributing-workflow-summary.md
---

# Piano – Libreria Composer "Action Request"

## (estrazione comando `make:action-request`)

## Obiettivi e ambito

- Rendere disponibile come pacchetto Composer un comando Artisan che generi trio
  **Action + Form Request + Test Pest** per architettura action-first
  (lorisleiva/actions), mantenendo naming/versioning e placeholder documentati.
- Consentire override di stub e configurazioni (namespace, percorsi output,
  versione API di default).
- Fornire test automatizzati (unit/integration) e istruzioni di qualità
  (Pint, coverage ≥90%).

## Presupposti

- Target stack: PHP ^8.4, Laravel ^12.0, lorisleiva/laravel-actions ^2.9,
  Pest ^4.2 (rif. operations doc).
- Uso SemVer; prima release prevista `v1.0.0`.
- Applicazioni host usano architettura versionata (`v1`, `v2`, …) per cartelle
  Actions/Requests/Tests.

## Piano di implementazione

1. **Bootstrap pacchetto**
   - Creare struttura `src/`, `stubs/`, `config/`, `tests/`, `composer.json`
     (`type: library`, autoload PSR-4 `Diggo\\ActionRequest\\`).
   - Aggiungere Service Provider che registra il comando e pubblica config/stub
     (`php artisan vendor:publish --tag=action-request-config|stubs`).
   - Impostare `extra.laravel.providers` nel composer.json per auto-discovery.

2. **Porting comando**
   - Spostare `CreateNewActionWithValidation` in `src/Console/Commands/`
     (namespace pacchetto).
   - Mantenere firma: `make:action-request {model} {name} {version=v1}`.
   - Estrarre percorsi/namespace in config con fallback agli attuali: Actions in
     `app/Actions/{version}/{Model}`, Requests in
     `app/Http/Requests/{version}/{Model}`, Tests in
     `tests/Feature/Http/Actions/{version}/{Model}`.
   - Mantenere `addRequest()` per sostituire `{{ requestClassWithNamespace }}`
     e `{{ requestClass }}` nello stub.

3. **Stub**
   - Posizionare `stubs/action.stub` e `stubs/request.stub` nel pacchetto,
     mantenendo i placeholder.
   - Opzione config per scegliere tipo di risposta (`Resource` vs
     `ResourceCollection`) o commento guida.
   - Documentare convenzione: in `asController` la Request è tipizzata e viene
     restituita una Resource/ResourceCollection.

4. **Config pubblicabile**
   - File `config/action-request.php` con chiavi: `namespace` base (App\\),
     `actions_path`, `requests_path`, `tests_path`, `default_version`,
     `response_type` (ResourceCollection/Resource), toggle `--pest`.
   - Testare override: dopo publish, il comando legge dal config dell'app host.

5. **Documentazione pacchetto**
   - README: installazione, publish, uso del comando con esempi, output atteso,
     principi da workflow (no codice commentato, action-first, coverage ≥90%).
   - CHANGELOG con SemVer; licenza esplicita.

6. **Distribuzione Composer**
   - `composer validate` e `composer dump-autoload -o` pre-release.
   - Tag `v1.0.0`; opzionale `branch-alias` `dev-main` → `1.x-dev`.

## Strategia di test (Pest)

### Unit

- Costruzione dei nomi/namespace e sostituzione placeholder negli stub.
- Lettura config override (percorsi, namespace, response_type) e fallback ai
  default.
- Registrazione comando via service provider e presenza in `artisan list`
  (mock di app container).
- Publish dei file (config/stubs) produce i percorsi attesi.

### Integrazione (artisan command end-to-end)

- Eseguire `php artisan make:action-request Post Store v1` in un’app di test:
  - Genera Action, Form Request, Test Pest nei percorsi configurati.
  - Contenuto Action: `use AsAction`, Request tipizzata in `asController`,
    return type coerente.
  - Contenuto Request: `authorize` e `rules` presenti.
  - Test Pest creato con namespace coerente.
- Variante con `version` custom (es. `v2`).
- Variante con config override di percorsi/cartelle.
- Dopo `vendor:publish --tag=action-request-stubs`, il comando usa gli stub
  pubblicati (verifica modifiche propagate).

### Qualità e compatibilità

- Pint `--test` sugli stub generati (almeno smoke: PHP valido).
- Snapshot/golden files per i tre artefatti generati, per rilevare regressioni
  nei placeholder.
- Matrix (CI) su PHP 8.4 / Laravel 12 / lorisleiva-actions 2.9; estendere se si
  decide di supportare versioni aggiuntive.

## Miglioramenti proposti al flusso/libreria

1. **Opzioni extra comando**: flag `--resource=collection|single` per scegliere
   tra `ResourceCollection` o `JsonResource` nel metodo `asController`.
2. **Template test potenziato**: includere già casi base di validazione e
   autorizzazione, con dataset Pest e placeholder per factory/seeder.
3. **Supporto policy opzionale**: parametro `--policy` per generare snippet di
   check `authorize` sulla Request o binding Policy nel test.
4. **Naming configurabile**: config per scegliere se includere il nome del
   dominio nel namespace dei test
   (`Tests\\Feature\\Actions\\{version}\\{model}` vs percorsi custom).
5. **Generazione risorse** (opt-in): flag `--resource` per creare uno scheletro
   di API Resource collegato all’Action (se presente `make:resource`).
6. **Linting predefinito**: comando `action-request:lint` che esegue Pint sulle
   classi generate per garantire stile PSR-12 (opzionale per non rallentare il
   comando principale).
7. **Compatibilità multi-versione**: se necessario, introdurre adattatori per
   Laravel 11 (condizionale sui namespace delle FormRequest) e test di
   compatibilità separati.
8. **Laravel Boost (third-party package skills)**: definire skill per includere
   il pacchetto via `composer require` e registrare nel manifest Boost, così da
   facilitare setup e suggerimenti automatici.
9. **Skill OpenAPI per Actions**: linee guida/skill per generare `openapi.yml`
   di ogni Action esposta via `asController`, con path versionato, schema
   request/response, security, esempi e validazione tramite `openapi-cli`.

### Dettaglio skill OpenAPI per Actions

- **Obiettivo**: standardizzare la documentazione OpenAPI per ogni Action con
  `asController`, allineata al versioning (`/api/{version}/...`).
- **Output atteso**: blocco OpenAPI in `openapi.yml` (o file modulare) con:
  - `paths`: entry `/api/{version}/{resource}` o `/api/{version}/{resource}/{id}`
    coerente con la rotta.
  - Metodo HTTP tipico dell’Action (GET/POST/PUT/DELETE/PATCH).
  - `operationId`: `{version}-{Model}-{Action}` (es. `v1-Post-Store`).
  - `summary`/`description`: derivate dal nome dell’Action.
  - `requestBody`: schema che replica le regole della Form Request (tipi,
    required, enum, pattern, min/max), con esempi.
  - `responses`: almeno `200` (o `201` per create) con schema Resource o
    ResourceCollection; `401/403/422` mappati.
  - `security`: es. `bearerAuth` se richiesto dalla rotta/policy.
- **Derivazione schema**: mappare le regole della Form Request in componenti
  `schemas` (`{Model}{Action}Request`, `{Model}{Action}Response`).
- **Esempi**: includere `examples` per request/response; usare dati minimi e
  realistici.
- **Validazione**: usare `npx @redocly/cli lint openapi.yml` (o `pnpm/yarn`),
  fallire su errori; integrare in CI.
- **Integrazione con comando** (facoltativa): opzione futura `--openapi` per
  generare uno snippet YAML basato sulle regole della Form Request, salvato in
  cartella `openapi/` o `docs/openapi/paths/`.
- **Versioning**: ogni versione API ha prefisso `vX` in path e in `operationId`
  per distinguere breaking changes.
- **Naming component**: usare PascalCase e coerenza con Resources (es.
  `{Model}{Action}Resource`, `{Model}{Action}Collection`).
- **Checklist manuale**: dopo aver completato Request/Resource, aggiornare lo
  snippet OpenAPI e rilanciare lint; includere nei PR template un box “OpenAPI
  aggiornato”.

## Rischi e mitigazioni

- Differenze percorsi host: mitigare con config pubblicabile e test di override.
- Stub personalizzati rotti: documentare i placeholder obbligatori; usare
  snapshot per rilevare rotture.
- Drift versioni Laravel/Actions: limitare il range di require e mantenere CI su
  versioni supportate; usare fallback di feature in base alla versione.

## Prossimi passi

- Allineare requisiti di compatibilità (solo Laravel 12 o anche 11?).
- Stabilire naming definitivo del pacchetto e licenza.
- Procedere con l’implementazione in un nuovo repo Git seguendo il piano e
  aggiungendo la CI.

## Decisione su dipendenza lorisleiva/laravel-actions

- Manteniamo la dipendenza completa (`lorisleiva/laravel-actions`) per usare
  anche job, listener, command, fake e auto-discovery via ActionManager/Lody.
- Evitiamo fork/estrazioni parziali: beneficeremo di compatibilità Laravel 10–12
  e degli aggiornamenti upstream su decorator e fasi di boot.

### Nota su gestione dipendenza

- `lorisleiva/laravel-actions` sarà in `require` del pacchetto (dipendenza
  transitiva), non un requisito manuale dell’app host. Chi installa il pacchetto
  non dovrà aggiungerla separatamente.

## Miglioramenti mirati per endpoint semplici e atomici

1. **Preset "endpoint semplice"**: comando `--preset=simple-endpoint` che genera
   solo i 3 file (Action, Request, Test) con stub minimali e senza dipendenze
   extra.
2. **Stub Action minimale**: firma `asController(FormRequest $request): JsonResponse`
   con `handle` che ritorna array/DTO; include TODO per resource opzionale.
3. **Stub Request minimale**: solo `authorize` e `rules`, con esempio di
   validazione tipica (id, string, enum) e messaggi custom opzionali.
4. **Stub Test minimale (Pest)**: casi base successo e 422 (validation) già
   impostati; helper per payload valido/invalid; dataset esempio.
5. **Flag `--resource=none|resource|collection`**: default `none` per endpoint
   semplice; se scelto `resource`/`collection`, inserisce use della Resource e
   return typed.
6. **Output configurabile ma convenzionale**: default paths rimangono versionati
   (`app/Actions/{v}/{Model}`, `app/Http/Requests/{v}/{Model}`,
   `tests/Feature/Http/Actions/{v}/{Model}`), ma preset non chiede altro input.
7. **Guard-rails di lint/test opzionali**: flag `--with-lint` per lanciare Pint
   sui file generati; flag `--with-test` per eseguire il test generato subito
   (skippabile nei CI lenti).
8. **Naming consistente**: `operationId` suggerito nel commento stub per
   eventuale OpenAPI (`v1-{Model}-{Action}`) e placeholder per path
   `/api/{version}/{model-kebab}`.
9. **Integrazione doc rapida**: link nel README a una sezione "endpoint
   semplice" con snippet copy-paste di rotte e payload esempio.
10. **Compatibilità Boost**: skill dedicata per preset semplice che registra
    automaticamente le tre path di output e aggiunge hint in IDE.
