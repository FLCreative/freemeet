<?php 

namespace Login\Form;

use Zend\Form\Form;

class LoginForm extends Form
{
    protected $captcha;

    public function __construct()
    {

        parent::__construct();
        
        $this->add(array(
            'type' => 'Zend\Form\Element\Email',
            'name' => 'email',
            'required' => true,
            'filters'  => array(
                array('name' => 'StripTags'),
                array('name' => 'StringTrim'),
            ),
            'validators'  => array(
                array('name' => 'Email'),
            ),
            'attributes' => array(
                'placeholder' => 'Votre adresse email',
            	'class'		  => 'form-control'
            ),
        ));
        
        $this->add(array(
        		'name' => 'password',
                'required' => true,
                'filters'  => array(
                    array('name' => 'StripTags'),
                    array('name' => 'StringTrim'),
                ),
        		'attributes' => array(
        				'placeholder' => 'Votre mot de passe',
        				'class'		  => 'form-control'
        		),
        		'type'  => 'Password',
        ));

    }
}