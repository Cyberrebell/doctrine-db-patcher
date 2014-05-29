<?php
return [
    'console' => [
        'router' => [
            'routes' => [
                'doctrinedbpatcher' => [
                    'options' => [
                        'route'    => 'dbpatch',
                        'defaults' => [
                            'controller' => 'DoctrineDbPatcher\Console\Controller\DoctrineDbPatch',
                            'action'     => 'dbpatch'
                        ]
                    ]
                ]
            ]
        ]
    ],
    'controllers'     => [
        'invokables' => [
            'DoctrineDbPatcher\Console\Controller\DoctrineDbPatch' => 'DoctrineDbPatcher\Console\Controller\DoctrineDbPatchController'
        ],
    ]
];
