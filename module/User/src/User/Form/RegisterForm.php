<?php 

namespace User\Form;

use Zend\Form\Element;
use Zend\Form\Form;

class RegisterForm extends Form
{
    protected $captcha;

    public function __construct()
    {

        parent::__construct();

        $this->add(array(
        		'name' => 'birth_day',
        		'attributes' => array(
        				'placeholder' => 'JJ',
        				'class'		  => 'form-control'
        		),
        		'type'  => 'Text',
        ));
        
        $this->add(array(
        		'name' => 'birth_month',
        		'attributes' => array(
        				'placeholder' => 'MM',
        				'class'		  => 'form-control'
        		),
        		'type'  => 'Text',
        ));
        
        $this->add(array(
        		'name' => 'birth_year',
        		'attributes' => array(
        				'placeholder' => 'AAAA',
        				'class'		  => 'form-control'
        		),
        		'type'  => 'Text',
        ));
        
        $this->add(array(
            'name' => 'name',
            'attributes' => array(
                'placeholder' => 'Votre pseudo',
            	'class'		  => 'form-control'
            ),
            'type'  => 'Text',
        ));
        
        $this->add(array(
            'type' => 'Zend\Form\Element\Email',
            'name' => 'email',
            'attributes' => array(
                'placeholder' => 'Votre adresse email',
            	'class'		  => 'form-control'
            ),
        ));
        
        $this->add(array(
        		'name' => 'password',
        		'attributes' => array(
        				'placeholder' => 'Votre mot de passe',
        				'class'		  => 'form-control'
        		),
        		'type'  => 'Password',
        ));




        // We could also define the input filter here, or
        // lazy-create it in the getInputFilter() method.
    }
}