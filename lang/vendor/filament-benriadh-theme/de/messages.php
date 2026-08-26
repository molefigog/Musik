<?php

return [
    'apps_menu' => 'App-Menü',

    'theme_settings' => [
        'title' => 'Design-Einstellungen',
        'navigation' => [
            'group' => 'Einstellungen',
            'label' => 'Design-Einstellungen',
        ],
        'sections' => [
            'visual_options' => [
                'label' => 'Visuelle Optionen',
                'description' => 'Farben, Typografie, Layout und Darstellungsmodus für dieses Panel steuern.',
            ],
        ],
        'fields' => [
            'preset' => [
                'label' => 'Voreinstellung',
                'helper' => 'Eine visuelle Voreinstellung wählen. Tokens können weiterhin überschrieben werden.',
            ],
            'theme_mode' => [
                'label' => 'Farbmodus',
                'helper' => 'Automatisch folgt den Systemeinstellungen, oder Hell/Dunkel erzwingen.',
            ],
            'primary_color_preset' => [
                'label' => 'Primärfarbe',
                'helper' => 'Eine vordefinierte Farbe wählen oder „Benutzerdefiniert" für eine eigene Farbe.',
            ],
            'accent_color' => [
                'label' => 'Benutzerdefinierte Primärfarbe',
                'helper' => 'Wird verwendet, wenn die Primärfarbe auf „Benutzerdefiniert" gesetzt ist.',
            ],
            'font_family' => [
                'label' => 'Schriftfamilie',
                'helper' => 'Eine der unterstützten Google Fonts auswählen.',
            ],
            'base_font_size' => [
                'label' => 'Basis-Schriftgröße',
                'helper' => 'Die globale Schriftgröße zwischen 12px und 20px anpassen.',
            ],
            'app_name' => [
                'label' => 'App-Name',
                'helper' => 'Wird im Logo-Bereich des Panels und im Seitentitel angezeigt.',
            ],
            'logo_url' => [
                'label' => 'Logo (Hell)',
                'helper' => 'Das Logo für den Hell-Modus hochladen.',
            ],
            'dark_logo_url' => [
                'label' => 'Logo (Dunkel)',
                'helper' => 'Das Logo für den Dunkel-Modus hochladen.',
            ],
            'logo_height' => [
                'label' => 'Logo-Größe',
                'helper' => 'Die Logo-Höhe zwischen 24px und 96px steuern.',
            ],
            'navigation_layout' => [
                'label' => 'Layout',
                'helper' => 'Zwischen Seitenleiste, kompakter Seitenleiste, Navigationsleiste oder Dropdown wechseln.',
            ],
            'show_mode_switcher' => [
                'label' => 'Moduswechsler anzeigen',
                'helper' => 'Den Dunkel-/Hell-/System-Umschalter im Benutzermenü anzeigen.',
            ],
            'show_apps_dropdown' => [
                'label' => 'App-Dropdown anzeigen',
                'helper' => 'Das App-Raster in der Navigationsleiste anzeigen, wenn die Seitenleiste ausgeblendet ist.',
            ],
        ],
        'layouts' => [
            'sidebar' => 'Seitenleiste',
            'compact_sidebar' => 'Kompakte Seitenleiste',
            'topbar' => 'Navigationsleiste',
            'dropdown' => 'Nur Dropdown',
        ],
        'primary_colors' => [
            'blue' => 'Blau',
            'green' => 'Grün',
            'amber' => 'Bernstein',
            'orange' => 'Orange',
            'red' => 'Rot',
            'violet' => 'Violett',
            'custom' => 'Benutzerdefiniert',
        ],
        'modes' => [
            'auto' => 'Automatisch',
            'light' => 'Hell',
            'dark' => 'Dunkel',
        ],
        'actions' => [
            'save' => 'Einstellungen speichern',
            'reset' => 'Alle zurücksetzen',
            'reset_confirm_title' => 'Alle Anpassungen zurücksetzen?',
            'reset_confirm_body' => 'Die aktuellen Design-Einstellungen werden durch die Standardwerte ersetzt.',
        ],
        'notifications' => [
            'table_missing' => [
                'title' => 'Die Tabelle für Design-Einstellungen fehlt.',
                'body' => 'Migrationen ausführen, um die persistente Speicherung zu aktivieren.',
            ],
            'saved' => [
                'title' => 'Design-Einstellungen gespeichert.',
            ],
            'reset' => [
                'title' => 'Design-Einstellungen auf Standard zurückgesetzt.',
            ],
        ],
    ],
];
