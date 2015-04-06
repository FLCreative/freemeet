<?php
 namespace Application\Model;

 class UserPhoto
 {
    protected $id;
    protected $owner;
    protected $filename;
    protected $status;
    protected $type;
    
	public function getId()
    {
        return $this->id;
    }

	public function setId($id)
    {
        $this->id = $id;
    }

	public function getOwner()
    {
        return $this->owner;
    }

	public function setOwner($owner)
    {
        $this->owner = $owner;
    }

	public function getFilename($size = false)
    {
        if($size)
        {
            $path_parts = pathinfo($this->filename);
        
            $ext = $path_parts['extension'];
            $filename = $path_parts['filename'];
        
            return $filename.'_'.$size.'.'.$ext;
        }
        
        return $this->filename;
    }

	public function setFilename($filename)
    {
        $this->filename = $filename;
    }

	public function getStatus()
    {
        return $this->status;
    }

	public function setStatus($status)
    {
        $this->status = $status;
    }

	public function getType()
    {
        return $this->type;
    }

	public function setType($type)
    {
        $this->type = $type;
    }
    
 }