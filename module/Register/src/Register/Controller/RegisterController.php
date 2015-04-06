<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/Register for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Register\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Register\Form\RegisterForm;
use Application\Model\User;
use Zend\View\Model\JsonModel;

class RegisterController extends AbstractActionController
{
    public function indexAction()
    {
        $form = new RegisterForm();
        
        if($this->getRequest()->isPost())
        {                    
            $sm = $this->getServiceLocator();           
            
            $request = $this->getRequest();
            $post = $request->getPost();
            
            $form->setData($post);
            
            if($form->isValid())
            {
                $userMapper = $this->getServiceLocator('userMapper');
                $user = new User();
                
                $data = $form->getData();
                
                $user->setName($data['name'])
                     ->setEmail($data['email'])
                     ->setPassword(sha1($data['password']));
                
                $userMapper->save($user);

                if($this->getRequest()->isXmlHttpRequest())
                {
                    return new JsonModel(array('status'=>'success'));
                }
                else
                {
                    return $this->redirect()->toRoute('register');
                }
                               
            }
            else
            {
                /** @todo erreur du formulaire **/
            }

        }
        
        return array('form'=>$form);
    }

    public function fooAction()
    {
        // This shows the :controller and :action parameters in default route
        // are working when you browse to /register/register/foo
        return array();
    }
}
