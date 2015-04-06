<?php
 namespace Mailbox\Model;

 class UserConversationParticipant
 {
    protected $id;
    protected $conversation;
    protected $chatboxStatus;

	public function getId()
    {
        return $this->id;
    }

	public function setId($id)
    {
        $this->id = $id;
    }
    
    public function getConversation()
    {
        return $this->conversation;
    }
    
    public function setConversation($conversation)
    {
        $this->conversation = $conversation;
    }
    
	public function getChatboxStatus()
    {
        return $this->chatboxStatus;
    }

	public function setChatboxStatus($chatboxStatus)
    {
        $this->chatboxStatus = $chatboxStatus;
    }


 }