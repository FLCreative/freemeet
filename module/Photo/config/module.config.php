<?php
return array(
    'controllers' => array(
        'invokables' => array(
            'Photo\Controller\Photo' => 'Photo\Controller\PhotoController',
        ),
    ),
    'router' => array(
        'routes' => array(
            'photo' => array(
                'type'    => 'Literal',
                'options' => array(
                    // Change this to something specific to your module
                    // Change this to something specific to your module
                    'route' => '/photo',
                    'defaults' => array(
                        // Change this value to reflect the namespace in which
                        // the controllers for your module are found
                        '__NAMESPACE__' => 'Photo\Controller',
                        'controller'    => 'Photo',
                        'action'        => 'index',
                    ),
                ),
                'may_terminate' => true,
                'child_routes' => array(                  
                    'add' => array(
                        'type'    => 'Literal',
                        'options' => array(
                            'route'    => '/add',
                            'defaults' => array(
                                'action'        => 'add',
                            ),
                        ),
                    ),
                    
                    'delete' => array(
                        'type'    => 'Segment',
                        'options' => array(
                            'route'    => '/delete/:id',
                            'constraints' => array(
                                'id' => '[0-9]+',
                            ),
                            'defaults' => array(
                                'action'        => 'delete',
                            ),
                        ),
                    ),
                    
                    'crop' => array(
                        'type'    => 'Literal',
                        'options' => array(
                            'route'    => '/crop',
                            'defaults' => array(
                                'action'        => 'crop',
                            ),
                        ),
                    ),
                    
                ),
            ),
        ),
    ),
    'view_manager' => array(
        'template_path_stack' => array(
            'Photo' => __DIR__ . '/../view',
        ),
    ),
);
