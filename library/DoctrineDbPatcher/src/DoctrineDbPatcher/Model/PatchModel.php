<?php
namespace DoctrineDbPatcher\Model;

use Zend\ServiceManager\ServiceLocatorInterface;
class PatchModel
{
    protected $om;
    protected $patches;
    protected $version;
    
    /**
     * Construct and check config
     * @param Zend\ServiceManager\ServiceLocatorInterface $service
     */
    function __construct(ServiceLocatorInterface $service) {
        $config = $service->get('Config');
        $patcherConfig = $this->getConfigValue('DoctrineDbPatcher', $config);
        $this->om = $service->get($this->getConfigValue('doctrine-objectmanager-service', $patcherConfig));
        $this->patches = $this->getConfigValue('patches', $patcherConfig);
    }
    
    function getVersion(){
        return '1.0.0';
    }
    
    function insert(array $config) {
        
    }
    
    function update(array $config) {
        
    }
    
    function delete(array $config) {
        
    }
    
    function connect(array $config) {
        
    }
    
    protected function checkVersion() {
        
    }
    
    protected function getConfigValue($key, array $config, $required = true) {
        if (array_key_exists($key, $config)) {
            return $config[$key];
        } else if ($required) {
            echo $key . ' must be configurated in module.config!' . "\n";
            exit;
        } else {
            return false;
        }
    }
}
