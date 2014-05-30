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
     * @param string $downpatch make a downpatch
     * @throws \Exception
     * @return boolean true if any patch was applied
     */
    function patchToVersion($targetVersion = NULL) {
        if ($targetVersion != NULL) {
            $targetVersion = explode('.', $targetVersion);
            if (!array_key_exists(implode('.', $targetVersion), $this->patches)) {  //if patch not exists
                throw new \Exception('Patch ' . implode('.', $targetVersion) . ' was not found!');
            } else if ($this->versionCmp($this->version, $targetVersion) == 1) {  //if target version < actual version
                throw new \Exception('Allready at version ' . $this->getVersion() . ' so updating to ' . implode('.', $targetVersion) . ' is not needed!');
            }
        }
        $patchedSomething = false;
        foreach ($this->patches as $patchVersion => $patch) {
            $patchVersionArray = explode('.', $patchVersion);
            if ($this->versionCmp($this->version, $patchVersionArray) == -1) {  //if patch is newer than version
                if ($targetVersion == NULL || $this->versionCmp($patchVersionArray, $targetVersion) == -1) {
                    try{
                        $dbVersion = $this->om->getRepository('DoctrineDbPatcher\Entity\DbVersion')->findOneBy([]);
                        $dbVersion->setVersion($patchVersion);
                        $this->om->persist($dbVersion);
                        if ($this->applyPatch($patch)) {
                            echo 'Successfull applied patch ' . $this->getVersion() . ' -> ' . $patchVersion . "\n";
                            $this->version = $patchVersionArray;
                            $patchedSomething = true;
                        }
                    }catch (\UnexpectedValueException $e){
                        throw new \Exception('Patch ' . $patchVersion . ' failed for the following reason: "' . $e->getMessage()
                            . '"! DB would still at version' . $this->getVersion());
                    }
                }
            }
        }
        return $patchedSomething;
    }
    
    /**
     * Applies a specific patch to DB
     * @param array $patchData
     * @return boolean
     */
    protected function applyPatch(array $patchData) {
        if (array_key_exists('insert', $patchData)) {
            $this->insert($patchData['insert']);
        }
        
        if (array_key_exists('update', $patchData)) {
            $this->update($patchData['update']);
        }
        
        if (array_key_exists('delete', $patchData)) {
            $this->delete($patchData['delete']);
        }
        
        if (array_key_exists('connect', $patchData)) {
            $this->connect($patchData['connect']);
        }
        $this->om->flush();
        return true;
    }
    
    /**
     * Resolve the insert-config and insert its contents
     * @param array $config
     * @throws \Exception
     */
    protected function insert(array $config) {
        foreach ($config as $entityNamespace => $setting) {
            if (!array_key_exists('attributes', $setting)) {
                throw new \UnexpectedValueException('[insert]: no attributes set for Entity "' . $entityNamespace . '"');
            } else if (!array_key_exists('values', $setting)) {
                throw new \UnexpectedValueException('[insert]: no values set for Entity "' . $entityNamespace . '"');
            }
            
            foreach ($setting['values'] as $values) {
                if (count($values) != count($setting['attributes'])) {
                    throw new \UnexpectedValueException('[insert]: values param count doesnt match attributes param count at "' . $entityNamespace . '"');
                }
                
                $entity = new $entityNamespace();   //create new void entity
                foreach ($setting['attributes'] as $key => $attribute) {
                    $func = 'set' . ucfirst($attribute);
                    if (!method_exists($entity, $func)) {
                        throw new \UnexpectedValueException('method "' . $func . '" is not defined on "' . $entityNamespace
                            . '". Do you not use common setter functions? Use Doctrine to generate your entities!');
                    }
                    $entity->$func($values[$key]);  //use setter for attribute
                }
                $this->om->persist($entity);
            }
        }
    }
    
    /**
     * Resolve the update-config and update as stated
     * Warning: Never update entries you inserted in the same patch. It won't work!
     * @param array $config
     * @throws \UnexpectedValueException
     */
    protected function update(array $config) {
        foreach ($config as $entityNamespace => $settings) {
            foreach ($settings as $setting) {
                if (!array_key_exists('attributes', $setting)) {
                    throw new \UnexpectedValueException('[update]: no attributes set for update Entity "' . $entityNamespace . '"');
                } else if (!array_key_exists('values', $setting)) {
                    throw new \UnexpectedValueException('[update]: no values set for update Entity "' . $entityNamespace . '"');
                }
            
                foreach ($setting['values'] as $values) {
                    $firstValues = reset($values);  //strict mode intermediate-step
                    if (count(reset($firstValues)) != count($setting['attributes'])) {
                        throw new \UnexpectedValueException('[update]: values param count doesnt match attributes param count at "' . $entityNamespace . '"');
                    } else if (!array_key_exists('old', $values) || !array_key_exists('new', $values)) {
                        throw new \UnexpectedValueException('[update]: values must have "old" and "new" key. Missing at "' . $entityNamespace . '"');
                    }
    
                    $searchParam = [];
                    foreach ($values['old'] as $key => $value) {
                        $searchParam[$setting['attributes'][$key]] = $value;
                    }
                    $entities = $this->om->getRepository($entityNamespace)->findBy($searchParam);
                    if (count($entities) < 1) {
                        throw new \UnexpectedValueException('[update]: entry to update not found in DB for "' . $entityNamespace . '". Probably the patch is broken!');
                    }
                    foreach ($entities as $entity) {
                        foreach ($setting['attributes'] as $key => $attribute) {
                            $func = 'set' . ucfirst($attribute);
                            if (!method_exists($entity, $func)) {
                                throw new \UnexpectedValueException('method "' . $func . '" is not defined on "' . $entityNamespace
                                        . '". Do you not use common setter functions? Use Doctrine to generate your entities!');
                            }
                            $entity->$func($values['new'][$key]);  //use setter for attribute
                        }
                        $this->om->persist($entity);
                    }
                }
            }
        }
    }
    
    /**
     * Resolve the delete-config and delete as stated
     * Warning: Never delete entries you inserted in the same patch. It won't work!
     * @param array $config
     * @throws \UnexpectedValueException
     */
    protected function delete(array $config) {
        foreach ($config as $entityNamespace => $settings) {
            foreach ($settings as $setting) {
                if (!array_key_exists('attributes', $setting)) {
                    throw new \UnexpectedValueException('[delete]: no attributes set for delete Entity "' . $entityNamespace . '"');
                } else if (!array_key_exists('values', $setting)) {
                    throw new \UnexpectedValueException('[delete]: no values set for delete Entity "' . $entityNamespace . '"');
                }
        
                foreach ($setting['values'] as $values) {
                    if (count(reset($values)) != count($setting['attributes'])) {
                        throw new \UnexpectedValueException('[delete]: values param count doesnt match attributes param count at "' . $entityNamespace . '"');
                    }
        
                    $searchParam = [];
                    foreach ($values as $key => $value) {
                        $searchParam[$setting['attributes'][$key]] = $value;
                    }
                    $entities = $this->om->getRepository($entityNamespace)->findBy($searchParam);
                    if (count($entities) < 1) {
                        throw new \UnexpectedValueException('[delete]: entry to delete not found in DB for "' . $entityNamespace . '". Probably the patch is broken!');
                    }
                    foreach ($entities as $entity) {
                        $this->om->remove($entity);
                    }
                }
            }
        }
    }
    
    protected function connect(array $config) {
        foreach ($config as $relation) {
            if (!array_key_exists('entities', $relation)) {
                throw new \UnexpectedValueException('[connect]: no entities set for connect!');
            }
            $sourceEnties = array_keys($relation['entities']);  //strict mode intermediate-step
            $sourceEntity = reset($sourceEnties);
            $targetEntity = reset($relation['entities']);
            if (!array_key_exists('methods', $relation)) {
                throw new \UnexpectedValueException('[connect]: no methods set for connect ' . $sourceEntity . ' with ' . $targetEntity . '!');
            }
            $methods = array_keys($relation['methods']);    //strict mode intermediate-step
            $addMethod = reset($methods);
            if (!array_key_exists('targets', $relation)) {
                throw new \UnexpectedValueException('[connect]: no targets set for connect ' . $sourceEntity . ' with ' . $targetEntity . '!');
            }
            foreach ($relation['targets'] as $target) {
                if (!array_key_exists('source', $target)) {
                    throw new \UnexpectedValueException('[connect]: no source set for connect ' . $sourceEntity . ' with ' . $targetEntity . '!');
                }
                if (!array_key_exists('target', $target)) {
                    throw new \UnexpectedValueException('[connect]: no target set for connect ' . $sourceEntity . ' with ' . $targetEntity . '!');
                }
                $entitiesToUpdate = $this->findPersistedEntityBy($sourceEntity, $target['source']);
                $dbEntities = $this->om->getRepository($sourceEntity)->findBy($target['source']);
                $entitiesToUpdate = array_merge($entitiesToUpdate, $dbEntities);
                foreach ($entitiesToUpdate as $entityToUpdate) {
                    $entitiesToAdd = $this->findPersistedEntityBy($targetEntity, $target['target']);
                    $dbEntities = $this->om->getRepository($targetEntity)->findBy($target['target']);
                    $entitiesToAdd = array_merge($entitiesToAdd, $dbEntities);
                    foreach ($entitiesToAdd as $entityToAdd) {
                        $entityToUpdate->$addMethod($entityToAdd);
                    }
                    $this->om->persist($entityToUpdate);
                }
            }
        }
    }
    
    /**
     * Searches in persisted Entites for matching $criteria
     * ODM Related function usage
     * @param string $entityNamespace
     * @param array $criteria
     */
    protected function findPersistedEntityBy($entityNamespace, array $criteria) {
        $results = [];
        $documents = $this->om->getUnitOfWork()->getScheduledDocumentInsertions();
        foreach ($documents as $document) {
            if ($document instanceof $entityNamespace) {
                $accept = true;
                foreach ($criteria as $propertie => $searchValue) {
                    $func = 'get' . ucfirst($propertie);
                    if ($document->$func() != $searchValue) {
                        $accept = false;
                    }
                }
                if ($accept) {
                    $results[] = $document;
                }
            }
        }
        return $results;
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
            $this->om->flush();
        }
        return explode('.', $dbVersion->getVersion());
    }
    
    /**
     * Compares two versions
     * @param array $v1
     * @param array $v2
     * @throws \UnexpectedValueException If not using Semantic Versioning
     * @return number -1, 0 or 1
     */
    protected function versionCmp(array $v1, array $v2) {
        if (count($v1) != 3 || count($v2) != 3) {
            throw new \UnexpectedValueException('You HAVE TO use Semantic Versioning. See http://semver.org/ for more information!');
        }
        $result = 0;
        if ($v1[0] < $v2[0] || $v1[1] < $v2[1] || $v1[2] < $v2[2]) {
            $result = -1;
        } else if ($v1[0] > $v2[0] || $v1[1] > $v2[1] || $v1[2] > $v2[2]) {
            $result = 1;
        }
        return $result;
    }
    
    /**
     * 
     * @param string $key
     * @param array $config
     * @param boolean $required
     * @throws \Exception
     * @return array|boolean
     */
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
