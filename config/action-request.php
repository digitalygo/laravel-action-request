<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Base Namespace
    |--------------------------------------------------------------------------
    |
    | The base namespace for generated classes. This will be prepended to
    | all generated action and request class namespaces.
    |
    */
    'namespace' => 'App\\',

    /*
    |--------------------------------------------------------------------------
    | Actions Path
    |--------------------------------------------------------------------------
    |
    | The default path where action classes will be generated.
    | Relative to the application base path.
    |
    */
    'actions_path' => 'app/Actions',

    /*
    |--------------------------------------------------------------------------
    | Requests Path
    |--------------------------------------------------------------------------
    |
    | The default path where form request classes will be generated.
    | Relative to the application base path.
    |
    */
    'requests_path' => 'app/Http/Requests',

    /*
    |--------------------------------------------------------------------------
    | Tests Path
    |--------------------------------------------------------------------------
    |
    | The default path where Pest test files will be generated.
    | Relative to the application base path.
    |
    */
    'tests_path' => 'tests/Feature/Http/Actions',

    /*
    |--------------------------------------------------------------------------
    | Default Version
    |--------------------------------------------------------------------------
    |
    | The default API version to use when generating actions.
    | This can be overridden via the command argument.
    |
    */
    'default_version' => 'v1',

    /*
    |--------------------------------------------------------------------------
    | Response Type
    |--------------------------------------------------------------------------
    |
    | The default response type for generated actions.
    | Options: 'none', 'resource', 'collection'
    |
    */
    'response_type' => 'none',

    /*
    |--------------------------------------------------------------------------
    | Generate Pest Tests
    |--------------------------------------------------------------------------
    |
    | Whether to generate Pest test files by default when creating
    | actions and requests.
    |
    */
    'pest' => true,

    /*
    |--------------------------------------------------------------------------
    | Default Preset
    |--------------------------------------------------------------------------
    |
    | The default preset to use when generating files.
    | Options: null, 'simple-endpoint'
    | Set to null to use standard stubs by default.
    |
    */
    'preset_default' => null,

    /*
    |--------------------------------------------------------------------------
    | Default Flags
    |--------------------------------------------------------------------------
    |
    | Default values for command flags. These can be overridden
    | via command line options.
    |
    */
    'flags' => [
        'with_lint' => false,
        'with_test' => false,
    ],
];
