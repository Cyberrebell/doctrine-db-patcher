<?php
namespace DoctrineDbPatcher\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\Console\Request;
use DoctrineDbPatcher\Model\PatchModel;

class DoctrineDbPatchController extends AbstractActionController
{
    public function dbpatchAction()
    {
    	$request = $this->getRequest();
        if (!$request instanceof Request) {
            throw new \RuntimeException('You can only use this action from a console!');
        }
        $params = $request->getParams();
        $patchModel = new PatchModel($this->getServiceLocator());
        
        if ($params->get('v')) {
            echo 'Version: ' . $patchModel->getVersion() . "\n";
            exit;
        }
        
        $patchModel->patchToVersion();
        
    	echo 'You patched the database to version ' . $patchModel->getVersion() . '!' . "\n";
    }
}
