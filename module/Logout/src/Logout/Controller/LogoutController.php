<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/Logout for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Logout\Controller;

use Zend\Mvc\Controller\AbstractActionController;

class LogoutController extends AbstractActionController
{
    public function indexAction()
    {
        $this->getServiceLocator()->get('AuthService')->clearIdentity();

        return $this->redirect()->toRoute('home');
    }
}
