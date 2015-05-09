<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/Login for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Login\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Login\Form\LoginForm;
use Zend\Db\Sql\Expression;
use ElephantIO\Client;
use ElephantIO\Engine\SocketIO\Version1X;

use Zend\Session\Container;
use Zend\Session\SessionManager;

class LoginController extends AbstractActionController
{
    protected $form;
    protected $data;
    
    public function indexAction()
    {
        /** @todo Redirection si déjà identifié */
        
        $this->form = new LoginForm();
        
        if($this->getRequest()->isPost())
        {                    
            $sm = $this->getServiceLocator();           
            
            $request = $this->getRequest();
            $post = $request->getPost();
            
            $this->form->setData($post);
            
            if($this->form->isValid())
            {
                $this->data = $this->form->getData();
                                   
                return $this->forward()->dispatch('Login\Controller\Login', array('action' => 'authenticate'));               
            }

        }
        
        return array('form'=>$this->form);
    }
    
    /**
     * General-purpose authentication action
     */
    
    public function authenticateAction()
    {      
        $auth = $this->getServiceLocator()->get('AuthService');
                      
        $auth->getAdapter()->setIdentity($this->data['email'])
                           ->setCredential($this->data['password']);
        
        // authenticate, this ensures that users.active = TRUE
        $result =  $auth->authenticate();
        
        if($result->isValid())
        {                   
            $infos = $auth->getAdapter()->getResultRowObject(array(
                'user_id','user_name',
            ));
            
            $storage = $auth->getStorage();
            
            $storage->write($infos);           
            
            // Update last login time
            $userMapper = $this->getServiceLocator()->get('userMapper');
            
            $user = $userMapper->find($infos->user_id);
            
            $user->setLastLogin(new Expression('UTC_TIME()'));
            
            $userMapper->save($user);
            
            $sessionManager = new SessionManager();
                     
            
            $client = new Client(new Version1X('http://localhost:3000'));
            $client->initialize();
            $client->emit('add user', array('username' => $user->getName(), 'session' => $sessionManager->getId()));
            
            $container = new Container('user');
            $container->socket = $client;
            

            
            return $this->redirect()->toRoute('account');  
        }
        
        else
        {
            $this->flashMessenger()->setNamespace('login')->addMessage('Mot de passe ou adresse email incorrect !');
            return $this->redirect()->toRoute('login'); 
        }
    }

}
