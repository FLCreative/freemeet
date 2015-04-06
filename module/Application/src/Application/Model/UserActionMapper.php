<?php
 namespace Application\Model;

 use Zend\Db\Adapter\Adapter; 
 use Zend\Db\Sql\Sql;
 use Zend\Db\ResultSet\HydratingResultSet;
 use Zend\Stdlib\Hydrator\ClassMethods;
 use Zend\Stdlib\Hydrator\NamingStrategy\FirstUnderscoreNamingStrategy;
 use Zend\Stdlib\Hydrator\Filter\MethodMatchFilter;
 use Zend\Stdlib\Hydrator\Filter\FilterComposite;
 
 class UserActionMapper
 {
     protected $tableName = 'users_actions';
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
         $select->where(array('action_id' => $id));
    
         $statement = $this->sql->prepareStatementForSqlObject($select);
         $result = $statement->execute()->current();
         
         if (!$result) {
             return null;
         }
    
         $hydrator = new ClassMethods();
         $action = new UserAction();
         $hydrator->hydrate($result, $action);

         return $action;
     }
     
     public function save(UserAction $userAction)
     {
         $hydrator = new ClassMethods();
         
         $hydrator->setNamingStrategy(new FirstUnderscoreNamingStrategy);
         
         $hydrator->addFilter(
             "username",
             new MethodMatchFilter("getUsername"),
             FilterComposite::CONDITION_AND
         );
         
         $hydratedData = $hydrator->extract($userAction);
         
         $data = array();
         
         foreach ($hydratedData as $key => $value)
         {
             $data['action_'.$key] = $value;
         }
         
         if ($userAction->getId()) {
             // update action
             $action = $this->sql->update();
             $action->set($data);
             $action->where(array('action_id' => $userAction->getId()));
         } else {
             // insert action
             $action = $this->sql->insert();
             unset($data['action_id']);
             $action->values($data);
         }
         $statement = $this->sql->prepareStatementForSqlObject($action);
         $result = $statement->execute();
         
         if (!$userAction->getId()) {
             $userAction->setId($result->getGeneratedValue());
         }
         
         return $result;
     }
     
     public function delete(UserAction $action)
     {
         $delete = $this->sql->delete();
         $delete->where(array('action_id' => $action->getId()));
         
         $statement = $this->sql->prepareStatementForSqlObject($delete);
         return $statement->execute();
     }
     
     public function findPerformed($author,$user,$type)
     {
         $select = $this->sql->select();
         $select->where(array('action_user' => $user));
         $select->where(array('action_author' => $author));
         $select->where(array('action_type' => $type));
         
         $statement = $this->sql->prepareStatementForSqlObject($select);
         $result = $statement->execute()->current();
          
         if (!$result) {
             return null;
         }
         
         $hydrator = new ClassMethods();
         $hydrator->setNamingStrategy(new FirstUnderscoreNamingStrategy);
         
         $action = new UserAction();
         $hydrator->hydrate($result, $action);
         
         return $action;
     }
     
     public function fetchAll($params = array())
     {
         $select = $this->sql->select();        
         
         if(isset($params['author']))
         {
             $select->where(array('action_author'=>$params['author']));
         }
         
         if(isset($params['limit']))
         {
             $select->limit($params['limit']);
         }
         
         $select->join('users', 'action_user = user_id', array('action_username'=>'user_name'));
         
         $select->order('action_id DESC');
         
         $statement = $this->sql->prepareStatementForSqlObject($select);
         $results = $statement->execute();

         $entityPrototype = new UserAction();
         $hydrator = new ClassMethods();
         
         $hydrator->setNamingStrategy(new FirstUnderscoreNamingStrategy);
         
         $resultset = new HydratingResultSet($hydrator, $entityPrototype);
         $resultset->initialize($results);
         return $resultset;
     }
 }