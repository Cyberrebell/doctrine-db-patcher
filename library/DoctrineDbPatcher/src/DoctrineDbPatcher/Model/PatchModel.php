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
    
    /**
     * Get the Version like this: "1.4.2"
     * @return string
     */
    function getVersion(){
        return implode('.', $this->version);
    }
    
    /**
     * Patches the DB to target version
     * @param string $targetVersion like "1.4.2"
     * @throws \Exception
     * @return boolean true if any patch was applied
     */
    function patchToVersion($targetVersion = NULL) {
        if ($targetVersion != NULL) {
            $targetVersion = explode('.', $targetVersion);
        }
        $patchedSomething = false;
        foreach ($this->patches as $patchVersion => $patch) {
            $patchVersionArray = explode('.', $patchVersion);
            if ($this->versionCmp($this->version, $patchVersionArray) == -1) {  //if patch is newer than version
                if ($targetVersion == NULL || $this->versionCmp($patchVersionArray, $targetVersion) == -1) {
                    try{
                        if ($this->applyPatch($patch)) {
                            echo 'Successfull applied patch ' . $this->getVersion() . ' -> ' . $patchVersion . "\n";
                            $this->version = $patchVersionArray;
                            $patchedSomething = true;
                        }
                    }catch (\Exception $e){
                        throw new \Exception('Patch ' . $patchVersion . ' failed for the following reason: "' . $e->getMessage() . '"');
                    }
                }
            }
        }
        return $patchedSomething;
    }
    
    protected function applyPatch(array $patchData) {
        $success = true;
        
        //do the patch
        
        return $success;
    }
    
    protected function insert(array $config) {
        
    }
    
    protected function update(array $config) {
        
    }
    
    protected function delete(array $config) {
        
    }
    
    protected function connect(array $config) {
        
    }
    
    /**
     * check for version in db
     * set to 0.0.0 if not exists
     * @return array(3) version
     */
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
    
    /**
     * Compares two versions
     * @param array $v1
     * @param array $v2
     * @throws \Exception If not using Semantic Versioning
     * @return number -1, 0 or 1
     */
    protected function versionCmp(array $v1, array $v2) {
        if (count($v1) != 3 || count($v2) != 3) {
            throw new \Exception('You HAVE TO use Semantic Versioning. See http://semver.org/ for more information!');
        }
        $result = 0;
        if ($v1[0] < $v2[0] || $v1[1] < $v2[1] || $v1[2] < $v2[2]) {
            $result = -1;
        } else if ($v1[0] > $v2[0] || $v1[1] > $v2[1] || $v1[2] > $v2[2]) {
            $result = 1;
        }
        return $result;
    }
    
    protected function getConfigValue($key, array $config, $required = true) {
        if (array_key_exists($key, $config)) {
            return $config[$key];
        } else if ($required) {
            throw new \Exception($key . ' must be configurated in module.config!');
        } else {
            return false;
        }
    }
}
