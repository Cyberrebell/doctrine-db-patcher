<?php
if (class_exists('Doctrine\ODM\MongoDB\Mapping\Driver\AnnotationDriver')) {
    $doctrineDbPatcherDriver = 'DoctrineDbPatcher_odm_driver';
} else {
    $doctrineDbPatcherDriver = 'DoctrineDbPatcher_orm_driver';
}

return [
    'console' => [
        'router' => [
            'routes' => [
                'doctrinedbpatcher' => [
                    'options' => [
                        'route'    => 'dbpatch [-v] [--down] [<version>]',
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
                    'DoctrineDbPatcher\Entity' => $doctrineDbPatcherDriver
                ]
            ],
            'DoctrineDbPatcher_odm_driver' => [
                'class' => 'Doctrine\ODM\MongoDB\Mapping\Driver\AnnotationDriver',
                'cache' => 'array',
                'paths' => [
                    __DIR__ . '/../src/DoctrineDbPatcher/Entity'
                ]
            ],
            'DoctrineDbPatcher_orm_driver' => [
                'class' => 'Doctrine\ORM\Mapping\Driver\AnnotationDriver',
                'cache' => 'array',
                'paths' => [
                    __DIR__ . '/../src/DoctrineDbPatcher/Entity'
                ]
            ]
        ]
    ]
];
