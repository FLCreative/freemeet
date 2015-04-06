<?php
 namespace Application\Model;

 use Zend\Db\Adapter\Adapter;
 use Zend\Stdlib\Hydrator\ClassMethods;
 use Zend\Db\Sql\Sql;
 use Zend\Db\ResultSet\HydratingResultSet;
 use Zend\Stdlib\Hydrator\NamingStrategy\FirstUnderscoreNamingStrategy;
 
 use Application\Model\UserFavorite;
 
 class UserFavoriteMapper
 {
     protected $tableName = 'users_favorites';
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
         $select->where(array('favorite_owner' => $owner, 'favorite_user'=> $user));
    
         $statement = $this->sql->prepareStatementForSqlObject($select);
         $result = $statement->execute()->current();
         
         if (!$result) {
             return null;
         }
    
         $hydrator = new ClassMethods();
         $hydrator->setNamingStrategy(new FirstUnderscoreNamingStrategy);
         
         $favorite = new UserFavorite();
         $hydrator->hydrate($result, $favorite);

         return $favorite;
     }
     
     public function save(UserFavorite $favorite)
     {
         $hydrator = new ClassMethods();
          
         $hydrator->setNamingStrategy(new FirstUnderscoreNamingStrategy);
          
         $hydratedData = $hydrator->extract($favorite);
          
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
     
     public function delete(UserFavorite $favorite)
     {
         $delete = $this->sql->delete();
         $delete->where(array('favorite_owner' => $favorite->getOwner(), 'favorite_user'=> $favorite->getUser()));
          
         $statement = $this->sql->prepareStatementForSqlObject($delete);
         return $statement->execute();
     }
     
     public function fetchAll($params = array())
     {
         $select = $this->sql->select();
         
         if(isset($params['owner']))
         {
             $select->where(array('favorite_owner = ?'=>$params['owner']));
         }            

         $statement = $this->sql->prepareStatementForSqlObject($select);
         $results = $statement->execute();

         $entityPrototype = new UserFavorite();
         $hydrator = new ClassMethods();
         
         $hydrator->setNamingStrategy(new FirstUnderscoreNamingStrategy);
         
         $resultset = new HydratingResultSet($hydrator, $entityPrototype);
         $resultset->initialize($results);
         return $resultset;
     }
 }