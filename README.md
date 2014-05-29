##doctrine-db-patcher
Gives your ZF2+Doctrine-Project a toolset for database-patches.

####Warning: Currently only ODM(MongoDB)-Support. ORM(MySQL) Maybe comming soon...

===================

###DoctrineDbPatcher

####Requirents:
php >=5.4

ZF2: https://github.com/zendframework/zf2

DoctrineMongoODMModule: https://github.com/doctrine/DoctrineMongoODMModule

####Installation via composer:
add to composer.json:
```sh
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/Cyberrebell/doctrine-db-patcher"
        }
    ],
    "require": {
        "Cyberrebell/doctrine-db-patcher": "dev-master"
    },
```


####Configuration in project:
add to application.config.php:
```sh
return [
    'modules' => [
        'DoctrineDbPatcher'
    ]
];
```


add to any module.config.php:
```sh
return [
    'DoctrineDbPatcher' => [
        'doctrine-objectmanager-service' => 'Doctrine\ODM\Mongo\DocumentManager',
        'patches' => include 'doctrine.patches.php'
    ]
];
```


create doctrine.patches.php and add your patches like this:
```sh
<?php 
return [
    '1.0.0' => [
        'insert' => [
            'User\Entity\Resource' => [
                'attributes' => ['route'],
                'values' => [
                    ['login'],
                    ['logout'],
                ]
            ],
            'User\Entity\Permission' => [
                'attributes' => ['description'],
                'values' => [
                    ['Sich einloggen'],
                    ['Sich ausloggen'],
                ]
            ],
        ],
        'update' => [
             'User\Entity\Guestbook' => [
                 [
                     'attributes' => ['title'],
                     'values' => [
                         ['old' => ['This is a niec website'], 'new' => ['This is a nice website']],
                     ]
                 ],
             ],
        ],
        'delete' => [
             'User\Entity\Guestbook' => [
                 [
                     'attributes' => ['title'],
                     'values' => [
                         ['Hello everyone'],
                     ]
                 ],
             ],
        ],
        'connect' => [
            [
                'entities' => ['User\Entity\Permission' => 'User\Entity\Resource'],
                'methods' => ['addResource' => 'removeResource'],
                'targets' => [
                    ['source' => ['description' => 'Sich einloggen'], 'target' => ['route' => 'login']],
                    ['source' => ['description' => 'Sich ausloggen'], 'target' => ['route' => 'logout']],
                ]
            ]
        ]
    ]
];
```


####Configuration in project:
run the patch-tool:
```sh
php public/index.php dbpatch
```

###Future features:
-downpatching by reading the patches "backwards"
-ORM(MySQL)-Support

###Features that will not be added by me:
-Symphony-Support
