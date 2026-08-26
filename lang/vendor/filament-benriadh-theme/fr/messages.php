<?php

return [
    'apps_menu' => 'Menu des applications',

    'theme_settings' => [
        'title' => 'Paramètres du thème',
        'navigation' => [
            'group' => 'Paramètres',
            'label' => 'Paramètres du thème',
        ],
        'sections' => [
            'visual_options' => [
                'label' => 'Options visuelles',
                'description' => 'Contrôlez les couleurs, la typographie, la mise en page et le mode pour ce panel.',
            ],
        ],
        'fields' => [
            'preset' => [
                'label' => 'Preset',
                'helper' => 'Choisissez un preset visuel de base. Vous pouvez toujours surcharger les tokens.',
            ],
            'theme_mode' => [
                'label' => 'Mode de couleur',
                'helper' => 'Auto suit le système, ou force clair/sombre.',
            ],
            'primary_color_preset' => [
                'label' => 'Couleur principale',
                'helper' => 'Choisissez une couleur prédéfinie ou Custom pour une couleur libre.',
            ],
            'accent_color' => [
                'label' => 'Couleur principale personnalisée',
                'helper' => 'Utilisée lorsque la couleur principale est sur Custom.',
            ],
            'font_family' => [
                'label' => 'Famille de police',
                'helper' => 'Choisissez une police Google prise en charge.',
            ],
            'base_font_size' => [
                'label' => 'Taille de police de base',
                'helper' => 'Ajustez la taille globale entre 12px et 20px.',
            ],
            'app_name' => [
                'label' => "Nom de l'application",
                'helper' => 'Affiché dans la zone logo du panel et dans le titre de page.',
            ],
            'logo_url' => [
                'label' => 'Logo clair',
                'helper' => 'Téléversez le logo utilisé en mode clair.',
            ],
            'dark_logo_url' => [
                'label' => 'Logo sombre',
                'helper' => 'Téléversez le logo utilisé en mode sombre.',
            ],
            'logo_height' => [
                'label' => 'Taille du logo',
                'helper' => 'Contrôlez la hauteur du logo entre 24px et 96px.',
            ],
            'navigation_layout' => [
                'label' => 'Mise en page',
                'helper' => 'Basculez entre sidebar, sidebar compacte, topbar ou navigation uniquement par dropdown.',
            ],
            'show_mode_switcher' => [
                'label' => 'Afficher le sélecteur de mode',
                'helper' => 'Affiche le sélecteur sombre/clair/système dans le menu utilisateur.',
            ],
            'show_apps_dropdown' => [
                'label' => 'Afficher le menu des applications',
                'helper' => "Affiche le menu d'applications dans la topbar quand la sidebar est masquée.",
            ],
        ],
        'layouts' => [
            'sidebar' => 'Barre latérale',
            'compact_sidebar' => 'Barre latérale compacte',
            'topbar' => 'Barre supérieure',
            'dropdown' => 'Menu déroulant uniquement',
        ],
        'primary_colors' => [
            'blue' => 'Bleu',
            'green' => 'Vert',
            'amber' => 'Ambre',
            'orange' => 'Orange',
            'red' => 'Rouge',
            'violet' => 'Violet',
            'custom' => 'Personnalisé',
        ],
        'modes' => [
            'auto' => 'Auto',
            'light' => 'Clair',
            'dark' => 'Sombre',
        ],
        'actions' => [
            'save' => 'Enregistrer les paramètres',
            'reset' => 'Tout réinitialiser',
            'reset_confirm_title' => 'Réinitialiser toutes les personnalisations ?',
            'reset_confirm_body' => 'Vos paramètres actuels seront remplacés par les valeurs par défaut.',
        ],
        'notifications' => [
            'table_missing' => [
                'title' => 'La table des paramètres du thème est introuvable.',
                'body' => 'Exécutez les migrations pour activer la persistance des paramètres du thème.',
            ],
            'saved' => [
                'title' => 'Paramètres du thème enregistrés.',
            ],
            'reset' => [
                'title' => 'Paramètres du thème réinitialisés par défaut.',
            ],
        ],
    ],
];
