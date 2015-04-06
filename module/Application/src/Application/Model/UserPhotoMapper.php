<?php
 namespace Application\Model;

 use Zend\Db\Adapter\Adapter;
 use Application\Model\UserPhoto;
 use Zend\Stdlib\Hydrator\ClassMethods;
 use Zend\Db\Sql\Sql;
 use Zend\Db\ResultSet\HydratingResultSet;
 use Zend\Stdlib\Hydrator\NamingStrategy\FirstUnderscoreNamingStrategy;
 
 class UserPhotoMapper
 {
     protected $tableName = 'users_photos';
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
         $select->where(array('photo_id' => $id));
    
         $statement = $this->sql->prepareStatementForSqlObject($select);
         $result = $statement->execute()->current();
         
         if (!$result) {
             return null;
         }
    
         $hydrator = new ClassMethods();
         $hydrator->setNamingStrategy(new FirstUnderscoreNamingStrategy);
         
         $photo = new UserPhoto();
         $hydrator->hydrate($result, $photo);

         return $photo;
     }
     
     public function save(UserPhoto $photo)
     {
         $hydrator = new ClassMethods();
          
         $hydrator->setNamingStrategy(new FirstUnderscoreNamingStrategy);
          
         $hydratedData = $hydrator->extract($photo);
          
         $data = array();
          
         foreach ($hydratedData as $key => $value)
         {
             $data['photo_'.$key] = $value;
         }
          
         if ($photo->getId()) {
             // update action
             $action = $this->sql->update();
             $action->set($data);
             $action->where(array('photo_id' => $photo->getId()));
         } else {
             // insert action
             $action = $this->sql->insert();
             unset($data['photo_id']);
             $action->values($data);
         }
         $statement = $this->sql->prepareStatementForSqlObject($action);
         $result = $statement->execute();
          
         if (!$photo->getId()) {
             $photo->setId($result->getGeneratedValue());
         }
          
         return $result;
     }
     
     public function delete(UserPhoto $photo)
     {
         $delete = $this->sql->delete();
         $delete->where(array('photo_id' => $photo->getId()));
          
         $statement = $this->sql->prepareStatementForSqlObject($delete);
         return $statement->execute();
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

         $entityPrototype = new UserPhoto();
         $hydrator = new ClassMethods();
         
         $hydrator->setNamingStrategy(new FirstUnderscoreNamingStrategy);
         
         $resultset = new HydratingResultSet($hydrator, $entityPrototype);
         $resultset->initialize($results);
         return $resultset;
     }
 }