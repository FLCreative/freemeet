<?php
 namespace Application\Model;

 class UserBlacklist
 {
    protected $owner;
    protected $user;
    protected $added;
    
    public function getOwner()
    {
        return $this->owner;
    }

    public function getUser()
    {
        return $this->user;
    }

    public function getAdded()
    {
        return $this->added;
    }

    public function setOwner($owner)
    {
        $this->owner = $owner;
    }

     public function setUser($user)
    {
        $this->user = $user;
    }

    public function setAdded($added)
    {
        $this->added = $added;
    }
 
 }