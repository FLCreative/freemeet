<?php
 namespace Mailbox\Model;

 use Zend\Db\Adapter\Adapter;
 use Zend\Stdlib\Hydrator\ClassMethods;
 use Zend\Db\Sql\Sql;
 use Zend\Db\ResultSet\HydratingResultSet;
 use Zend\Stdlib\Hydrator\NamingStrategy\FirstUnderscoreNamingStrategy;
 use Zend\Stdlib\Hydrator\Filter\MethodMatchFilter;
 use Zend\Stdlib\Hydrator\Filter\FilterComposite;
 use Mailbox\Model\UserMessage;
 use Zend\Db\Sql\Expression;
	 
 class UserMessageMapper
 {
     protected $tableName = 'users_messages';
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
         $select->where(array('message_id' => $id));
    
         $statement = $this->sql->prepareStatementForSqlObject($select);
         $result = $statement->execute()->current();
         
         if (!$result) {
             return null;
         }
    
         $hydrator = new ClassMethods();
         $hydrator->setNamingStrategy(new FirstUnderscoreNamingStrategy);
         
         $message = new UserMessage();
         $hydrator->hydrate($result, $message);

         return $message;
     }
     
     public function save(UserMessage $message)
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
         
         $hydratedData = $hydrator->extract($message);
         
         $data = array();
          
         foreach ($hydratedData as $key => $value)
         {
             $data['message_'.$key] = $value;
         }
         
         if ($message->getId()) {
             // update action
             $action = $this->sql->update();
             $action->set($data);
             $action->where(array('message_id' => $message->getId()));
         } else {
             // insert action
             $action = $this->sql->insert();
             unset($data['message_id']);
             $action->values($data);
         }
         $statement = $this->sql->prepareStatementForSqlObject($action);
         $result = $statement->execute();
         
         if (!$message->getId()) {
             $message->setId($result->getGeneratedValue());
         }
         
         return $result;
     }
     
     public function delete(UserMessage $message)
     {         
         $delete = $this->sql->delete();
         $delete->where(array('message_id' => $message->getId()));
         
         $statement = $this->sql->prepareStatementForSqlObject($delete);
         return $statement->execute();
     }
     
     public function fetchToDelete(UserConversation $conversation)
     {
         $select = $this->sql->select();
         $select->join('users_messages_status','status_message_id = message_id',array());
         $select->where(array('message_conversation_id'=>$conversation->getId()));
         $select->where(array('status_value'=>'deleted'));
         $select->group('message_id');
         $select->having('count(message_id) = 2');
         
         $statement = $this->sql->prepareStatementForSqlObject($select);
         $results = $statement->execute();
         
         $entityPrototype = new UserMessage();
         $hydrator = new ClassMethods();
          
         $hydrator->setNamingStrategy(new FirstUnderscoreNamingStrategy);
          
         $resultset = new HydratingResultSet($hydrator, $entityPrototype);
         $resultset->initialize($results);
         
         return $resultset;
     }
              
     public function fetchAll($params = array())
     {
         $select = $this->sql->select();
         
         if(isset($params['conversation']))
         {             
             $select->where(array('message_conversation_id = ?' => $params['conversation']));
         }
         
         if(isset($params['status']))
         {
             $select->where(array('status_value = ?' => $params['status']));
         }
         
         if(isset($params['owner']))
         {
             $select->where(array('status_user_id = ?' => $params['owner']));
         }

         if(isset($params['order']))
         {
         	$select->order('message_id '.$params['order']);
         }
         
         if(isset($params['limit']))
         {
         	$select->limit($params['limit']);
         }
         
         $select->where(array('status_value != ?' => 'deleted'));
         
         $select->join('users', 'user_id = message_author', array('message_username'=>'user_name', 'message_type'=>'user_type'));
         $select->join('users_messages_status', 'status_message_id = message_id', array());
         
         $expression = new Expression('photo_owner = user_id AND photo_type ="main" AND photo_status = "validated"');
         $select->join(array('p'=>'users_photos'),$expression,array('message_photo'=>'photo_filename'),'left');

         $statement = $this->sql->prepareStatementForSqlObject($select);
         $results = $statement->execute();

         $entityPrototype = new UserMessage();
         $hydrator = new ClassMethods();
         
         $hydrator->setNamingStrategy(new FirstUnderscoreNamingStrategy);
         
         $resultset = new HydratingResultSet($hydrator, $entityPrototype);
         $resultset->initialize($results);
         return $resultset;
     }
 }