<?php
 namespace Application\Model;

 class UserWarning
 {
    protected $id;
    protected $author;
    protected $user;
    protected $message;
    protected $date;
    
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

	public function getMessage()
    {
        return $this->message;
    }

	public function getDate()
    {
        return $this->date;
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

	public function setMessage($message)
    {
        $this->message = $message;
    }

	public function setDate($date)
    {
        $this->date = $date;
    }

    
 }