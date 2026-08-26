<?php

return [
    'apps_menu' => 'Menu de aplicações',

    'theme_settings' => [
        'title' => 'Configurações do tema',
        'navigation' => [
            'group' => 'Configurações',
            'label' => 'Configurações do tema',
        ],
        'sections' => [
            'visual_options' => [
                'label' => 'Opções visuais',
                'description' => 'Controle as cores, a tipografia, o layout e o modo deste painel.',
            ],
        ],
        'fields' => [
            'preset' => [
                'label' => 'Predefinição',
                'helper' => 'Escolha uma predefinição visual base. Você ainda pode substituir os tokens.',
            ],
            'theme_mode' => [
                'label' => 'Modo de cor',
                'helper' => 'Automático segue as preferências do sistema, ou force claro/escuro.',
            ],
            'primary_color_preset' => [
                'label' => 'Cor primária',
                'helper' => 'Escolha uma cor predefinida ou selecione "Personalizado" para usar a sua.',
            ],
            'accent_color' => [
                'label' => 'Cor primária personalizada',
                'helper' => 'Usada quando a cor primária está definida como "Personalizado".',
            ],
            'font_family' => [
                'label' => 'Família tipográfica',
                'helper' => 'Escolha uma das Google Fonts compatíveis.',
            ],
            'base_font_size' => [
                'label' => 'Tamanho de fonte base',
                'helper' => 'Ajuste o tamanho global da fonte entre 12px e 20px.',
            ],
            'app_name' => [
                'label' => 'Nome da aplicação',
                'helper' => 'Exibido na área do logotipo do painel e no título da página.',
            ],
            'logo_url' => [
                'label' => 'Logotipo claro',
                'helper' => 'Carregue o logotipo usado no modo claro.',
            ],
            'dark_logo_url' => [
                'label' => 'Logotipo escuro',
                'helper' => 'Carregue o logotipo usado no modo escuro.',
            ],
            'logo_height' => [
                'label' => 'Tamanho do logotipo',
                'helper' => 'Controle a altura do logotipo de 24px a 96px.',
            ],
            'navigation_layout' => [
                'label' => 'Layout',
                'helper' => 'Alterne entre barra lateral, barra lateral compacta, barra superior ou apenas menu suspenso.',
            ],
            'show_mode_switcher' => [
                'label' => 'Mostrar seletor de modo',
                'helper' => 'Exibe o seletor escuro/claro/sistema no menu do usuário.',
            ],
            'show_apps_dropdown' => [
                'label' => 'Mostrar menu de aplicações',
                'helper' => 'Exibe o menu suspenso de aplicações na barra superior quando a barra lateral está oculta.',
            ],
        ],
        'layouts' => [
            'sidebar' => 'Barra lateral',
            'compact_sidebar' => 'Barra lateral compacta',
            'topbar' => 'Barra superior',
            'dropdown' => 'Apenas menu suspenso',
        ],
        'primary_colors' => [
            'blue' => 'Azul',
            'green' => 'Verde',
            'amber' => 'Âmbar',
            'orange' => 'Laranja',
            'red' => 'Vermelho',
            'violet' => 'Violeta',
            'custom' => 'Personalizado',
        ],
        'modes' => [
            'auto' => 'Automático',
            'light' => 'Claro',
            'dark' => 'Escuro',
        ],
        'actions' => [
            'save' => 'Guardar configurações',
            'reset' => 'Repor tudo',
            'reset_confirm_title' => 'Repor todas as personalizações?',
            'reset_confirm_body' => 'As configurações atuais do tema serão substituídas pelos valores padrão.',
        ],
        'notifications' => [
            'table_missing' => [
                'title' => 'A tabela de configurações do tema não existe.',
                'body' => 'Execute as migrações para ativar a persistência das configurações do tema.',
            ],
            'saved' => [
                'title' => 'Configurações do tema guardadas.',
            ],
            'reset' => [
                'title' => 'Configurações do tema repostas para os valores padrão.',
            ],
        ],
    ],
];
