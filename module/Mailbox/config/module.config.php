<?php
return array(
    'controllers' => array(
        'invokables' => array(
            'Mailbox\Controller\Mailbox' => 'Mailbox\Controller\MailboxController',
        ),
    ),
    'router' => array(
        'routes' => array(
            'mailbox' => array(
                'type'    => 'Literal',
                'options' => array(
                    // Change this to something specific to your module
                    'route'    => '/mailbox',
                    'defaults' => array(
                        // Change this value to reflect the namespace in which
                        // the controllers for your module are found
                        '__NAMESPACE__' => 'Mailbox\Controller',
                        'controller'    => 'Mailbox',
                        'action'        => 'index',
                    ),
                ),
                'may_terminate' => true,
                'child_routes' => array(
                    
                    'view' => array(
                        'type'    => 'segment',
                        'options' => array(
                            'route'    => '/view/:id[/]',
                            'constraints' => array(
                                'id' => '[0-9]+',
                            ),
                            'defaults' => array(
                                'action' => 'view',
                            ),
                        ),
                    ),
                    
                    'compose' => array(
                        'type'    => 'segment',
                        'options' => array(
                            'route'    => '/compose/:user[/]',
                            'constraints' => array(
                                'user' => '[0-9]+',
                            ),
                            'defaults' => array(
                                'action' => 'compose',
                            ),
                        ),
                    ),
                    
                    'add-conversation' => array(
                        'type'    => 'Literal',
                        'options' => array(
                            'route'    => '/add-conversation',
                            'defaults' => array(
                                'action' => 'addConversation',
                            ),
                        ),
                    ),
                		
                	'load-messages' => array(
                        'type'    => 'segment',
                        'options' => array(
                            'route'    => '/load-messages/:conversation[/]',
                            'constraints' => array(
                                'conversation' => '[0-9]+',
                            ),
                            'defaults' => array(
                                'action' => 'loadMessages',
                            ),
                        ),
                	),
                		             		
                	'update-chatbox-status' => array(
                		'type'    => 'Literal',
                		'options' => array(
                			'route'    => '/update-chatbox-status',
                			'defaults' => array(
                			'action' => 'updateChatboxStatus',
                			),
                		),
                	),
                    
                    'reply' => array(
                        'type'    => 'Literal',
                        'options' => array(
                            'route'    => '/reply',
                            'defaults' => array(
                                'action' => 'reply',
                            ),
                        ),
                    ),
                    
                    'delete' => array(
                        'type'    => 'segment',
                        'options' => array(
                            'route'    => '/delete/:id[/]',
                            'constraints' => array(
                                'id' => '[0-9]+',
                            ),
                            'defaults' => array(
                                'action' => 'delete',
                            ),
                        ),
                    ),
                ),
            ),
        ),
    ),
    'view_manager' => array(
        'template_path_stack' => array(
            'Mailbox' => __DIR__ . '/../view',
        ),
    ),
);
