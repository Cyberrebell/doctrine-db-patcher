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
    ],
    'doctrine' => [
        'driver' => [
            'odm_default' => [
                'drivers' => [
                    'DoctrineDbPatcher\Entity' => 'DoctrineDbPatcher_driver'
                ]
            ],
            'DoctrineDbPatcher_driver' => [
                'class' => 'Doctrine\ODM\MongoDB\Mapping\Driver\AnnotationDriver',
                'cache' => 'array',
                'paths' => [
                    __DIR__ . '/../src/DoctrineDbPatcher/Entity'
                ]
            ]
        ]
    ]
];
