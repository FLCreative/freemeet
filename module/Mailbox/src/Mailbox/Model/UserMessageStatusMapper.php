<?php
 namespace Mailbox\Model;

 use Zend\Db\Adapter\Adapter;
 use Zend\Stdlib\Hydrator\ClassMethods;
 use Zend\Db\Sql\Sql;
 use Zend\Db\ResultSet\HydratingResultSet;
 use Zend\Stdlib\Hydrator\NamingStrategy\FirstUnderscoreNamingStrategy;
 use Mailbox\Model\UserMessageStatus;
 
 class UserMessageStatusMapper
 {
     protected $tableName = 'users_messages_status';
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
         $select->where(array('status_message_id' => $id));
    
         $statement = $this->sql->prepareStatementForSqlObject($select);
         $result = $statement->execute()->current();
         
         if (!$result) {
             return null;
         }
    
         $hydrator = new ClassMethods();
         $hydrator->setNamingStrategy(new FirstUnderscoreNamingStrategy);
         
         $status = new UserMessageStatus();
         $hydrator->hydrate($result, $status);

         return $status;
     }
     
     public function save(UserMessageStatus $status)
     {
         $hydrator = new ClassMethods();
         $hydratedData = $hydrator->extract($status);
         
         $data = array();
         
         foreach ($hydratedData as $key => $value)
         {
             $data['status_'.$key] = $value;
         }
         
          // insert action
         $action = $this->sql->insert();
         $action->values($data);
             
         $statement = $this->sql->prepareStatementForSqlObject($action);
         $result = $statement->execute();
         
         return $result;
     }
     
     public function delete(UserMessage $message)
     {         
         $delete = $this->sql->delete();
         $delete->where(array('status_message_id' => $message->getId()));
         
         $statement = $this->sql->prepareStatementForSqlObject($delete);
         return $statement->execute();
     }
     
     public function countUnreadMessages($owner)
     {
         $select = $this->sql->select();
     
         $select->where(array('status_user_id' => $owner, 'status_value'=>'unread'));
         
         $statement = $this->sql->prepareStatementForSqlObject($select);
         $result    = $statement->execute();
         
         return $result->count();
     }
     
     public function updateStatus($message,$user,$value)
     {
         $update = $this->sql->update();

         $update->where(array('status_user_id' => $user, 'status_message_id'=>$message));
         
         $update->set(array('status_value'=>$value));
          
         $statement = $this->sql->prepareStatementForSqlObject($update);
         $result    = $statement->execute();
         
         return $result;
     }
     
     public function fetchAll($params = array())
     {
         $select = $this->sql->select();
         
         if(isset($params['folder']))
         {
             $select->where(array('message_folder = ?'=>$params['folder']));
         }
         
         if(isset($params['owner']))
         {
             $select->where(array('message_owner = ?'=>$params['owner']));
         }
         
         $statement = $this->sql->prepareStatementForSqlObject($select);
         $results = $statement->execute();

         $entityPrototype = new UserMessageStatus();
         $hydrator = new ClassMethods();
         
         $hydrator->setNamingStrategy(new FirstUnderscoreNamingStrategy);
         
         $resultset = new HydratingResultSet($hydrator, $entityPrototype);
         $resultset->initialize($results);
         return $resultset;
     }
 }