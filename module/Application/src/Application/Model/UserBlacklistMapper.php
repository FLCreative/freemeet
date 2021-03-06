<?php
 namespace Application\Model;

 use Zend\Db\Adapter\Adapter;
 use Zend\Stdlib\Hydrator\ClassMethods;
 use Zend\Db\Sql\Sql;
 use Zend\Db\ResultSet\HydratingResultSet;
 use Zend\Stdlib\Hydrator\NamingStrategy\FirstUnderscoreNamingStrategy;
 
 use Application\Model\UserBlacklist;
 
 class UserBlacklistMapper
 {
     protected $tableName = 'users_profils_blacklisted';
     protected $dbAdapter;
     protected $sql;

     public function __construct(Adapter $dbAdapter)
     {
         $this->dbAdapter = $dbAdapter;
         $this->sql = new Sql($dbAdapter);
         $this->sql->setTable($this->tableName);
     }
     
     public function find($owner, $user)
     {
         $select = $this->sql->select();
         $select->where(array('blacklist_owner' => $owner, 'blacklist_user'=> $user));
    
         $statement = $this->sql->prepareStatementForSqlObject($select);
         $result = $statement->execute()->current();
         
         if (!$result) {
             return null;
         }
    
         $hydrator = new ClassMethods();
         $hydrator->setNamingStrategy(new FirstUnderscoreNamingStrategy);
         
         $blacklist = new UserBlacklist();
         $hydrator->hydrate($result, $blacklist);

         return $blacklist;
     }
     
     public function save(UserBlacklist $blacklist)
     {
         $hydrator = new ClassMethods();
          
         $hydrator->setNamingStrategy(new FirstUnderscoreNamingStrategy);
          
         $hydratedData = $hydrator->extract($blacklist);
          
         $data = array();
          
         foreach ($hydratedData as $key => $value)
         {
             $data['favorite_'.$key] = $value;
         }
          
         $action = $this->sql->insert();
         unset($data['favorite_id']);
         $action->values($data);
             
         $statement = $this->sql->prepareStatementForSqlObject($action);
         $result = $statement->execute();
          
         return $result;
     }
     
     public function delete(UserBlacklist $blacklist)
     {
         $delete = $this->sql->delete();
         $delete->where(array('blacklist_owner' => $blacklist->getOwner()));
          
         $statement = $this->sql->prepareStatementForSqlObject($delete);
         return $statement->execute();
     }
     
     public function fetchAll($params = array())
     {
         $select = $this->sql->select();
         
         if(isset($params['owner']))
         {
             $select->where(array('blacklist_owner = ?'=>$params['owner']));
         }            

         $statement = $this->sql->prepareStatementForSqlObject($select);
         $results = $statement->execute();

         $entityPrototype = new UserBlacklist();
         $hydrator = new ClassMethods();
         
         $hydrator->setNamingStrategy(new FirstUnderscoreNamingStrategy);
         
         $resultset = new HydratingResultSet($hydrator, $entityPrototype);
         $resultset->initialize($results);
         return $resultset;
     }
 }