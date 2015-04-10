<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/User for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace User\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\Db\Sql\Expression;
use Zend\View\Model\JsonModel;
use DateTime;
use Application\Model\UserAction;
use Application\Model\UserFavorite;

class UserController extends AbstractActionController
{
    public function indexAction()
    {
        $this->layout('layout/logged');
    	
    	return array();
    }

    public function viewAction()
    {
        if($this->identity()->user_id == $this->params('id'))
        {
            return $this->redirect()->toRoute('account/view');
        }
        
        $mapper = $this->getServiceLocator()->get('userMapper');
        
    	$this->layout('layout/logged');
    	
    	$this->layout()->fullLayout = true;
    	
    	$user = $mapper->find($this->params('id'));
    	
    	if(!$user)
    	{
    	    $this->flashMessenger()->setNamespace('error')->addMessage('Ce membre n\'existe pas');
    	    
    	    return $this->redirect()->toRoute('account');
    	}
    	
    	// GET ALL PHOTOS
    	
    	$photoMapper = $this->getServiceLocator()->get('PhotoMapper');
    	
    	$photos = $photoMapper->fetchAll(array('owner'=>$user->getId()));
    	
    	$photos->buffer();
    	
    	// CHECK IF USER IS FAVORITE
    	
    	$favoriteMapper = $this->getServiceLocator()->get('FavoriteMapper');
    	
    	$isFavorite = $favoriteMapper->find($this->identity()->user_id, $user->getId());   	
    	
    	if($isFavorite)
    	{
    	    $favorite = true;
    	}
    	else
    	{
    	    $favorite = false;
    	}
    	
    	// GET PROFIL QUESTIONS & CATEGORIES
    	
        $categories = array();
    	
    	$categoryMapper = $this->getServiceLocator()->get('QuestionCategoryMapper');
    	$questionMapper = $this->getServiceLocator()->get('QuestionMapper');
    	
    	foreach($categoryMapper->fetchAll() as $category)
    	{
    	    $data = array(
    	        'name'      => $category->getName(),
    	        'questions' => array()
    	    );
    	    
    	    $questions = $questionMapper->fetchAll(array('category'=>$category->getId()));
    	    
    	    foreach($questions as $question)
    	    {
    	        $data['questions'][] = $question;
    	    }
    	    
    	    $categories[] = $data;
    	}
    	
    	// ADD VIEW ACTION
    	
    	$actionMapper = $this->getServiceLocator()->get('ActionMapper');
    	
    	$action = $actionMapper->findPerformed($this->identity()->user_id, $user->getId(), 'view');
    	
    	if(!$action)
    	{
        	$action = new UserAction();
        	
        	$action->setAuthor($this->identity()->user_id);
        	$action->setType('view');
        	$action->setUser($user->getId());
        	
    	}
    	
    	$action->setAdded(new Expression('UTC_TIMESTAMP()'));
    	
    	$actionMapper->save($action);
    	
        return array(
            'user'     => $user,
            'photos'   => $photos,
            'favorite' => $favorite,
        	'categories' => $categories
        );
    }
    
    public function flashAction()
    {
        if($this->getRequest()->isPost())
        {       
            $data = $this->getRequest()->getPost();
            
            $userMapper = $this->getServiceLocator()->get('userMapper');
            
            $user = $userMapper->find($data['user']);
            
            // Check if user exist and not banned
            
            if($user && $user->getStatus() == 'active')
            {
                $actionMapper = $this->getServiceLocator()->get('ActionMapper');
                
                $action = $actionMapper->findPerformed($this->identity()->user_id, $user->getId(), 'flash');
                
                // check if flash has already sent to this user if yes check if latest is 24 hours passed
                
                if(!$action )
                {          
                    $action = new UserAction();
                     
                    $action->setAuthor($this->identity()->user_id);
                    $action->setType('flash');
                    $action->setUser($data['user']);              
                }
                else
                {
                    $datetime1 = new DateTime();
                    $datetime2 = new DateTime($action->getAdded());
                    
                    $interval = $datetime1->diff($datetime2);
                    
                    if($interval->days == 0)
                    {
                        return new JsonModel(array('status'=>'error', 'message' => 'Vous avez envoyé un flash il y moins de 24h !'));
                    }
                }
                
                $action->setAdded(new Expression('UTC_TIMESTAMP()'));
                 
                $actionMapper->save($action);
                
                return new JsonModel(array('status'=>'success'));
            }
            
            return new JsonModel(array('status'=>'error'));
        }
    }
    
    public function blacklistAction()
    {
        if($this->getRequest()->isPost())
        {
            $data = $this->getRequest()->getPost();
        
            $userMapper = $this->getServiceLocator()->get('userMapper');
        
            $user = $userMapper->find($data['user']);
        
            // Check if user exist and not banned
        
            if($user && $user->getStatus() == 'active')
            {
                $favoriteMapper = $this->getServiceLocator()->get('FavoriteMapper');
        
                $favorite = $favoriteMapper->find($this->identity()->user_id, $user->getId());
        
                // check if user has already added this user
        
                if(!$favorite )
                {
                    $favorite = new UserFavorite();
                     
                    $favorite->setOwner($this->identity()->user_id);
                    $favorite->setUSer($user->getId());
                    $favorite->setAdded(new Expression('UTC_TIMESTAMP()'));
                }
                else
                {
                    return new JsonModel(array('status'=>'error', 'message' => 'Vous avez déjà ajouté ce membre à vos favoris !'));
                }
                 
                $favoriteMapper->save($favorite);
        
                return new JsonModel(array('status'=>'success'));
            }
        
            return new JsonModel(array('status'=>'error'));
        }
    }
    
    public function favoriteAction()
    {
        if($this->getRequest()->isPost())
        {
            $data = $this->getRequest()->getPost();
    
            $userMapper = $this->getServiceLocator()->get('userMapper');
    
            $user = $userMapper->find($data['user']);
    
            // Check if user exist and not banned
    
            if($user && $user->getStatus() == 'active')
            {
                $favoriteMapper = $this->getServiceLocator()->get('FavoriteMapper');
    
                $favorite = $favoriteMapper->find($this->identity()->user_id, $user->getId());
    
                // check if user has already added this user
    
                if(!$favorite )
                {
                    $favorite = new UserFavorite();
                     
                    $favorite->setOwner($this->identity()->user_id);
                    $favorite->setUSer($user->getId());
                    $favorite->setAdded(new Expression('UTC_TIMESTAMP()'));
                }
                else
                {
                    return new JsonModel(array('status'=>'error', 'message' => 'Vous avez déjà ajouté ce membre à vos favoris !'));
                }
                 
                $favoriteMapper->save($favorite);
    
                return new JsonModel(array('status'=>'success'));
            }
    
            return new JsonModel(array('status'=>'error','message'=>'Le compte de ce membre a été désactivé.'));
        }
    }
    
    public function deleteFavoriteAction()
    {    
        $data = $this->getRequest()->getPost();
                    
        $favoriteMapper = $this->getServiceLocator()->get('FavoriteMapper');        
        
        $favorite = $favoriteMapper->find($this->identity()->user_id, $data['user']);
        // Check if user exist and not banned

        if($favorite)
        {
            $favoriteMapper->delete($favorite);

            return new JsonModel(array('status'=>'success'));
        }

        return new JsonModel(array('status'=>'error','message'=>'Ce membre ne fait pas parti de vos favoris.'));
    }

}
