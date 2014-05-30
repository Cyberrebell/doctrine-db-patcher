<?php
namespace DoctrineDbPatcher\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\Console\Request;
use DoctrineDbPatcher\Model\PatchModel;
use DoctrineDbPatcher\Model\DownpatchModel;

class DoctrineDbPatchController extends AbstractActionController
{
    public function dbpatchAction()
    {
    	$request = $this->getRequest();
        if (!$request instanceof Request) {
            throw new \RuntimeException('You can only use this action from a console!');
        }
        $params = $request->getParams();
        
        if ($params->get('down')) {
            $patchModel = new DownpatchModel($this->getServiceLocator());
        } else {
            $patchModel = new PatchModel($this->getServiceLocator());
        }
        
        if ($params->get('v')) {
            echo 'Version: ' . $patchModel->getVersion() . "\n";
            exit;
        }
        
        $targetVersion = $params->get('version');
        
        if ($patchModel->patchToVersion($targetVersion)) {
            echo 'You patched the database to version ' . $patchModel->getVersion() . '!' . "\n";
        } else {
            echo 'No patch found to apply an update. Still at version ' . $patchModel->getVersion() . '!' . "\n";
        }
    }
}
