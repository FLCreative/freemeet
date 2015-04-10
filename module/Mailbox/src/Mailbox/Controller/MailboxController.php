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

class MailboxController extends AbstractActionController
{
    public function indexAction()
    {
        $mapper = $this->getServiceLocator()->get('ConversationMapper');
        $conversations = $mapper->fetchAll(['owner' => $this->identity()->user_id]);
             
        return array(
            'conversations'=> $conversations
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
               
               $messages = $messageMapper->fetchAll(['conversation'=>$conversation->getId(),'owner'=>$this->identity()->user_id]);
               
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
            $this->flashMessenger()->setNamespace('error')->addMessage('Ce membre n\'existe pas, impossible d\'envoyer un message.');
            
            return $this->redirect()->toRoute('mailbox');
        }
        
        // Find if conversation already exist
        
        $mapper = $this->getServiceLocator()->get('ConversationMapper');
        
        $conversation = $mapper->findByUsers($this->identity()->user_id, $user->getId());
        
        if($conversation->getId())
        {
            return $this->redirect()->toRoute('mailbox/reply', array('id'=>$conversation->getId()));
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
                
                            $con->commit();
                            
                            if($this->getRequest()->isXmlHttpRequest())
                            {
                                $user = $sm->get('currentuser');
                                
                                return new JsonModel(['status'=>'success','date'=>'à l\'instant','message'=>nl2br($message->getContent()),'photo'=>$user->getPhoto('small')]);
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
               
               $messages = $messageMapper->fetchAll(['conversation'=>$conversation->getId()]);
               
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
}
