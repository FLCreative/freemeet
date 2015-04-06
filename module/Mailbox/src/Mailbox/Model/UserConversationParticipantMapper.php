<?php
 namespace Mailbox\Model;

 use Zend\Db\Adapter\Adapter;
 use Zend\Stdlib\Hydrator\ClassMethods;
 use Zend\Db\Sql\Sql;
 use Zend\Db\ResultSet\HydratingResultSet;
 use Zend\Stdlib\Hydrator\NamingStrategy\FirstUnderscoreNamingStrategy;
 use Mailbox\Model\UserConversationParticipant;
 
 class UserConversationParticipantMapper
 {
     protected $tableName = 'users_conversations_participants';
     protected $dbAdapter;
     protected $sql;

     public function __construct(Adapter $dbAdapter)
     {
         $this->dbAdapter = $dbAdapter;
         $this->sql = new Sql($dbAdapter);
         $this->sql->setTable($this->tableName);
     }
     
     public function find($conversation, $user)
     {
         $select = $this->sql->select();
         $select->where(array('participant_id' => $user, 'participant_conversation' => $conversation));
    
         $statement = $this->sql->prepareStatementForSqlObject($select);
         $result = $statement->execute()->current();
         
         if (!$result) {
             return null;
         }
    
         $hydrator = new ClassMethods();
         $hydrator->setNamingStrategy(new FirstUnderscoreNamingStrategy);
         
         $participant = new UserConversationParticipant();
         $hydrator->hydrate($result, $participant);

         return $participant;
     }
     
     public function save(UserConversationParticipant $participant)
     {
         $hydrator = new ClassMethods();
          
         $hydrator->setNamingStrategy(new FirstUnderscoreNamingStrategy);
          
         $hydratedData = $hydrator->extract($participant);
          
         $data = array();
          
         foreach ($hydratedData as $key => $value)
         {
             $data['participant_'.$key] = $value;
         }
          
         // insert action
         $action = $this->sql->insert();
         $action->values($data); 
               
         $statement = $this->sql->prepareStatementForSqlObject($action);
         $result = $statement->execute();
                   
         return $result;
     }
     
     public function delete(UserConversationParticipant $participant)
     {
         $delete = $this->sql->delete();
         $delete->where(array('participant_id' => $participant->getId()));
          
         $statement = $this->sql->prepareStatementForSqlObject($delete);
         return $statement->execute();
     }
     
     public function findReceiver($conversation, $user)
     {
         $select = $this->sql->select();
         $select->where(array('participant_id != ?' => $user, 'participant_conversation' => $conversation));
         $select->join('users','user_id = participant_id', array());
         
         $statement = $this->sql->prepareStatementForSqlObject($select);
         $result = $statement->execute()->current();
          
         if (!$result) {
             return null;
         }
         
         $hydrator = new ClassMethods();
         $hydrator->setNamingStrategy(new FirstUnderscoreNamingStrategy);
          
         $participant = new UserConversationParticipant();
         $hydrator->hydrate($result, $participant);
         
         return $participant;
     }
     
     public function fetchAll($params = array())
     {
         $select = $this->sql->select();
         
         if(isset($params['owner']))
         {
             $select->where(array('photo_owner = ?'=>$params['owner']));
         }
         if(isset($params['type']))
         {
             $select->where(array('photo_type = ?'=>$params['type']));
         }               

         $statement = $this->sql->prepareStatementForSqlObject($select);
         $results = $statement->execute();

         $entityPrototype = new UserConversationParticipant();
         $hydrator = new ClassMethods();
         
         $hydrator->setNamingStrategy(new FirstUnderscoreNamingStrategy);
         
         $resultset = new HydratingResultSet($hydrator, $entityPrototype);
         $resultset->initialize($results);
         return $resultset;
     }
 }