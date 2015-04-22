<?php 
namespace Application\Form;


use Zend\Form\Form;
use Zend\InputFilter;

class EditPasswordForm extends Form
{
    
    public function __construct($name = null, $options = array())
    {
        parent::__construct($name, $options);
              
        $this->addElements();
        $this->addInputFilter();
    }

    public function addElements()
    {
        $this->add(array(
            'type' => 'Zend\Form\Element\Password',
            'name' => 'currentPassword',
            'required' => true,
            'attributes' => array(
                'placeholder' => 'Saisissez votre mot de passe',
            	'class'		  => 'form-control'
            ),
        ));
        
        $this->add(array(
            'type' => 'Zend\Form\Element\Password',
            'name' => 'newPassword',
            'required' => true,
            'attributes' => array(
                'placeholder' => 'Saisissez votre nouveau mot de passe',
                'class'		  => 'form-control'
            ),
        ));
        
        $this->add(array(
            'type' => 'Zend\Form\Element\Password',
            'name' => 'confirmPassword',
            'required' => true,
            'attributes' => array(
                'placeholder' => 'Confirmez le mot de passe',
                'class'		  => 'form-control'
            ),
        ));
    }
    
    public function addInputFilter()
    {
        $inputFilter = new InputFilter\InputFilter();
          
        $inputFilter->add(array(
            'name'     => 'currentPassword',
            'required' => true,
            'filters'  => array(
                array('name' => 'StripTags'),
                array('name' => 'StringTrim'),
            ),
        ));
        
        $inputFilter->add(array(
            'name'     => 'newPassword',
            'required' => true,
            'filters'  => array(
                array('name' => 'StripTags'),
                array('name' => 'StringTrim'),
            ),
            'validators' => array(
                array(
                    'name'    => 'StringLength',
                    'options' => array(
                        'encoding' => 'UTF-8',
                        'min'      => 6,
                    ),
                )
            ),
        ));
        
        $inputFilter->add(array(
            'name'     => 'confirmPassword',
            'required' => true,
            'filters'  => array(
                array('name' => 'StripTags'),
                array('name' => 'StringTrim'),
            ),
            'validators' => array(
                array(
                    'name'    => 'StringLength',
                    'options' => array(
                        'encoding' => 'UTF-8',
                        'min'      => 6,
                    ),
                ),
                array(
                    'name' => 'identical',
                    'options' => array('token' => 'newPassword' )
                ),
            ),
        ));
        
    
        $this->setInputFilter($inputFilter);
    }
}

?>