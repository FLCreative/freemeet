<?php
return array(
    'controllers' => array(
        'invokables' => array(
            'Search\Controller\Index' => 'Search\Controller\IndexController',
        	'Search\Controller\Result' => 'Search\Controller\ResultController',
        ),
    ),
    'router' => array(
        'routes' => array(
            'search' => array(
                'type'    => 'Literal',
                'options' => array(
                    // Change this to something specific to your module
                    'route'    => '/search',
                    'defaults' => array(
                        // Change this value to reflect the namespace in which
                        // the controllers for your module are found
                        '__NAMESPACE__' => 'Search\Controller',
                        'controller'    => 'Index',
                        'action'        => 'index',
                    ),
                ),
                'may_terminate' => true,
                'child_routes' => array(
                    // This route is a sane default when developing a module;
                    // as you solidify the routes for your module, however,
                    // you may want to remove it and replace it with more
                    // specific routes.
                    'online' => array(
                        'type'    => 'Literal',
                        'options' => array(
                            'route'    => '/online',
                            'defaults' => array(
                                'action'        => 'online'
                            ),
                        ),
                    ),
                    
                    'new' => array(
                        'type'    => 'Literal',
                        'options' => array(
                            'route'    => '/search/new',
                            'defaults' => array(
                                'action'        => 'new'
                            ),
                        ),
                    ),
                ),
            ),
        ),
    ),
    'view_manager' => array(
        'template_path_stack' => array(
            'Search' => __DIR__ . '/../view',
        ),

    ),
);
