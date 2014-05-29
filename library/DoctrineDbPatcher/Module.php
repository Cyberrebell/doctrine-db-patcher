<?php
namespace DoctrineDbPatcher;

use Zend\Config\Config;
use Zend\Mvc\MvcEvent;
use Zend\Console\Adapter\AdapterInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class Module
{
    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function getAutoloaderConfig()
    {
        return [
            'Zend\Loader\StandardAutoloader' => [
                'namespaces' => [
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ]
            ]
        ];
    }

    public function getConsoleUsage(AdapterInterface $console){
        return [
            'dbpatch [Options]' => 'patches the database',

            ['-v', 'just get version of database']
        ];
    }
}
