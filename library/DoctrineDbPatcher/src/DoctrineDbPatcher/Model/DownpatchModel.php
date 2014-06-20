<?php
namespace DoctrineDbPatcher\Model;

class DownpatchModel extends PatchModel
{
    function patchToVersion($targetVersion = NULL) {
        return $this->downpatchToVersion($targetVersion);
    }
    
    /**
     * Downpatching to $targetVersion
     * @param unknown $targetVersion
     * @throws \Exception
     * @return boolean
     */
    protected function downpatchToVersion($targetVersion) {
        if ($targetVersion == NULL) {
            $targetVersion = '0.0.0';
            $this->patches['0.0.0'] = [];
        }
        $targetVersion = explode('.', $targetVersion);
        if (!array_key_exists(implode('.', $targetVersion), $this->patches)) {  //if patch not exists
            throw new \Exception('Patch-version ' . implode('.', $targetVersion) . ' was not found!');
        } else if ($this->versionCmp($this->version, $targetVersion) == -1) {  //if target version > actual version
            throw new \Exception('Allready at version ' . $this->getVersion() . ' so downpatch to ' . implode('.', $targetVersion) . ' is not needed!');
        }
        $patchedSomething = false;
        $this->patches = array_reverse($this->patches, true);
        foreach ($this->patches as $patchVersion => $patch) {
            $patchVersionArray = explode('.', $patchVersion);
            if ($this->versionCmp($this->version, $patchVersionArray) == 0) {  //if patch equals the db version
                if ($this->versionCmp($patchVersionArray, $targetVersion) == 1) {   //if target version is lower
                    try{
                        $newVersion = key($this->patches);
                        
                        $dbVersion = $this->om->getRepository($this->dbVersionNamespace)->findOneBy([]);
                        $dbVersion->setVersion($newVersion);
                        $this->om->persist($dbVersion);
                        
                        $invertedPatch = $this->invertPatch($patch);    //invert the patch to downpatch
                        if ($this->applyPatch($invertedPatch)) {
                            echo 'Successfull applied patch ' . $this->getVersion() . ' -> ' . $newVersion . "\n";
                            $this->version = explode('.', $newVersion);
                            $patchedSomething = true;
                        }
                    }catch (\UnexpectedValueException $e){
                        throw new \Exception('Patch ' . $newVersion . ' failed for the following reason: "' . $e->getMessage()
                                . '"! DB would still at version ' . $this->getVersion());
                    }
                }
            }
        }
        return $patchedSomething;
    }
    
    /**
     * Inverts the patch to revert its changes
     * @param array $patch
     * @return array
     */    
    protected function invertPatch(array $patch) {
        $invertionResult = [];
        $insert = [];
        $update = [];
        $delete = [];
        $connect = [];
        
        if (array_key_exists('insert', $patch)) {
            foreach ($patch['insert'] as $entityNamespace => $setting) {
                $delete[$entityNamespace] = [$setting];
            }
        }
        
        if (array_key_exists('update', $patch)) {
            foreach ($patch['update'] as $entityNamespace => $settings) {
                foreach ($settings as $setting) {
                    $newSetting = ['attributes' => $setting['attributes'], 'values' => []];
                    foreach ($setting['values'] as $key => $keyPair) {
                        $inversedKeyPair = ['old' => $keyPair['new'], 'new' => $keyPair['old']];
                        $newSetting['values'][$key] = $inversedKeyPair;
                    }
                    $update[$entityNamespace] = $newSetting;
                }
            }
        }
        
        if (array_key_exists('delete', $patch)) {
            foreach ($patch['delete'] as $entityNamespace => $settings) {
                $insert[$entityNamespace] = reset($settings);
            }
        }
        
        if (array_key_exists('connect', $patch)) {
            foreach ($patch['connect'] as $setting) {
                $newSetting = $setting;
                $newSetting['methods'] = array_flip($setting['methods']);
                $connect[] = $newSetting;
            }
        }
        
        $invertionResult['insert'] = $insert;
        $invertionResult['update'] = $update;
        $invertionResult['delete'] = $delete;
        $invertionResult['connect'] = $connect;
        
        return $invertionResult;
    }
}
