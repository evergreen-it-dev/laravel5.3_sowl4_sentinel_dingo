<?php

use SleepingOwl\Admin\Navigation\Page;

return [
    [
        'title' => 'Dashboard',
        'icon'  => 'fa fa-dashboard',
        'url'   => route('admin.dashboard'),
    ],

    [
        'title' => 'Пользователи',
        'icon' => 'fa fa-group',
        'pages' => [
            [
                'title' => 'Пользователи',
                'icon'  => 'fa fa-group',
                'url'   => 'admin/users',
            ],
            [
                'title' => 'Роли',
                'icon'  => 'fa fa-graduation-cap',
                'url'   => 'admin/roles',
            ],
            [
                'title' => 'Права',
                'icon'  => 'fa fa-key',
                'url'   => 'admin/permits',
            ],
        ]
    ],

    [
        'title' => 'Выйти',
        'icon'  => 'fa fa-sign-out',
        'url'   => '/logout',
    ],

];