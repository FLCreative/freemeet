<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/Mailbox for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Mailbox\Controller;

use Exception;
use Zend\Mvc\Controller\AbstractActionController;
use Mailbox\Model\UserConversation;
use Mailbox\Model\UserConversationParticipant;
use Mailbox\Model\UserMessage;
use Zend\Db\Sql\Expression;
use Mailbox\Model\UserMessageStatus;
use Mailbox\Form\AddConversationForm;
use Mailbox\Form\ReplyConversationForm;
use Zend\View\Model\JsonModel;
use ElephantIO\Client;
use ElephantIO\Engine\SocketIO\Version1X;

class MailboxController extends AbstractActionController
{
    public function indexAction()
    {
    	$this->layout()->fullLayout = true;
    	
    	$form = new ReplyConversationForm('reply_conversation');
    	
    	$mapper = $this->getServiceLocator()->get('ConversationMapper');
        $results = $mapper->fetchAll(array('owner' => $this->identity()->user_id));
        
        $messageMapper = $this->getServiceLocator()->get('MessageMapper');
        
        $conversations = array();
        
        foreach($results as $index => $conversation)
        {
	        if($index === 0)
	        {
		        // Set message status to read   
		        $messages = $messageMapper->fetchAll(array('conversation'=>$conversation->getId(), 'status'=> 'unread'));
		         
		        if(count($messages))
		        {	        	 
		        	$messageStatusMapper = $this->getServiceLocator()->get('MessageStatusMapper');
		        	 
		        	foreach($messages as $message)
		        	{
		        		$messageStatusMapper->updateStatus($message->getId(), $this->identity()->user_id, 'read');
		        	}
		        }
		         
		        $messages = $messageMapper->fetchAll(array('conversation'=>$conversation->getId(),'owner'=>$this->identity()->user_id));

		        $currentConversation = $conversation;
		        
		        $form->get('conversation')->setValue($conversation->getId());
	        	
		        $conversations[] = $conversation;
	        }
        }
        
        return array(
            'conversations'=> $conversations,
        	'conversation' => $currentConversation,
        	'messages'	   => $messages,
        	'form'		   => $form
        );
    }

    public function viewAction()
    {
       $id = $this->params('id');

       $conversationMapper = $this->getServiceLocator()->get('ConversationMapper');
       $conversation = $conversationMapper->find($id);
       
       if($conversation)
       {   
           $mapper = $this->getServiceLocator()->get('ParticipantMapper');
           $participant = $mapper->find($conversation->getId(), $this->identity()->user_id);
           
           if($participant)
           {
               
               $messageMapper = $this->getServiceLocator()->get('MessageMapper');
                
               // Set message status to read
               
               $messages = $messageMapper->fetchAll(array('conversation'=>$conversation->getId(), 'status'=> 'unread'));
               
               if(count($messages))
               {
                                       
                   $messageStatusMapper = $this->getServiceLocator()->get('MessageStatusMapper');
                   
                   foreach($messages as $message)
                   {
                       $messageStatusMapper->updateStatus($message->getId(), $this->identity()->user_id, 'read');
                   }
               }
               
               $messages = $messageMapper->fetchAll(array('conversation'=>$conversation->getId(),'owner'=>$this->identity()->user_id));
               
               $form = new ReplyConversationForm();
               
               $form->get('conversation')->setValue($conversation->getId());
               
               return array('conversation'=> $conversation, 'messages' => $messages, 'form' => $form);
           }
           
           $this->flashMessenger()->setNamespace('error')->addMessage('Cette discution n\'existe pas ou a été supprimée !');
           return $this->redirect()->toRoute('mailbox');
       }
       
       else
       {
           $this->flashMessenger()->setNamespace('error')->addMessage('Cette discution n\'existe pas ou a été supprimée !');
           return $this->redirect()->toRoute('mailbox');
       }
    }
    
    public function composeAction()
    {
             
        if($this->params('user') == $this->identity()->user_id)
        {
            $this->flashMessenger()->setNamespace('error')->addMessage('Vous ne pouvez pas vous envoyer de message !');
            
            return $this->redirect()->toRoute('mailbox');
        }
        
        $userMapper = $this->getServiceLocator()->get('userMapper');
        
        $user = $userMapper->find($this->params('user'));
        
        if(!$user || $user->getStatus() != 'active')
        {
        	if ($this->getRequest()->isXmlHttpRequest()) 
        	{
        		return new JsonModel(array());
        	}
        	
        	$this->flashMessenger()->setNamespace('error')->addMessage('Ce membre n\'existe pas, impossible d\'envoyer un message.');
            
            return $this->redirect()->toRoute('mailbox');
        }
        
        // Find if conversation already exist
        
        $mapper = $this->getServiceLocator()->get('ConversationMapper');
        
        $conversation = $mapper->findByUsers($this->identity()->user_id, $user->getId());        
        
        if($conversation)
        {       	
        	if ($this->getRequest()->isXmlHttpRequest())
        	{
        		return new JsonModel(array('username'=>$user->getName(),'id'=>$conversation->getId()));
        	}
        	
        	return $this->redirect()->toRoute('mailbox/view', array('id'=>$conversation->getId()));
        }

        $form = new AddConversationForm();
        
        $form->get('receiver')->setValue($this->params('user'));
        
        return array('user'=>$user,'form'=>$form);
    }
    
    public function replyAction()
    {
               
        if($this->getRequest()->isPost())
        {
            $data = $this->getRequest()->getPost();
      
            $form = new ReplyConversationForm();
        
            $form->setData($data);
        
            if($form->isValid())
            {
                
                $mapper = $this->getServiceLocator()->get('ConversationMapper');
                $conversation = $mapper->find($data['conversation']);
                 
                if($conversation)
                {
                    $mapper = $this->getServiceLocator()->get('ParticipantMapper');
                    $participant = $mapper->find($conversation->getId(), $this->identity()->user_id);
                     
                    if($participant)
                    {
                
                        $sm = $this->getServiceLocator();
                
                        $receiver = $mapper->findReceiver($conversation->getId(), $this->identity()->user_id);
                        
                        $userMapper = $sm->get('userMapper');
                        
                        $user = $userMapper->find($receiver->getId());
                        
                        $db = $sm->get('Zend\Db\Adapter\Adapter');
                
                        $con = $db->getDriver()->getConnection();
                        $con->beginTransaction();
                
                        try {
                        
                            // Add the message
                            $messageMapper = $sm->get('MessageMapper');
                            $message = new UserMessage();
                
                            $message->setAuthor($this->identity()->user_id);
                            $message->setContent($data['content']);
                            $message->setDate(new Expression('UTC_TIMESTAMP()'));
                            $message->setConversationId($conversation->getId());
                
                            $messageMapper->save($message);
                
                            // Add message status
                            $statusMapper = $sm->get('MessageStatusMapper');
                            $status = new UserMessageStatus();
                
                            $status->setMessageId($message->getId());
                            $status->setUserId($this->identity()->user_id);
                            $status->setValue('read');
                
                            $statusMapper->save($status);
                
                            $status->setUserId($receiver->getId());
                            $status->setValue('unread');
                
                            $statusMapper->save($status);
                			
                            //
                            
                            $currentUser = $sm->get('currentuser');
                            
                            $con->commit();
                            
				            $client = new Client(new Version1X('http://localhost:3000'));
				            $client->initialize();
                            

                            $client->emit('new message', 
                            		array(
                            		'conversation' => $conversation->getId(),
                            		'sender'  	   => $currentUser->getName(),
                            		'receiver'     => $user->getName(),
                            		'photo'        => $currentUser->getPhoto('xsmall'),
                            		'content'      => nl2br($message->getContent())
                            	)
                            );                        
                            
                            if($this->getRequest()->isXmlHttpRequest())
                            {                                                               
                                return new JsonModel(
                                		array(
                                		'status'       => 'success',
                                		'date'         => 'à l\'instant',
                                		'message'      => nl2br($message->getContent()),
                                	    'conversation' => $conversation->getId(),
                                		'photo'        => $currentUser->getPhoto('small'))
                                );
                            }
                
                            $this->flashMessenger()->setNamespace('success')->addMessage('Votre message a été envoyé !');
                            
                            return $this->redirect()->toRoute('mailbox');
                        }
                
                        catch(Exception $e)
                        {
                            $con->rollback();
                
                            $this->flashMessenger()->setNamespace('error')->addMessage($e->getMessage());
                        }
                    }
                }
            }
        }
        
        return $this->redirect()->toRoute('mailbox');
    }
    
    public function addConversationAction()
    {
        if($this->getRequest()->isPost())
        {
            $data = $this->getRequest()->getPost();
            
            // Check if receiver is not the sender
            if($data['receiver'] == $this->identity()->user_id)
            {
                $this->flashMessenger()->setNamespace('error')->addMessage('Vous ne pouvez pas vous envoyer de message !');
            
                return $this->redirect()->toRoute('mailbox');
            }
            
            $userMapper = $this->getServiceLocator()->get('userMapper');
            
            $user = $userMapper->find($data['receiver']);
            
            // Check if receiver exist and is active user
            if(!$user || $user->getStatus() != 'active')
            {
                $this->flashMessenger()->setNamespace('error')->addMessage('Ce membre n\'existe pas, impossible d\'envoyer un message.');
            
                return $this->redirect()->toRoute('mailbox');
            }
            
            $form = new AddConversationForm();
            
            $form->setData($data);
            
            if($form->isValid())
            {
                $sm = $this->getServiceLocator();
                
                $db = $sm->get('Zend\Db\Adapter\Adapter');

                $con = $db->getDriver()->getConnection();
                $con->beginTransaction();
                
                try {
                
                    // Create the conversation
                    $conversationMapper = $sm->get('ConversationMapper');
                    $conversation = new UserConversation();
                    
                    $conversation->setDate(new Expression('UTC_TIMESTAMP()'));
                    $conversationMapper->save($conversation);
                    
                    // Add the participants
                    $participantMapper = $sm->get('ParticipantMapper');
                    $participant = new UserConversationParticipant();
                    
                    // Add the author
                    $participant->setId($this->identity()->user_id);
                    $participant->setConversation($conversation->getId());
                    $participant->setChatboxStatus('close');
                                    
                    $participantMapper->save($participant);
                    
                    // Add the receiver
                    $participant->setId($data['receiver']);              
                    $participantMapper->save($participant);
                    
                    // Add the message
                    $messageMapper = $sm->get('MessageMapper');
                    $message = new UserMessage();
                    
                    $message->setAuthor($this->identity()->user_id);
                    $message->setContent($data['content']);
                    $message->setDate(new Expression('UTC_TIMESTAMP()'));
                    $message->setConversationId($conversation->getId());
                    
                    $messageMapper->save($message);                   
                    
                    // Add message status
                    $statusMapper = $sm->get('MessageStatusMapper');
                    $status = new UserMessageStatus();
                    
                    $status->setMessageId($message->getId());
                    $status->setUserId($this->identity()->user_id);
                    $status->setValue('read');
                    
                    $statusMapper->save($status);
                    
                    $status->setUserId($data['receiver']);
                    $status->setValue('unread');
                    
                    $statusMapper->save($status);                    
                    
                    $con->commit();
                    
                    $this->flashMessenger()->setNamespace('success')->addMessage('Votre message a été envoyé !');
                    
                    return $this->redirect()->toRoute('mailbox');
                    
                }
                
                catch(Exception $e)
                {                   
                    $con->rollback();
                    
                    $this->flashMessenger()->setNamespace('error')->addMessage($e->getMessage());
                }
            }
        }
        
        return $this->redirect()->toRoute('mailbox');
    }
    
    public function deleteAction()
    {
       $id = $this->params('id');       
       
       $conversationMapper = $this->getServiceLocator()->get('ConversationMapper');
       $conversation = $conversationMapper->find($id);
        
       if($conversation)
       {
           $mapper = $this->getServiceLocator()->get('ParticipantMapper');
           $participant = $mapper->find($conversation->getId(), $this->identity()->user_id);
            
           if($participant)
           {
       
               $messageMapper = $this->getServiceLocator()->get('MessageMapper');
                        
               // Set message status to read
               
               $messages = $messageMapper->fetchAll(array('conversation'=>$conversation->getId()));
               
               if(count($messages))
               {

                   $db = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
                   
                   $con = $db->getDriver()->getConnection();
                   $con->beginTransaction();
                   
                   try {
                   
                       $messageStatusMapper = $this->getServiceLocator()->get('MessageStatusMapper');
                       
                       foreach($messages as $message)
                       {
                           $messageStatusMapper->updateStatus($message->getId(), $this->identity()->user_id, 'deleted');
                       }
                       
                       // Find messages which have same deleted status and delete them from database
                       $messages = $messageMapper->fetchToDelete($conversation);
                       
                       foreach($messages as $message)
                       {
                           $messageMapper->delete($message);
                           $messageStatusMapper->delete($message);
                       }
                       
                       // If conversation has 0 message, delete it
                       
                       if(!$conversationMapper->countMessage($conversation))
                       {
                           $conversationMapper->delete($conversation);
                       }
                       
                       $con->commit();
                       
                       $this->flashMessenger()->setNamespace('success')->addMessage('La conversation a bien été supprimée');
                   }
                   catch(Exception $e)
                   {                   
                       $con->rollback();
                    
                       $this->flashMessenger()->setNamespace('error')->addMessage($e->getMessage());
                   }
                                   
                   return $this->redirect()->toRoute('mailbox');
               }
           }
       }
       
       return $this->redirect()->toRoute('mailbox');             
       
    }
    
    public function loadMessagesAction()
    {
    	$id = $this->params('conversation');
    
    	$conversationMapper = $this->getServiceLocator()->get('ConversationMapper');
    	$conversation = $conversationMapper->find($id);
    	 
    	if($conversation)
    	{
    		$mapper = $this->getServiceLocator()->get('ParticipantMapper');
    		$participant = $mapper->find($conversation->getId(), $this->identity()->user_id);
    		 
    		if($participant)
    		{    			 
    			$messageMapper = $this->getServiceLocator()->get('MessageMapper');    			 
    			 
    			$rows = $messageMapper->fetchAll(array('conversation'=>$conversation->getId(),'owner'=>$this->identity()->user_id));    			 
    			
    			$partialLooper = $this->getServiceLocator()->get('viewhelpermanager')->get('partialLoop');
    			
    			$partialLooper->setObjectKey('message');
    			
    			$messages = $partialLooper('partial/chatbox-message.phtml',$rows);
    			
    			return new JsonModel(array('messages' => $messages));
    		}
    		 
    	}

    }
    
    public function updateChatboxStatusAction()
    {
    	$data = $this->getRequest()->getPost();
    	 
    	$conversationMapper = $this->getServiceLocator()->get('ConversationMapper');
    	$conversation = $conversationMapper->find($data['conversation']);
    
    	if($conversation)
    	{
    		$mapper = $this->getServiceLocator()->get('ParticipantMapper');
    		$participant = $mapper->find($conversation->getId(), $this->identity()->user_id);
    
    		if($participant)
    		{
    			$status = array('open','reduce','close'); 
    			
    			if(in_array($data['status'],$status))
    			{
	    			$participant->setChatboxStatus($data['status']);
	    			
	    			$mapper->update($participant);
    			}
    			
    			return new JsonModel();
    		}
    	}    	 
    	 
    }
    
}
