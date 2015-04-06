<?php
 namespace Application\Model;

 class UserAction
 {
    protected $id;
    protected $author;
    protected $user;
    protected $added;
    protected $type;
    
    protected $username;
    
	public function getId()
    {
        return $this->id;
    }

	public function getAuthor()
    {
        return $this->author;
    }

	public function getUser()
    {
        return $this->user;
    }

	public function getAdded()
    {
        return $this->added;
    }

	public function getType()
    {
        return $this->type;
    }

	public function setId($id)
    {
        $this->id = $id;
    }

	public function setAuthor($author)
    {
        $this->author = $author;
    }

	public function setUser($user)
    {
        $this->user = $user;
    }

	public function setAdded($added)
    {
        $this->added = $added;
    }

	public function setType($type)
    {
        $this->type = $type;
    }
    
	public function getUsername()
    {
        return $this->username;
    }

	public function setUsername($username)
    {
        $this->username = $username;
    }


 }