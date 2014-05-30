<?php
namespace DoctrineDbPatcher\Model;

class DownpatchModel extends PatchModel
{
    function patchToVersion($targetVersion = NULL) {
        $this->downpatchToVersion($targetVersion);
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
        $this->patches = array_reverse($this->patches);
        foreach ($this->patches as $patchVersion => $patch) {
            $patchVersionArray = explode('.', $patchVersion);
            if ($this->versionCmp($this->version, $patchVersionArray) == 0) {  //if patch equals the db version
                if ($this->versionCmp($patchVersionArray, $targetVersion) == 1) {   //if target version is lower
                    try{
                        $dbVersion = $this->om->getRepository('DoctrineDbPatcher\Entity\DbVersion')->findOneBy([]);
                        $dbVersion->setVersion($patchVersion);
                        $this->om->persist($dbVersion);
                        
                        $invertedPatch = $this->invertPatch($patch);    //invert the patch to downpatch
                        if ($this->applyPatch($invertedPatch)) {
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
        return $invertionResult;
    }
}
