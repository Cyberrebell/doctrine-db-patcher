<?php
return [
    'console' => [
        'router' => [
            'routes' => [
                'doctrinedbpatcher' => [
                    'options' => [
                        'route'    => 'dbpatch',
                        'defaults' => [
                            'controller' => 'DoctrineDbPatcher\Controller\DoctrineDbPatch',
                            'action'     => 'dbpatch'
                        ]
                    ]
                ]
            ]
        ]
    ],
    'controllers'     => [
        'invokables' => [
            'DoctrineDbPatcher\Controller\DoctrineDbPatch' => 'DoctrineDbPatcher\Controller\DoctrineDbPatchController'
        ],
    ]
];
