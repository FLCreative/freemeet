<?php 

namespace Register\Form;

use Zend\Form\Form;
use Zend\Validator\Db\NoRecordExists;
use Zend\Validator\EmailAddress;

class RegisterForm extends Form
{
    public function __construct()
    {
        
        parent::__construct();
        
        $validator = new NoRecordExists(
            array(
                'table'   => 'users',
                'field'   => 'user_email',
            )
        );
        
        
        $this->add(array(
            'name' => 'name',
            'required' => true,
            'filters' => array(
                array('name' => 'Zend\Filter\StringTrim'),
                array('name' => 'Zend\Filter\StripTags'),
            ),
            'attributes' => array(
                'placeholder' => 'Votre pseudo',
            	'class'		  => 'form-control'
            ),
            'type'  => 'Text',
        ));
        
        $this->add(array(
            'type' => 'email',
            'name' => 'email',
            'attributes' => array(
                'placeholder' => 'Votre adresse email',
            	'class'		  => 'form-control'
            ),
            'required' => true,
            'filters' => array(
                array('name' => 'Zend\Filter\StringTrim'),
                array('name' => 'Zend\Filter\StripTags'),
            ),
        ));
        
        $this->add(array(
        		'name' => 'password',
                'type'  => 'password',
                'required' => true,
        		'attributes' => array(
        				'placeholder' => 'Votre mot de passe',
        				'class'		  => 'form-control'
        		),
        		
        ));




        // We could also define the input filter here, or
        // lazy-create it in the getInputFilter() method.
    }
}