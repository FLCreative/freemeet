<?php 
namespace Mailbox\Form;


use Zend\Form\Form;
use Zend\InputFilter;

class AddConversationForm extends Form
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
            'type' => 'Zend\Form\Element\Textarea',
            'name' => 'content',
            'label'=> 'Message',
            'required' => true,
            'attributes' => array(
                'placeholder' => 'Saisissez votre message',
                'class'		  => 'form-control'
            ),
        ));       
        
        $this->add(array(
            'type' => 'Zend\Form\Element\Hidden',
            'name' => 'receiver',
            'required' => true,
            'attributes' => array(
                'class'		  => 'form-control'
            ),
        ));
        
        $this->add(array(
            'type' => 'Zend\Form\Element\Submit',
            'name' => 'submit',
            'attributes' => array(
                'value' => 'Envoyer le message',
                'class' => 'btn btn-primary',
                'id' => 'submitbutton',
            ),
        ));
    }
    
    public function addInputFilter()
    {
        $inputFilter = new InputFilter\InputFilter();
          
        $inputFilter->add(array(
            'name'     => 'content',
            'required' => true,
            'filters'  => array(
                array('name' => 'StripTags'),
                array('name' => 'StringTrim'),
            ),
        ));
        
        
        $inputFilter->add(array(
            'name'     => 'receiver',
            'required' => true,
            'validators' => array(
                array(
                    'name'    => 'Digits',                    
                ),
            ),
        ));
        
    
        $this->setInputFilter($inputFilter);
    }
}

?>