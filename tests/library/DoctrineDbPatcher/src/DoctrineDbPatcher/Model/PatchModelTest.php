<?php
namespace DoctrineDbPatcher\Model;

require_once 'library/DoctrineDbPatcher/src/DoctrineDbPatcher/Model/PatchModel.php';
require_once 'library/DoctrineDbPatcher/src/DoctrineDbPatcher/Entity/DbVersion.php';

class PatchModelTest extends \PHPUnit_Framework_TestCase
{
    protected $patchModel;
    
    function setup() {
        $serviceLocator = $this->getMock('Zend\ServiceManager\ServiceLocatorInterface', ['get']);
        $serviceLocator->expects($this->at(0))->method('get')->will($this->returnValue($this->getPatcherConfig()));
        $repo = $this->getMock('Repo', ['findOneBy']);
        $om = $this->getMock('ObjectManager', ['getRepository', 'persist', 'flush']);
        $om->expects($this->any())->method('getRepository')->will($this->returnValue($repo));
        $serviceLocator->expects($this->at(1))->method('get')->will($this->returnValue($om));
        $this->patchModel = new PatchModel($serviceLocator);
    }
    
    function testGetVersion() {
        $this->assertEquals($this->patchModel->getVersion(), '0.0.0');
    }
    
    function testPatchToVersion() {
//         $this->patchModel->patchToVersion();
    }
    
    protected function getPatcherConfig() {
        return [
            'DoctrineDbPatcher' => [
                'doctrine-objectmanager-service' => 'Doctrine\ODM\Mongo\DocumentManager',
                'patches' => [
                    '1.0.0' => [
                        'insert' => [
                            'User\Entity\DbVersion' => [
                                'attributes' => ['version'],
                                'values' => [
                                    ['0.0.1'],
                                ]
                            ],                    
                        ]
                    ]
                ]
            ]
        ];
    }
}
