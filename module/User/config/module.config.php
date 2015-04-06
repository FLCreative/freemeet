<?php
return array(
    'controllers' => array(
        'invokables' => array(
            'User\Controller\User' => 'User\Controller\UserController',
        	'User\Controller\Register' => 'User\Controller\RegisterController',
        ),
    ),
    'router' => array(
        'routes' => array(
            'user' => array(
                'type'    => 'Literal',
                'options' => array(
                    // Change this to something specific to your module
                    'route'    => '/user',
                    'defaults' => array(
                        // Change this value to reflect the namespace in which
                        // the controllers for your module are found
                        '__NAMESPACE__' => 'User\Controller',
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
        ),
    ),
    'view_manager' => array(
        'template_path_stack' => array(
            'User' => __DIR__ . '/../view',
        ),

    ),
);
