<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/User for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Search\Controller;

use Zend\Mvc\Controller\AbstractActionController;

class IndexController extends AbstractActionController
{
    public function indexAction()
    {
        $this->layout('layout/logged');
    	
    	return array();
    }

    public function onlineAction()
    {
        // This shows the :controller and :action parameters in default route
        // are working when you browse to /user/user/foo
    	$this->layout('layout/logged');
    	
    	$userMapper = $this->getServiceLocator()->get('UserMapper');
    	   	
        return array('users'=>$userMapper->fetchAll());
    }
    
    public function newAction()
    {
        
    }
}
