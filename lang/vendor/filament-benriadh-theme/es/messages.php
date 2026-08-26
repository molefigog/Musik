<?php

return [
    'apps_menu' => 'Menú de aplicaciones',

    'theme_settings' => [
        'title' => 'Configuración del tema',
        'navigation' => [
            'group' => 'Configuración',
            'label' => 'Configuración del tema',
        ],
        'sections' => [
            'visual_options' => [
                'label' => 'Opciones visuales',
                'description' => 'Controla los colores, la tipografía, el diseño y el modo para este panel.',
            ],
        ],
        'fields' => [
            'preset' => [
                'label' => 'Preset',
                'helper' => 'Elige un preset visual base. Aún puedes sobrescribir los tokens.',
            ],
            'theme_mode' => [
                'label' => 'Modo de color',
                'helper' => 'Automático sigue las preferencias del sistema, o fuerza claro/oscuro.',
            ],
            'primary_color_preset' => [
                'label' => 'Color primario',
                'helper' => 'Elige un color predefinido o selecciona "Personalizado" para usar el tuyo.',
            ],
            'accent_color' => [
                'label' => 'Color primario personalizado',
                'helper' => 'Se usa cuando el color primario está configurado como "Personalizado".',
            ],
            'font_family' => [
                'label' => 'Familia tipográfica',
                'helper' => 'Elige una de las Google Fonts compatibles.',
            ],
            'base_font_size' => [
                'label' => 'Tamaño de fuente base',
                'helper' => 'Ajusta el tamaño global de fuente entre 12px y 20px.',
            ],
            'app_name' => [
                'label' => 'Nombre de la aplicación',
                'helper' => 'Se muestra en el área del logotipo del panel y en el título de la página.',
            ],
            'logo_url' => [
                'label' => 'Logotipo claro',
                'helper' => 'Sube el logotipo usado en modo claro.',
            ],
            'dark_logo_url' => [
                'label' => 'Logotipo oscuro',
                'helper' => 'Sube el logotipo usado en modo oscuro.',
            ],
            'logo_height' => [
                'label' => 'Tamaño del logotipo',
                'helper' => 'Controla la altura del logotipo de 24px a 96px.',
            ],
            'navigation_layout' => [
                'label' => 'Diseño',
                'helper' => 'Cambia entre barra lateral, barra lateral compacta, barra superior o solo menú desplegable.',
            ],
            'show_mode_switcher' => [
                'label' => 'Mostrar selector de modo',
                'helper' => 'Muestra el selector oscuro/claro/sistema en el menú de usuario.',
            ],
            'show_apps_dropdown' => [
                'label' => 'Mostrar menú de aplicaciones',
                'helper' => 'Muestra el menú desplegable de aplicaciones en la barra superior cuando la barra lateral está oculta.',
            ],
        ],
        'layouts' => [
            'sidebar' => 'Barra lateral',
            'compact_sidebar' => 'Barra lateral compacta',
            'topbar' => 'Barra superior',
            'dropdown' => 'Solo menú desplegable',
        ],
        'primary_colors' => [
            'blue' => 'Azul',
            'green' => 'Verde',
            'amber' => 'Ámbar',
            'orange' => 'Naranja',
            'red' => 'Rojo',
            'violet' => 'Violeta',
            'custom' => 'Personalizado',
        ],
        'modes' => [
            'auto' => 'Automático',
            'light' => 'Claro',
            'dark' => 'Oscuro',
        ],
        'actions' => [
            'save' => 'Guardar configuración',
            'reset' => 'Restablecer todo',
            'reset_confirm_title' => '¿Restablecer todas las personalizaciones?',
            'reset_confirm_body' => 'Esto reemplazará tu configuración actual del tema con los valores predeterminados.',
        ],
        'notifications' => [
            'table_missing' => [
                'title' => 'La tabla de configuración del tema no existe.',
                'body' => 'Ejecuta las migraciones para habilitar la persistencia de la configuración del tema.',
            ],
            'saved' => [
                'title' => 'Configuración del tema guardada.',
            ],
            'reset' => [
                'title' => 'Configuración del tema restablecida a los valores predeterminados.',
            ],
        ],
    ],
];
