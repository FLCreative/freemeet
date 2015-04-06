<?php
return array(
    'controllers' => array(
        'invokables' => array(
            'Account\Controller\Account' => 'Account\Controller\AccountController',
        ),
    ),
    'router' => array(
        'routes' => array(
            'account' => array(
                'type'    => 'Literal',
                'options' => array(
                    // Change this to something specific to your module
                    'route' => '/account',
                    'defaults' => array(
                        // Change this value to reflect the namespace in which
                        // the controllers for your module are found
                        '__NAMESPACE__' => 'Account\Controller',
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
                ),
            ),
        ),
    ),
    'view_manager' => array(
        'template_path_stack' => array(
            'Account' => __DIR__ . '/../view',
        ),
    ),
);
