<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/Account for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Application\Model\UserPhotoMapper;
use Account\Form\EditPasswordForm;
use Zend\View\Model\JsonModel;


class AccountController extends AbstractActionController
{   
    public function indexAction()
    {
        $sm = $this->getServiceLocator();
        $mapper = $sm->get('UserMapper');
        
        //FETCH LAST USER ACTIONS
        $actionsMapper = $sm->get('ActionMapper');
        $actions = $actionsMapper->fetchAll(array('author'=>$this->identity()->user_id,'limit'=>5));
        
        return array(
            'users'     => $mapper->fetchAll(),
            'onlines'   => $mapper->fetchOnline($this->identity()->user_id),
            'actions'   => $actions,
            'newUsers'  => $mapper->fetchAll(array('status'=>'active','order'=>'user_id DESC','limit'=>2))
        );
    }
    
    public function completeAction()
    {
        $this->layout()->fullLayout = true;
        
        return array();
    }
    
    public function viewAction()
    {
        return array();
    }

    public function visitAction()
    {
        $sm = $this->getServiceLocator();
        $mapper = $sm->get('UserMapper');
        
        $profils = $mapper->fetchVisitors($this->identity()->user_id, true);
        
        // set the current page to what has been passed in query string, or to 1 if none set
        $profils->setCurrentPageNumber((int) $this->params()->fromQuery('page', 1));
        // set the number of items per page to 10
        $profils->setItemCountPerPage(16);
        
        return array('profils'=>$profils);
    }
    
    public function flashAction()
    {
        $sm = $this->getServiceLocator();
        $mapper = $sm->get('UserMapper');
        
        $profils = $mapper->fetchFlashs($this->identity()->user_id, true);
        
        // set the current page to what has been passed in query string, or to 1 if none set
        $profils->setCurrentPageNumber((int) $this->params()->fromQuery('page', 1));
        // set the number of items per page to 10
        $profils->setItemCountPerPage(16);
        
        return array('profils'=>$profils);
    }
    
    public function favoriteAction()
    {
        $sm = $this->getServiceLocator();
        $mapper = $sm->get('UserMapper');
        
        $profils = $mapper->fetchFavorites($this->identity()->user_id, true);
        
        // set the current page to what has been passed in query string, or to 1 if none set
        $profils->setCurrentPageNumber((int) $this->params()->fromQuery('page', 1));
        // set the number of items per page to 10
        $profils->setItemCountPerPage(16);
        
        return array('profils'=>$profils);
    }
    
    public function profilAction()
    {
        $this->layout()->fullLayout = true;
        
        $photoMapper = $this->getServiceLocator()->get('PhotoMapper');
        $photos = $photoMapper->fetchAll(array('owner'=>$this->identity()->user_id));
        
        return array('photos'=>$photos);
    }
    
    public function preferenceAction()
    {
        // This shows the :controller and :action parameters in default route
        // are working when you browse to /account/account/foo
        return array();
    }
    
    public function editPasswordAction()
    {
        if($this->getRequest()->isPost())
        {
                     
            $form = new EditPasswordForm();
            
            $data = $this->getRequest()->getPost();
            
            $form->setData($data);
                                           
            if($form->isValid())
            {
                $user = $this->getServiceLocator()->get('currentUser');
                
                if(md5($data['currentPassword']) != $user->getPassword())
                {
                    return new JsonModel(array('status'=>'error','messages'=>sha1($data['currentPassword'])));
                }

                $mapper = $this->getServiceLocator()->get('userMapper');
                $user->setPassword((md5($data['newPassword'])));
                
                $mapper->save($user);                
                
                return new JsonModel(array('status'=>'success'));
            }
            else
            {
                $errors = array();
                $messages = $form->getMessages();
                
                foreach($messages as $error)
                {
                    $errors[] = $error;
                }
                
                return new JsonModel(array('status'=>'error','error'=>$errors));
            }
        }
    }
    
    public function logoutAction()
    {
    	$this->getServiceLocator()->get('AuthService')->clearIdentity();
    	
    	return $this->redirect()->toRoute('home');
    }
    
}
