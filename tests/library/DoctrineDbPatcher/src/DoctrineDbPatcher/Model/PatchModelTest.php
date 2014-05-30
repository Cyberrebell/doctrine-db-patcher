<?php
namespace DoctrineDbPatcher\Model;

use DoctrineDbPatcher\Model\PatchModel;
use DoctrineDbPatcher\Entity\DbVersion;

class PatchModelTest extends \PHPUnit_Framework_TestCase
{
    protected $patchModel;
    
    function setup() {
        $serviceLocator = $this->getMock('Zend\ServiceManager\ServiceManager', ['get']);
        $serviceLocator->expects($this->at(0))->method('get')->will($this->returnValue($this->getPatcherConfig()));
        $repo = $this->getMock('Repo', ['findBy', 'findOneBy']);
        $repo->expects($this->any())->method('findBy')->will($this->returnValue([new DbVersion()]));
        $om = $this->getMock('ObjectManager', ['getRepository', 'persist', 'remove', 'flush']);
        $om->expects($this->any())->method('getRepository')->will($this->returnValue($repo));
        $serviceLocator->expects($this->at(1))->method('get')->will($this->returnValue($om));
        $this->patchModel = new PatchModel($serviceLocator);
    }
    
    function testGetVersion() {
        $this->assertEquals($this->patchModel->getVersion(), '0.0.0');
    }
    
    function testPatchToVersion() {
        $this->patchModel->patchToVersion();
    }
    
    protected function getPatcherConfig() {
        return [
            'DoctrineDbPatcher' => [
                'doctrine-objectmanager-service' => 'Doctrine\ODM\Mongo\DocumentManager',
                'patches' => [
                    '1.0.0' => [
                        'insert' => [
                            'DoctrineDbPatcher\Entity\DbVersion' => [
                                'attributes' => ['version'],
                                'values' => [
                                    ['0.0.1']
                                ]
                            ]
                        ],
                        'update' => [
                            'DoctrineDbPatcher\Entity\DbVersion' => [
                                [
                                    'attributes' => ['version'],
                                    'values' => [
                                        ['old' => ['0.0.2'], 'new' => ['0.0.4']],
                                        ['old' => ['0.0.5'], 'new' => ['0.0.3']]
                                    ]
                                ]
                            ]
                        ],
                        'delete' => [
                            'DoctrineDbPatcher\Entity\DbVersion' => [
                                [
                                    'attributes' => ['version'],
                                    'values' => [
                                        ['0.0.3']
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];
    }
}
