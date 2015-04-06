<?php
 namespace Application\Model;

 class ProfilQuestion
 {
    protected $id;
    protected $title;
    protected $category;
	
	public function getId()
    {
        return $this->id;
    }

	public function setId($id)
    {
        $this->id = $id;
    }
	
	public function getTitle()
    {
        return $this->title;
    }

	public function setTitle($title)
    {
        $this->title = $title;
    }

	public function getCategory()
    {
        return $this->category;
    }

	public function setCategory($category)
    {
        $this->category = $category;
    }


 
 }