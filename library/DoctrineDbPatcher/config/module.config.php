<?php
if (class_exists('Doctrine\ODM\MongoDB\Mapping\Driver\AnnotationDriver')) {
    $mappingType = 'odm';
} else {
    $mappingType = 'orm';
}
$doctrineDbPatcherDriver = 'DoctrineDbPatcher_' . $mappingType . '_driver';

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
            $mappingType . '_default' => [
                'drivers' => [
                    'DoctrineDbPatcher\\' . ucfirst($mappingType) . 'Entity' => $doctrineDbPatcherDriver
                ]
            ],
            'DoctrineDbPatcher_odm_driver' => [
                'class' => 'Doctrine\ODM\MongoDB\Mapping\Driver\AnnotationDriver',
                'cache' => 'array',
                'paths' => [
                    __DIR__ . '/../src/DoctrineDbPatcher/OdmEntity'
                ]
            ],
            'DoctrineDbPatcher_orm_driver' => [
                'class' => 'Doctrine\ORM\Mapping\Driver\AnnotationDriver',
                'cache' => 'array',
                'paths' => [
                    __DIR__ . '/../src/DoctrineDbPatcher/OrmEntity'
                ]
            ]
        ],
        'mapping_type' => $mappingType
    ]
];
