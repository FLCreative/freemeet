<?php
 namespace Mailbox\Model;

 class UserMessageStatus
 {
    protected $messageId;
    protected $userId;
    protected $value;
    
	public function getMessageId()
    {
        return $this->messageId;
    }

	public function getUserId()
    {
        return $this->userId;
    }

	public function getValue()
    {
        return $this->value;
    }

	public function setMessageId($messageId)
    {
        $this->messageId = $messageId;
    }

	public function setUserId($userId)
    {
        $this->userId = $userId;
    }

	public function setValue($value)
    {
        $this->value = $value;
    }

	
 }