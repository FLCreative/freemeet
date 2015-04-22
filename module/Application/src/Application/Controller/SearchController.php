<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class SearchController extends AbstractActionController
{
    public function indexAction()
    {
    	$currentUser = $this->getServiceLocator()->get('CurrentUser');
    	
    	return new ViewModel(array('user'=>$currentUser));
    }
    
    public function resultAction()
    {
    	
    	
    	$userMapper = $this->getServiceLocator()->get('UserMapper');
    	
    	$users = $userMapper->fetchAll();
    	
    	return new ViewModel();
    }
    
}
