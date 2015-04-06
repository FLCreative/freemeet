<?php
 namespace Mailbox\Model;

 use DateTime;
 
 class UserMessage
 {
    protected $id;
    protected $author;
    protected $content;
    protected $date;
    protected $conversationId;
    
    protected $username;
    protected $type;
    protected $photo;
    
	public function getId()
    {
        return $this->id;
    }

	public function setId($id)
    {
        $this->id = $id;
    }

	public function getAuthor()
    {
        return $this->author;
    }

	public function setAuthor($author)
    {
        $this->author = $author;
    }

	public function getContent()
    {
        return $this->content;
    }

	public function setContent($content)
    {
        $this->content = $content;
    }

	public function getDate($returnDateTime = false)
    {
        if($returnDateTime)
        {
            $date = new DateTime($this->date);
            
            return $date->format('Y-m-d H:i:sP');
        }
        
        return $this->date;
    }

	public function setDate($date)
    {
        $this->date = $date;
    }
    
	public function getConversationId()
    {
        return $this->conversationId;
    }

	public function setConversationId($conversationId)
    {
        $this->conversationId = $conversationId;
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

	
 }