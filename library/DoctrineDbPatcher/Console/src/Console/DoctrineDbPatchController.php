<?php
namespace DoctrineDbPatcher\Console;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\Console\Request;

class DoctrineDbPatchController extends AbstractActionController
{
    public function dbpatchAction()
    {
    	$request = $this->getRequest();
        if (!$request instanceof Request) {
            throw new \RuntimeException('You can only use this action from a console!');
        }
        $params = $request->getParams();
        
        
    	echo "Datenbank wurde gepatched!\n";
    }
}
