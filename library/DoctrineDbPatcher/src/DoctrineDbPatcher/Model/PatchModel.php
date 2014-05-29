<?php
namespace DoctrineDbPatcher\Model;

use Zend\ServiceManager\ServiceLocatorInterface;
use DoctrineDbPatcher\Entity\DbVersion;
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
        $this->version = $this->checkVersion();
    }
    
    function getVersion(){
        return implode('.', $this->version);
    }
    
    function patchToVersion($targetVersion = NULL) {
        
    }
    
    protected function insert(array $config) {
        
    }
    
    protected function update(array $config) {
        
    }
    
    protected function delete(array $config) {
        
    }
    
    protected function connect(array $config) {
        
    }
    
    protected function checkVersion() {
        $versionRepo = $this->om->getRepository('DoctrineDbPatcher\Entity\DbVersion');
        $dbVersion = $versionRepo->findOneBy([]);
        if ($dbVersion === NULL) {
            $dbVersion = new DbVersion();
            $dbVersion->setVersion('0.0.0');
            $this->om->persist($dbVersion);
        }
        return explode('.', $dbVersion->getVersion());
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
