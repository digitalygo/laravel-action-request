---
status: completed
created_at: 2026-02-23
files_edited:
  - .github/CONTRIBUTING.md
rationale: Sintesi del workflow contributivo da riusare nella libreria composer.
supporting_docs: []
---

# Workflow di contributo (estratto da .github/CONTRIBUTING.md)

## Principi chiave

- **Nessun commento nel codice PHP** (niente codice commentato, debug temporanei, TODO/HACK; eccezioni solo per config/doc). Migliorare nomi/struttura invece di spiegare con commenti.
- **Action-first**: le API puntano direttamente alle Actions (lorisleiva/actions), niente controller.
- **Crea tutto con `make:action-request`**: Action + Form Request + Test Pest sono generati solo tramite il comando custom, niente file manuali.
- **Test >= 90%** per ogni Action; usare Pest (feature/unit) e coprire successi/fallimenti.
- **PSR-1/12 + Pint**: format con Pint prima dei commit; niente modifiche agli strumenti di style.

## Setup rapido

1. Fork, branch da `main`/`develop`.
2. `composer install`, `npm install`, copia `.env`, `php artisan key:generate`, `php artisan migrate` (eventuale seed), `npm run dev`.
3. Opzionale Docker: `docker-compose up -d`, poi `composer install`, `php artisan migrate` nel container.
4. `.env` contiene solo segreti; variabili runtime in config/compose senza `env_file`.

## Flusso Action-first obbligatorio

1. Esegui `php artisan make:action-request {model} {name} {version=v1}`.
2. Registra la rotta API verso l'Action in `routes/api.php` (no controller).
3. Completa la Form Request (regole + autorizzazione via policy).
4. Aggiorna/crea API Resource per il payload.
5. Implementa l'Action (handle + asController) riusando servizi/modelli.
6. Espandi il test Pest generato per coprire tutti gli scenari (target 100%, minimo 90%).

## Stile e nomi

- Nomi descrittivi (`PascalCase` classi, `camelCase` metodi, `UPPER_SNAKE_CASE` costanti).
- Evitare N+1: eager loading.
- Controller sottili (quando esistono), logica in servizi/azioni; preferire API Resources.

## Git e PR

- Branch tematico (`feature/…`, `fix/…`, ecc.).
- Messaggi commit: Conventional Commits (`type(scope): desc`).
- Prima della PR: rebase, risolvere conflitti, rimuovere debug, aggiornare docs se cambia il comportamento, eseguire Pint, static analysis, test mirati.
- PR: descrivere cosa/ perché, breaking changes, come testare.

## Test

- Usa Pest (feature/unit); dataset per casi multipli; coprire autorizzazione/validazione/successo.
- Comandi comuni: `php artisan test`, `php artisan test --coverage`, `php artisan test --parallel`.

## Supporto

- In caso di dubbi: leggere docs/README, issue/PR simili, oppure aprire una nuova issue/discussione.
