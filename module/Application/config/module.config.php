<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

return array(
    'router' => array(
        'routes' => array(
            'home' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route'    => '/',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Index',
                        'action'     => 'index',
                    ),
                ),
            ),
        		
            // The following is a route to simplify getting started creating
            // new controllers and actions without needing to create a new
            // module. Simply drop new controllers in, and you can access them
            // using the path /application/:controller/:action
            'application' => array(
                'type'    => 'Literal',
                'options' => array(
                    'route'    => '/application',
                    'defaults' => array(
                        '__NAMESPACE__' => 'Application\Controller',
                        'controller'    => 'Index',
                        'action'        => 'index',
                    ),
                ),
                'may_terminate' => true,
                'child_routes' => array(
                    'default' => array(
                        'type'    => 'Segment',
                        'options' => array(
                            'route'    => '/[:controller[/:action]]',
                            'constraints' => array(
                                'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'action'     => '[a-zA-Z][a-zA-Z0-9_-]*',
                            ),
                            'defaults' => array(
                            ),
                        ),
                    ),
                ),
            ),
            
            'account' => array(
            		'type'    => 'Literal',
            		'options' => array(
            				// Change this to something specific to your module
            				'route' => '/account',
            				'defaults' => array(
            						// Change this value to reflect the namespace in which
            						// the controllers for your module are found
            						'__NAMESPACE__' => 'Application\Controller',
            						'controller'    => 'Account',
            						'action'        => 'index',
            				),
            		),
            		'may_terminate' => true,
            		'child_routes' => array(
            				// This route is a sane default when developing a module;
            				// as you solidify the routes for your module, however,
            				// you may want to remove it and replace it with more
            				// specific routes.
                		    'auth' => array(
                		        'type'    => 'Literal',
                		        'options' => array(
                		            'route'    => '/auth',
                		            'defaults' => array(
                		                'action'        => 'auth'
                		            ),
                		        ),
                		    ),           
            				'profil' => array(
            						'type'    => 'Literal',
            						'options' => array(
            								'route'    => '/profil',
            								'defaults' => array(
            										'action'        => 'profil'
            								),
            						),
            				),
            				'preference' => array(
            						'type'    => 'Literal',
            						'options' => array(
            								'route'    => '/preference',
            								'defaults' => array(
            										'action'        => 'preference'
            								),
            						),
            				),
            
            				'edit-password' => array(
            						'type'    => 'Literal',
            						'options' => array(
            								'route'    => '/edit-password',
            								'defaults' => array(
            										'action'        => 'edit-password'
            								),
            						),
            				),
            
            				'visit' => array(
            						'type'    => 'Segment',
            						'options' => array(
            								'route'    => '/visit/[page/:page]',
            								'constraints' => array(
            										'page'=> '[0-9]+'),
            								'defaults' => array(
            										'action'        => 'visit'
            								),
            						),
            				),
            
            				'view' => array(
            						'type'    => 'Literal',
            						'options' => array(
            								'route'    => '/view',
            								'defaults' => array(
            										'action'        => 'view'
            								),
            						),
            				),
            
            				'flash' => array(
            						'type'    => 'Literal',
            						'options' => array(
            								'route'    => '/flash',
            								'defaults' => array(
            										'action'        => 'flash'
            								),
            						),
            				),
            
            				'favorite' => array(
            						'type'    => 'Literal',
            						'options' => array(
            								'route'    => '/favorite',
            								'defaults' => array(
            										'action'        => 'favorite'
            								),
            						),
            				),
            				
            				'logout' => array(
            						'type'    => 'Literal',
            						'options' => array(
            								'route'    => '/logout',
            								'defaults' => array(
            										'action'        => 'logout'
            								),
            						),
            				),
            		),
            ),
            
            // USER CONTROLLER
            
            'user' => array(
            		'type'    => 'Literal',
            		'options' => array(
            				// Change this to something specific to your module
            				'route'    => '/user',
            				'defaults' => array(
            						// Change this value to reflect the namespace in which
            						// the controllers for your module are found
            						'__NAMESPACE__' => 'Application\Controller',
            						'controller'    => 'User',
            						'action'        => 'index',
            				),
            		),
            		'may_terminate' => true,
            		'child_routes' => array(
            				// This route is a sane default when developing a module;
            				// as you solidify the routes for your module, however,
            				// you may want to remove it and replace it with more
            				// specific routes.
            				'view' => array(
            						'type'    => 'segment',
            						'options' => array(
            								'route'    => '/view/:id[/]',
            								'constraints' => array(
            										'id' => '[0-9]+',
            								),
            								'defaults' => array(
            										'action' => 'view'
            								),
            						),
            				),
            				'favorite' => array(
            						'type'    => 'literal',
            						'options' => array(
            								'route'    => '/favorite',
            								'defaults' => array(
            										'action' => 'favorite'
            								),
            						),
            				),
            				'deleteFavorite' => array(
            						'type'    => 'literal',
            						'options' => array(
            								'route'    => '/delete-favorite',
            								'defaults' => array(
            										'action' => 'deleteFavorite'
            								),
            						),
            				),
            				'flash' => array(
            						'type'    => 'literal',
            						'options' => array(
            								'route'    => '/flash',
            
            								'defaults' => array(
            										'action' => 'flash'
            								),
            						),
            				),
            		),
            ),
            
            
            // SEARCH CONTROLLER
            
            'search' => array(
            		'type'    => 'Literal',
            		'options' => array(
            				'route'    => '/search',
            				'defaults' => array(
            						'__NAMESPACE__' => 'Application\Controller',
            						'controller'    => 'Search',
            						'action'        => 'index',
            				),
            		),
            		'may_terminate' => true,
            		'child_routes' => array(
            				'default' => array(
            						'type'    => 'Segment',
            						'options' => array(
            								'route'    => '/[:controller[/:action]]',
            								'constraints' => array(
            										'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
            										'action'     => '[a-zA-Z][a-zA-Z0-9_-]*',
            								),
            								'defaults' => array(
            								),
            						),
            				),
            				
            				'online' => array(
            						'type'    => 'Literal',
            						'options' => array(
            								'route'    => '/online',
		            						'defaults' => array(
		            							'action'        => 'index',
		            						),
            						),
            				),
            				
            				'new' => array(
            						'type'    => 'Literal',
            						'options' => array(
            								'route'    => '/new',
            								'defaults' => array(
            										'action'        => 'index',
            								),
            						),
            				),
            				
            		),
            ),
            
        ),
    ),
    'service_manager' => array(
        'factories' => array(
            'translator' => 'Zend\I18n\Translator\TranslatorServiceFactory',
        ),
    ),
    'translator' => array(
        'locale' => 'fr_FR',
        'translation_file_patterns' => array(
            array(
                'type'     => 'gettext',
                'base_dir' => __DIR__ . '/../language',
                'pattern'  => '%s.mo',
            ),
        ),
    ),
    'controllers' => array(
        'invokables' => array(
            'Application\Controller\Index'   => 'Application\Controller\IndexController',
            'Application\Controller\Account' => 'Application\Controller\AccountController',
            'Application\Controller\User'  => 'Application\Controller\UserController',
            'Application\Controller\Search'  => 'Application\Controller\SearchController',
        ),
    ),
    'view_manager' => array(
        'display_not_found_reason' => true,
        'display_exceptions'       => true,
        'doctype'                  => 'HTML5',
        'not_found_template'       => 'error/404',
        'exception_template'       => 'error/index',
        'template_map' => array(
            'layout/layout'           => __DIR__ . '/../view/layout/layout.phtml',
            'layout/guest'            => __DIR__ . '/../view/layout/layout-guest.phtml',
            'application/index/index' => __DIR__ . '/../view/application/index/index.phtml',
            'error/404'               => __DIR__ . '/../view/error/404.phtml',
            'error/index'             => __DIR__ . '/../view/error/index.phtml',
        ),
        'template_path_stack' => array(
            __DIR__ . '/../view',
        ),
        'strategies' => array(
           'ViewJsonStrategy',
        ),
    ),
    'view_helpers' => array(
        'invokables' => array(
            'birthdaytoage'    => 'Application\View\Helper\BirthdayToAge',
            'currentuser'      => 'Application\View\Helper\CurrentUser',
            'userstats'        => 'Application\View\Helper\UserStats',
            'relativetime'     => 'Application\View\Helper\RelativeTime',
            'astrologicalsign' => 'Application\View\Helper\AstrologicalSign',
            'chatbox'          => 'Application\View\Helper\ChatBox'
        )
    ),
    'module_layouts' => array(
            'Account' => array(
                'default' => 'layout/layout',
            ),
            'Admin' => array(
                'default' => 'layout/layout-admin',
            ),
            'Mailbox' => array(
                'default' => 'layout/layout',
            ),
            'Search' => array(
                'default' => 'layout/layout',
            ),
            'User' => array(
                'default' => 'layout/layout',
            ),
            'Photo' => array(
                'default' => 'layout/layout',
            ),
     ),
);
