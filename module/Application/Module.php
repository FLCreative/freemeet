<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application;

use Zend\Mvc\MvcEvent;
use Application\Model\UserMapper;
use Application\Model\User;
use Application\Model\UserActionMapper;
use Application\Model\UserFavoriteMapper;
use Application\Model\UserPhotoMapper;
use Application\Model\UserWarningMapper;
use Zend\Mvc\Router\RouteMatch;
use Zend\Authentication\Adapter\DbTable as AuthAdapter;
use Zend\Mvc\Application;
use Application\Model\UserBlacklistMapper;
use Application\Model\ProfilQuestionCategoryMapper;
use Application\Model\ProfilQuestionMapper;
use Zend\Db\TableGateway\TableGateway;
use Zend\Session\SaveHandler\DbTableGateway;
use Zend\Session\SaveHandler\DbTableGatewayOptions;
use Zend\Session\SessionManager;

class Module
{
protected $whitelist = array('login','register','authenticate','home');

    public function onBootstrap($e)
    {
        $app = $e->getApplication();
        $em  = $app->getEventManager();
        $sm  = $app->getServiceManager();

        $list = $this->whitelist;
        $auth = $sm->get('my_auth_service');
        $mapper = $sm->get('UserMapper');
        $statusMapper = $sm->get('MessageStatusMapper');

        $adapter = $sm->get('Zend\Db\Adapter\Adapter');
         
        $tableGateway = new TableGateway('session', $adapter);
        $saveHandler  = new DbTableGateway($tableGateway, new DbTableGatewayOptions());
        $manager      = new SessionManager();
        $manager->setSaveHandler($saveHandler);
        
        $manager->start();
        
        
        $em->attach(MvcEvent::EVENT_ROUTE, function($e) use ($list, $auth, $mapper, $statusMapper, $sm) 
        {
           
        	$sm->setService('userStats',null);
            
        	$match = $e->getRouteMatch();

            // No route match, this is a 404
            if (!$match instanceof RouteMatch) {
                return;
            }

            // Route is whitelisted
            $name = $match->getMatchedRouteName();
            if (in_array($name, $list)) {
                return;
            }

           // User is authenticated
            if ($auth->hasIdentity()) {
                
                $user = $mapper->find($auth->getIdentity()->user_id);
                
                if($user != null)
                {   
                    if($user->getStatus() == 'active')
                    {                       
                        $sm->setService('currentUser',$user);
                        
                        $stats = array();
                        
                        // Fetch all stats
                        
                        $stats['unread_messages'] = $statusMapper->countUnreadMessages($user->getId());
                        
                        $sm->setService('userStats',$stats);
                        
                        // Fetch favorite
                        
                        $favoriteMapper = $sm->get('favoriteMapper');
                        
                        $favorites = $favoriteMapper->fetchAll();
                        
                        $sm->setService('userFavorites',$favorites);
                        
                        if($user->getBirthDay() == null)
                        {
                            // Redirect to the profil page to complete informations
                            $router   = $e->getRouter();
                            $url      = $router->assemble(array(), array(
                                'name' => 'account/complete'
                            ));
                            
                            $response = $e->getResponse();
                            $response->getHeaders()->addHeaderLine('Location', $url);
                            $response->setStatusCode(302);
                            
                            return $response;
                        }
                        

                        return;
                    }
                    else
                    {
                        $auth->clearIdentity();
                    }
                    
                }
                else
                {
                    $auth->clearIdentity();
                }               
            }           

            // Redirect to the user login page, as an example
            $router   = $e->getRouter();
            $url      = $router->assemble(array(), array(
                'name' => 'login'
            ));
            
            $response = $e->getResponse();
            $response->getHeaders()->addHeaderLine('Location', $url);
            $response->setStatusCode(302);

            return $response;
        }, -100);
        

        $e->getApplication()->getEventManager()->getSharedManager()->attach('Zend\Mvc\Controller\AbstractActionController', 'dispatch', function($e) {
            $controller = $e->getTarget();
            $controllerClass = get_class($controller);
            $moduleNamespace = substr($controllerClass, 0, strpos($controllerClass, '\\'));
            $config = $e->getApplication()->getServiceManager()->get('config');
            $routeMatch = $e->getRouteMatch();
            $actionName = strtolower($routeMatch->getParam('action', 'not-found')); // get the action name
            if (isset($config['module_layouts'][$moduleNamespace][$actionName])) {
                $controller->layout($config['module_layouts'][$moduleNamespace][$actionName]);
            }elseif(isset($config['module_layouts'][$moduleNamespace]['default'])) {
                $controller->layout($config['module_layouts'][$moduleNamespace]['default']);
            }
        }, 100);
        
    }
    
    public function initSession($config)
    {
    	$adapter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
    	
    	$tableGateway = new TableGateway('session', $adapter);
    	$saveHandler  = new DbTableGateway($tableGateway, new DbTableGatewayOptions());
    	$manager      = new SessionManager();
    	$manager->setSaveHandler($saveHandler);
    }
    
    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }
    
    public function getServiceConfig()
    {
    	return array(
    			'factories' => array(
        			    'ActionMapper' => function ($sm) {
        			        $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
        			        $mapper = new UserActionMapper($dbAdapter);
        			        return $mapper;
        			    },
        			    
        			    'BlacklistMapper' => function ($sm) {
        			        $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
        			        $mapper = new UserBlacklistMapper($dbAdapter);
        			        return $mapper;
        			    },
        			    
        			    'FavoriteMapper' => function ($sm) {
        			        $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
        			        $mapper = new UserFavoriteMapper($dbAdapter);
        			        return $mapper;
        			    },
        			    
/*         			    'FilterMapper' => function ($sm) {
        			        $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
        			        $mapper = new UserFilterMapper($dbAdapter);
        			        return $mapper;
        			    }, */
        			        
    			        'UserMapper' => function ($sm) {   					    
    					    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
    						$mapper = new UserMapper($dbAdapter);
    						return $mapper;
    					},
    					
    					'User' => function ($sm) {   					    
                            $user = new User;
                            $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                            $user->setDbAdapter($dbAdapter);
    						return $user;
    					}, 
    					   					
    					'PhotoMapper' => function ($sm) {
    					    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
    					    $mapper = new UserPhotoMapper($dbAdapter);
    					    return $mapper;
    					},
    					
    					'QuestionCategoryMapper' => function ($sm) {
    						$dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
    						$mapper = new ProfilQuestionCategoryMapper($dbAdapter);
    						return $mapper;
    					},
    					
    					'QuestionMapper' => function ($sm) {
    					    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
    					    $mapper = new ProfilQuestionMapper($dbAdapter);
    					    return $mapper;
    					},
    					
    					'WarningMapper' => function ($sm) {
    					    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
    					    $mapper = new UserWarningMapper($dbAdapter);
    					    return $mapper;
    					},
    					
    					'AuthService' => function($sm) {
    					
    					    $auth = $sm->get('my_auth_service');
    					
    					    $adapter = new AuthAdapter($sm->get('Zend\Db\Adapter\Adapter'),
    					        'users',
    					        'user_email',
    					        'user_password',
    					        'MD5(?)'
    					    );
    					
    					    $auth->setAdapter($adapter);
    					
    					    return $auth;
    					},
    					
    					'Zend\Session\SessionManager' => function ($sm) {
    						$config = $sm->get('config');
    						if (isset($config['session'])) {
    							$session = $config['session'];
    					
    							$sessionConfig = null;
    							if (isset($session['config'])) {
    								$class = isset($session['config']['class'])  ? $session['config']['class'] : 'Zend\Session\Config\SessionConfig';
    								$options = isset($session['config']['options']) ? $session['config']['options'] : array();
    								$sessionConfig = new $class();
    								$sessionConfig->setOptions($options);
    							}
    					
    							$sessionStorage = null;
    							if (isset($session['storage'])) {
    								$class = $session['storage'];
    								$sessionStorage = new $class();
    							}
    					
    							$sessionSaveHandler = null;
    							if (isset($session['save_handler'])) {
    								// class should be fetched from service manager since it will require constructor arguments
    								$sessionSaveHandler = $sm->get($session['save_handler']);
    							}
    					
    							$sessionManager = new SessionManager($sessionConfig, $sessionStorage, $sessionSaveHandler);
    					
    							if (isset($session['validators'])) {
    								$chain = $sessionManager->getValidatorChain();
    								foreach ($session['validators'] as $validator) {
    									$validator = new $validator();
    									$chain->attach('session.validate', array($validator, 'isValid'));
    					
    								}
    							}
    						} else {
    							$sessionManager = new SessionManager();
    						}
    						Container::setDefaultManager($sessionManager);
    						return $sessionManager;
    					},
    			),
    			
    			
    		);
    }
}
