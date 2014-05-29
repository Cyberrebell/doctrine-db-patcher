<?php
return [
    'console' => [
        'router' => [
            'routes' => [
                'doctrinedbpatcher' => [
                    'options' => [
                        'route'    => 'dbpatch [-v]',
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
