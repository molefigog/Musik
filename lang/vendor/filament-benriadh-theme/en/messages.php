<?php

return [
    'apps_menu' => 'Apps menu',

    'theme_settings' => [
        'title' => 'Theme Settings',
        'navigation' => [
            'group' => 'Settings',
            'label' => 'Theme Settings',
        ],
        'sections' => [
            'visual_options' => [
                'label' => 'Visual Options',
                'description' => 'Control colors, typography, layout, and mode behavior for this panel.',
            ],
        ],
        'fields' => [
            'preset' => [
                'label' => 'Preset',
                'helper' => 'Choose a base visual preset. You can still override tokens.',
            ],
            'theme_mode' => [
                'label' => 'Color mode',
                'helper' => 'Auto follows system preference, or force light/dark.',
            ],
            'primary_color_preset' => [
                'label' => 'Primary color',
                'helper' => 'Pick a predefined color or choose Custom to use your own color.',
            ],
            'accent_color' => [
                'label' => 'Custom primary color',
                'helper' => 'Used when Primary color is set to Custom.',
            ],
            'font_family' => [
                'label' => 'Font family',
                'helper' => 'Choose one of the supported Google Fonts.',
            ],
            'base_font_size' => [
                'label' => 'Base font size',
                'helper' => 'Adjust the global font size between 12px and 20px.',
            ],
            'app_name' => [
                'label' => 'App name',
                'helper' => 'Displayed in the panel logo area and page title.',
            ],
            'logo_url' => [
                'label' => 'Light logo',
                'helper' => 'Upload the logo used in light mode.',
            ],
            'dark_logo_url' => [
                'label' => 'Dark logo',
                'helper' => 'Upload the logo used in dark mode.',
            ],
            'logo_height' => [
                'label' => 'Logo size',
                'helper' => 'Control logo height from 24px to 96px.',
            ],
            'navigation_layout' => [
                'label' => 'Layout',
                'helper' => 'Switch between sidebar, compact sidebar, topbar, or dropdown-only navigation.',
            ],
            'show_mode_switcher' => [
                'label' => 'Show mode switcher',
                'helper' => 'Display the dark/light/system switcher in the user menu.',
            ],
            'show_apps_dropdown' => [
                'label' => 'Show apps dropdown',
                'helper' => 'Display the apps grid dropdown in the topbar when sidebar is hidden.',
            ],
        ],
        'layouts' => [
            'sidebar' => 'Sidebar',
            'compact_sidebar' => 'Compact Sidebar',
            'topbar' => 'Topbar',
            'dropdown' => 'Dropdown Only',
        ],
        'primary_colors' => [
            'blue' => 'Blue',
            'green' => 'Green',
            'amber' => 'Amber',
            'orange' => 'Orange',
            'red' => 'Red',
            'violet' => 'Violet',
            'custom' => 'Custom',
        ],
        'modes' => [
            'auto' => 'Auto',
            'light' => 'Light',
            'dark' => 'Dark',
        ],
        'actions' => [
            'save' => 'Save settings',
            'reset' => 'Reset all',
            'reset_confirm_title' => 'Reset all customizations?',
            'reset_confirm_body' => 'This will replace your current theme settings with defaults.',
        ],
        'notifications' => [
            'table_missing' => [
                'title' => 'Theme settings table is missing.',
                'body' => 'Run migrations to enable persistent theme settings.',
            ],
            'saved' => [
                'title' => 'Theme settings saved.',
            ],
            'reset' => [
                'title' => 'Theme settings reset to defaults.',
            ],
        ],
    ],
];
