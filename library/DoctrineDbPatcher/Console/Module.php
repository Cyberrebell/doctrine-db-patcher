<?php
namespace DoctrineDbPatcher\Console;

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

    public function onBootstrap(MvcEvent $e)
    {
        $application   = $e->getApplication();
        $sm            = $application->getServiceManager();
        $sharedManager = $application->getEventManager()->getSharedManager();
    }

    public function getConsoleUsage(AdapterInterface $console){
        return [
            'cmd db-init [Options]' => 'Pushes required data into DB',

            ['--noclear', 'do not clear DB first']
        ];
    }
}
