<?php
 namespace Mailbox\Model;

 class UserConversation
 {
    protected $id;
    protected $date;
    
    protected $username;
    protected $photo;
    protected $type;
    protected $lastMessage;
    protected $lastUser;
    protected $lastMessageStatus;
    
	public function getId()
    {
        return $this->id;
    }

	public function setId($id)
    {
        $this->id = $id;
    }
    
	public function getDate()
    {
        return $this->date;
    }

	public function setDate($date)
    {
        $this->date = $date;
    }
    
	public function getUsername()
    {
        return $this->username;
    }

	public function setUsername($username)
    {
        $this->username = $username;
    }
    
	public function getPhoto($size = false)
    {
        if(!strlen($this->photo))
        {
            if($this->type == 1)
            {
                $file =  '/images/default_man.png';
            }
            else
            {
                $file = '/images/default_woman.png';
            }
            
            if($size)
            {
                $path_parts = pathinfo($file);
                
                $ext = $path_parts['extension'];
                $filename = $path_parts['filename'];
                
                return '/images/'.$filename.'_'.$size.'.'.$ext;
            }
            
            return $file;
        }
        
        if($size)
        {
            $path_parts = pathinfo($this->photo);
            
            $ext = $path_parts['extension'];
            $filename = $path_parts['filename'];
            
            return '/photos/'.$filename.'_'.$size.'.'.$ext;
        }
        
        return '/photos/'.$this->photo;
    }

	public function setPhoto($photo)
    {
        $this->photo = $photo;
    }

	public function getType()
    {
        return $this->type;
    }

	public function setType($type)
    {
        $this->type = $type;
    }
	
	public function getLastMessage()
    {
        return $this->lastMessage;
    }

	public function setLastMessage($lastMessage)
    {
        $this->lastMessage = $lastMessage;
    }
    
    public function getLastUser()
    {
        return $this->lastUser;
    }

    public function setLastUser($lastUser)
    {
        $this->lastUser = $lastUser;
    }
    
    public function getLastMessageStatus()
    {
        return $this->lastMessageStatus;
    }

    public function setLastMessageStatus($lastMessageStatus)
    {
        $this->lastMessageStatus = $lastMessageStatus;
    }


 }