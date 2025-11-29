<?php

/**
 * Returns the importmap for this application.
 *
 * - "path" is a path inside the asset mapper system. Use the
 *     "debug:asset-map" command to see the full list of paths.
 *
 * - "entrypoint" (JavaScript only) set to true for any module that will
 *     be used as an "entrypoint" (and passed to the importmap() Twig function).
 *
 * The "importmap:require" command can be used to add new entries to this file.
 */
return [
    'app' => [
        'path' => './assets/app.js',
        'entrypoint' => true,
    ],
    // Vue 3 + Vuetify via importmap (no bundler)
    'vue' => [
        // Use the ESM browser build that includes the template compiler
        'package_specifier' => 'vue/dist/vue.esm-browser.js',
        'version' => '3.4.38',
    ],
    // Vue internal packages required by ESM runtime & template compiler
    '@vue/runtime-dom' => [
        'version' => '3.4.38',
    ],
    '@vue/runtime-core' => [
        'version' => '3.4.38',
    ],
    '@vue/reactivity' => [
        'version' => '3.4.38',
    ],
    '@vue/shared' => [
        'version' => '3.4.38',
    ],
    // If using inline templates (Vue compiles templates in the browser)
    '@vue/compiler-dom' => [
        'version' => '3.4.38',
    ],
    '@vue/compiler-core' => [
        'version' => '3.4.38',
    ],
    'vuetify' => [
        'version' => '3.7.1',
    ],
    // Vuetify component and directive entrypoints (required to register components)
    'vuetify/components' => [
        'version' => '3.7.1',
    ],
    'vuetify/directives' => [
        'version' => '3.7.1',
    ],
    'todo-app' => [
        'path' => './assets/todo-app/app.js',
        'entrypoint' => true,
    ],
    '@hotwired/stimulus' => [
        'version' => '3.2.2',
    ],
    '@symfony/stimulus-bundle' => [
        'path' => './vendor/symfony/stimulus-bundle/assets/dist/loader.js',
    ],
    '@hotwired/turbo' => [
        'version' => '7.3.0',
    ],
];
