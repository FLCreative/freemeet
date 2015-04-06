<?php

namespace Search\Controller;

use Zend\Mvc\Controller\AbstractActionController;

class ResultController extends AbstractActionController
{
    public function indexAction()
    {
        $this->layout('layout/logged');
    	
    	return array();
    }
}
?>