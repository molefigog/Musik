<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Schema
    |--------------------------------------------------------------------------
    */
    'schema_version' => 1,

    /*
    |--------------------------------------------------------------------------
    | Theme Asset
    |--------------------------------------------------------------------------
    */
    'asset_path' => 'vendor/filament-benriadh-theme/theme.css',

    /*
    |--------------------------------------------------------------------------
    | Color Mode
    |--------------------------------------------------------------------------
    |
    | Supported: auto, light, dark
    |
    */
    'mode' => 'auto',

    /*
    |--------------------------------------------------------------------------
    | Presets
    |--------------------------------------------------------------------------
    */
    'preset' => 'corporate',
    'presets' => [
        'corporate' => [
            'label' => 'Corporate',
            'tokens' => [
                'surface' => '#11151f',
                'surface_alt' => '#171c28',
                'text' => '#e5e7eb',
                'muted' => '#9ca3af',
                'border' => '#2a3140',
                'primary' => '#cba24c',
                'success' => '#22c55e',
                'warning' => '#f59e0b',
                'danger' => '#ef4444',
                'sidebar_from' => '#0f172a',
                'sidebar_to' => '#111827',
                'focus_ring' => '#93c5fd',
            ],
            'layout' => [
                'show_left_sidebar' => true,
                'compact_sidebar' => false,
                'card_radius' => '0.9rem',
                'soft_shadows' => true,
            ],
        ],
        'minimal' => [
            'label' => 'Minimal',
            'tokens' => [
                'surface' => '#111111',
                'surface_alt' => '#161616',
                'text' => '#f5f5f5',
                'muted' => '#a3a3a3',
                'border' => '#2b2b2b',
                'primary' => '#60a5fa',
                'success' => '#34d399',
                'warning' => '#fbbf24',
                'danger' => '#f87171',
                'sidebar_from' => '#111111',
                'sidebar_to' => '#161616',
                'focus_ring' => '#60a5fa',
            ],
            'layout' => [
                'show_left_sidebar' => true,
                'compact_sidebar' => false,
                'card_radius' => '0.75rem',
                'soft_shadows' => false,
            ],
        ],
        'bold' => [
            'label' => 'Bold',
            'tokens' => [
                'surface' => '#120f1f',
                'surface_alt' => '#1b1530',
                'text' => '#f8f7ff',
                'muted' => '#b8b4d3',
                'border' => '#3a3357',
                'primary' => '#f97316',
                'success' => '#22c55e',
                'warning' => '#facc15',
                'danger' => '#f43f5e',
                'sidebar_from' => '#1d1235',
                'sidebar_to' => '#28154f',
                'focus_ring' => '#f97316',
            ],
            'layout' => [
                'show_left_sidebar' => true,
                'compact_sidebar' => false,
                'card_radius' => '1rem',
                'soft_shadows' => true,
            ],
        ],
        'neutral' => [
            'label' => 'Neutral',
            'tokens' => [
                'surface' => '#121315',
                'surface_alt' => '#1a1c20',
                'text' => '#f3f4f6',
                'muted' => '#9ca3af',
                'border' => '#31343a',
                'primary' => '#3b82f6',
                'success' => '#16a34a',
                'warning' => '#d97706',
                'danger' => '#dc2626',
                'sidebar_from' => '#17191e',
                'sidebar_to' => '#1f2329',
                'focus_ring' => '#60a5fa',
            ],
            'layout' => [
                'show_left_sidebar' => true,
                'compact_sidebar' => false,
                'card_radius' => '0.85rem',
                'soft_shadows' => true,
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Token Overrides
    |--------------------------------------------------------------------------
    |
    | Semantic tokens used by the theme engine.
    |
    */
    'tokens' => [],

    /*
    |--------------------------------------------------------------------------
    | Layout Overrides
    |--------------------------------------------------------------------------
    */
    'layout' => [],

    /*
    |--------------------------------------------------------------------------
    | Breadcrumbs
    |--------------------------------------------------------------------------
    */
    'breadcrumbs' => [
        'enabled' => true,
        'show_home' => true,
        'show_icons' => false,
        'max_items' => 4,
        'collapse' => true,
        'style' => 'pill', // pill | minimal
        'mobile_mode' => 'compact', // compact | full-scroll
    ],

    /*
    |--------------------------------------------------------------------------
    | Branding Overrides
    |--------------------------------------------------------------------------
    */
    'branding' => [
        'app_name' => null,
        'logo_url' => null,
        'dark_logo_url' => null,
        'logo_height' => 40,
        'logo_disk' => 'public',
        'logo_directory' => 'filament-theme/logos',
    ],

    /*
    |--------------------------------------------------------------------------
    | Panel Overrides
    |--------------------------------------------------------------------------
    |
    | Example:
    | 'panel_overrides' => [
    |     'admin' => [
    |         'preset' => 'minimal',
    |         'mode' => 'dark',
    |         'tokens' => ['primary' => '#2563eb'],
    |         'layout' => ['compact_sidebar' => true],
    |     ],
    | ],
    |
    */
    'panel_overrides' => [],

    /*
    |--------------------------------------------------------------------------
    | Tenant Overrides
    |--------------------------------------------------------------------------
    |
    | Resolver must implement:
    | Benriadh1\FilamentBenriadhTheme\Contracts\TenantThemeResolver
    |
    */
    'tenant' => [
        'enabled' => false,
        'resolver' => null,
    ],

    /*
    |--------------------------------------------------------------------------
    | Extension API
    |--------------------------------------------------------------------------
    |
    | token_transformers must implement:
    | Benriadh1\FilamentBenriadhTheme\Contracts\ThemeTokenTransformer
    |
    | plugin_adapters must implement:
    | Benriadh1\FilamentBenriadhTheme\Contracts\PluginThemeAdapter
    |
    */
    'extensions' => [
        'token_transformers' => [],
        'plugin_adapters' => [
            Benriadh1\FilamentBenriadhTheme\Adapters\TranslationManagerAdapter::class,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Apps Dropdown
    |--------------------------------------------------------------------------
    |
    | Controls the apps grid shown in the topbar when the sidebar is hidden.
    |
    */
    'apps_dropdown' => [
        'max_items' => 15,
    ],

    /*
    |--------------------------------------------------------------------------
    | Accessibility
    |--------------------------------------------------------------------------
    */
    'a11y' => [
        'enforce_focus_ring' => true,
        'respect_reduced_motion' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Theme Settings Page
    |--------------------------------------------------------------------------
    */
    'show_theme_settings_page' => true,

    /*
    |--------------------------------------------------------------------------
    | Legacy Keys (Backward Compatibility)
    |--------------------------------------------------------------------------
    |
    | Keep these only to support older published config files. The resolver
    | maps them to v1 tokens/layout automatically.
    */
    'accent_color' => null,
    'sidebar_from' => null,
    'sidebar_to' => null,
    'show_left_sidebar' => null,
    'compact_sidebar' => null,
    'card_radius' => null,
    'soft_shadows' => true,
];
