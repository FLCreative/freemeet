<?php
 namespace Application\Model;

 class UserProfilSearch
 {
    protected $user;
    protected $type;
    protected $ageMin;
    protected $ageMax;
    protected $region;
    
	public function getUser()
    {
        return $this->user;
    }

	public function getType()
    {
        return $this->type;
    }

	public function getAgeMin()
    {
        return $this->ageMin;
    }

	public function getAgeMax()
    {
        return $this->ageMax;
    }

	public function getRegion()
    {
        return $this->region;
    }

	public function setUser($user)
    {
        $this->user = $user;
    }

	public function setType($type)
    {
        $this->type = $type;
    }

	public function setAgeMin($ageMin)
    {
        $this->ageMin = $ageMin;
    }

	public function setAgeMax($ageMax)
    {
        $this->ageMax = $ageMax;
    }

	public function setRegion($region)
    {
        $this->region = $region;
    }


 }