<?php
 namespace Mailbox\Model;

 use Zend\Db\Adapter\Adapter;
 use Zend\Stdlib\Hydrator\ClassMethods;
 use Zend\Db\Sql\Sql;
 use Zend\Db\ResultSet\HydratingResultSet;
 use Zend\Stdlib\Hydrator\NamingStrategy\FirstUnderscoreNamingStrategy;
 use Zend\Stdlib\Hydrator\Filter\MethodMatchFilter;
 use Zend\Stdlib\Hydrator\Filter\FilterComposite;
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
         
         $hydrator->addFilter(
         		"Username",
         		new MethodMatchFilter("getUsername"),
         		FilterComposite::CONDITION_AND
         );
          
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
     
     public function update(UserConversationParticipant $participant)
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
     	$action = $this->sql->update();
     	$action->set($data);
     	$action->where(array(
     			'participant_id'           => $participant->getId(),
     			'participant_conversation' => $participant->getConversation())
     	);
     	 
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
         $select->join('users','user_id = participant_id', array('participant_username'=>'user_name'));
         
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
         
         if(isset($params['status']))
         {
            if(is_array($params['status']))
            { 
            	$select->where->in('participant_chatbox_status',$params['status']);
            }
            else
            {
            	$select->where(array('participant_chatbox_status = ?' => $params['status']));
            }
         }

         if(isset($params['participant']))
         {
         	$select->where(array('participant_id = ?' => $params['participant']));
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