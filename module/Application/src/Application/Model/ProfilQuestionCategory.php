<?php
 namespace Application\Model;

 class ProfilQuestionCategory
 {
    protected $id;
    protected $name;
    protected $order;
	
	public function getId()
    {
        return $this->id;
    }

	public function setId($id)
    {
        $this->id = $id;
    }

	public function getName()
    {
        return $this->name;
    }

	public function setName($name)
    {
        $this->name = $name;
    }

	public function getOrder()
    {
        return $this->order;
    }

	public function setOrder($order)
    {
        $this->order = $order;
    }
 
 }