<?php
 namespace Mailbox\Model;

 use Zend\Db\Adapter\Adapter;
 use Zend\Stdlib\Hydrator\ClassMethods;
 use Zend\Db\Sql\Sql;
 use Zend\Db\ResultSet\HydratingResultSet;
 use Zend\Stdlib\Hydrator\NamingStrategy\FirstUnderscoreNamingStrategy;
 use Zend\Stdlib\Hydrator\Filter\MethodMatchFilter;
 use Zend\Stdlib\Hydrator\Filter\FilterComposite;
 use Mailbox\Model\UserConversation;
 use Zend\Db\Sql\Expression;
		 
 class UserConversationMapper
 {
     protected $tableName = 'users_conversations';
     protected $dbAdapter;
     protected $sql;

     public function __construct(Adapter $dbAdapter)
     {
         $this->dbAdapter = $dbAdapter;
         $this->sql = new Sql($dbAdapter);
         $this->sql->setTable($this->tableName);
     }
     
     public function find($id)
     {
         $select = $this->sql->select();
         $select->where(array('conversation_id' => $id));
    
         $statement = $this->sql->prepareStatementForSqlObject($select);
         $result = $statement->execute()->current();
         
         if (!$result) {
             return null;
         }
    
         $hydrator = new ClassMethods();
         $hydrator->setNamingStrategy(new FirstUnderscoreNamingStrategy);
         
         $conversation = new UserConversation();
         $hydrator->hydrate($result, $conversation);

         return $conversation;
     }
     
     public function save(UserConversation $conversation)
     {
         $hydrator = new ClassMethods();
         
         $hydrator->addFilter(
             "Username",
             new MethodMatchFilter("getUsername"),
             FilterComposite::CONDITION_AND
         );
          
         $hydrator->addFilter(
             "photo",
             new MethodMatchFilter("getPhoto"),
             FilterComposite::CONDITION_AND
         );
          
         $hydrator->addFilter(
             "type",
             new MethodMatchFilter("getType"),
             FilterComposite::CONDITION_AND
         );
         
         $hydrator->addFilter(
             "lastuser",
             new MethodMatchFilter("getLastUser"),
             FilterComposite::CONDITION_AND
         );
         
         $hydrator->addFilter(
             "lastmessage",
             new MethodMatchFilter("getLastMessage"),
             FilterComposite::CONDITION_AND
         );
         
         $hydrator->addFilter(
             "lastmessagestatus",
             new MethodMatchFilter("getLastMessageStatus"),
             FilterComposite::CONDITION_AND
         );
         
         $hydratedData = $hydrator->extract($conversation);
        
         $data = array();
          
         foreach ($hydratedData as $key => $value)
         {
             $data['conversation_'.$key] = $value;
         }
         
         if ($conversation->getId()) {
             // update action
             $action = $this->sql->update();
             $action->set($data);
             $action->where(array('conversation_id' => $conversation->getId()));
         } else {
             // insert action
             $action = $this->sql->insert();
             unset($data['conversation_id']);
             $action->values($data);
         }
         $statement = $this->sql->prepareStatementForSqlObject($action);
         $result = $statement->execute();
         
         if (!$conversation->getId()) {
             $conversation->setId($result->getGeneratedValue());
         }
         
         return $result;
     }
     
     public function delete(UserConversation $conversation)
     {         
         $delete = $this->sql->delete();
         $delete->where(array('conversation_id' => $conversation->getId()));
         
         $statement = $this->sql->prepareStatementForSqlObject($delete);
         return $statement->execute();
     }
     
     public function findByUsers($user1,$user2)
     {
         $select = $this->sql->select();
         
         $select->join(array('p1'=>'users_conversations_participants'), 'p1.participant_conversation = conversation_id', array());
         $select->join(array('p2'=>'users_conversations_participants'), 'p2.participant_conversation = conversation_id', array());
         $select->where(array('p1.participant_id = ?'=>$user1));
         $select->where(array('p2.participant_id = ?'=>$user2));
         
         $statement = $this->sql->prepareStatementForSqlObject($select);
         $result = $statement->execute()->current();         
         
         if (!$result) {
             return null;
         }
         
         $hydrator = new ClassMethods();
         $hydrator->setNamingStrategy(new FirstUnderscoreNamingStrategy);
         
         $conversation = new UserConversation();
         $hydrator->hydrate($result, $conversation);

         return $conversation;
     }
     
     public function countMessage(UserConversation $conversation)
     {
         $select = $this->sql->select();
         $select->join('users_messages','message_conversation_id = conversation_id',array());
         $select->where(array('conversation_id = ?'=>$conversation->getId()));
         $select->columns(array('COUNT'=>new \Zend\Db\Sql\Expression('COUNT(message_id)')));
         
         $statement = $this->sql->prepareStatementForSqlObject($select);
         $result = $statement->execute();
         
         $row = $result->current();
         
         return (int) $row['COUNT'];
     }
     
     public function fetchAll($params = array())
     {
         $select = $this->sql->select();
         
         if(isset($params['owner']))
         {             
             $expression = new Expression('p2.participant_conversation = conversation_id AND p2.participant_id != '.$params['owner']);
             
             $select->join(array('p1'=>'users_conversations_participants'), 'p1.participant_conversation = conversation_id', array());
             $select->join(array('p2'=>'users_conversations_participants'), $expression, array('receiver'=>'participant_id'));
             $select->where(array('p1.participant_id' => $params['owner']));
             $select->group('conversation_id');
         }
         
         $subSql = new sql($this->dbAdapter);
         $subSql->setTable('users_messages');
         
         $subSelect = $subSql->select();
         $subSelect->columns(array('message_content','message_id','message_date','message_conversation_id','message_author'));
         $subSelect->order('message_id DESC');
         
         $subSelect->join('users_messages_status','message_id = status_message_id',array('status_value'));
         $subSelect->where(array('status_user_id = ?'=>$params['owner']));

         $select->join(array('um'=>'users_messages'),'message_conversation_id = conversation_id',array());
         $select->join(array('s'=>'users_messages_status'),'message_id = status_message_id',array());
         $select->where(array('s.status_value != ?'=>'deleted'));
         $select->where(array('s.status_user_id = ?'=>$params['owner']));
         $select->having('count(*) > 0');
         
         $select->join(array('m'=>$subSelect), 'm.message_conversation_id = conversation_id',array('conversation_last_message_status'=>'status_value','conversation_last_user'=>'message_author','conversation_last_message'=>'message_content', 'conversation_last_message_date'=>'message_date'));
         
         $select->join('users', 'user_id = p2.participant_id', array('conversation_username'=>'user_name', 'conversation_type'=>'user_type'));
         
         $expression = new Expression('photo_owner = user_id AND photo_type ="main" AND photo_status = "validated"');
         $select->join(array('p'=>'users_photos'),$expression,array('message_photo'=>'photo_filename'),'left');
                 
         $select->order('conversation_last_message_date DESC');
         
         $statement = $this->sql->prepareStatementForSqlObject($select);
                  
         $results = $statement->execute();
         
         

         $entityPrototype = new UserConversation();
         $hydrator = new ClassMethods();
         
         $hydrator->setNamingStrategy(new FirstUnderscoreNamingStrategy);
         
         $resultset = new HydratingResultSet($hydrator, $entityPrototype);
         $resultset->initialize($results);
         return $resultset;
     }
 }