<?php
 namespace Application\Model;

 use Zend\Db\Adapter\Adapter;
 use Zend\Stdlib\Hydrator\ClassMethods;
 use Zend\Db\Sql\Sql;
 use Zend\Db\ResultSet\HydratingResultSet;
 use Zend\Stdlib\Hydrator\NamingStrategy\FirstUnderscoreNamingStrategy;
 
 use Application\Model\UserBlacklist;
 
 class ProfilQuestionCategoryMapper
 {
     protected $tableName = 'profil_questions_categories';
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
     
     public function save(ProfilQuestionCategory $category)
     {
         $hydrator = new ClassMethods();
          
         $hydrator->setNamingStrategy(new FirstUnderscoreNamingStrategy);
          
         $hydratedData = $hydrator->extract($category);
          
         $data = array();
          
         foreach ($hydratedData as $key => $value)
         {
             $data['category_'.$key] = $value;
         }
         
          
         if ($category->getId()) {
             // update action
             $action = $this->sql->update();
             $action->set($data);
             $action->where(array('category_id' => $category->getId()));
         } else {
             // insert action
             $action = $this->sql->insert();
             unset($data['category_id']);
             $action->values($data);
         }
         
         $statement = $this->sql->prepareStatementForSqlObject($action);
         $result = $statement->execute();
         
         if (!$category->getId()) {
             $category->setId($result->getGeneratedValue());
         }
          
         return $result;
     }
     
     public function delete(ProfilQuestionCategory $category)
     {
         $delete = $this->sql->delete();
         $delete->where(array('category_id' => $category->getId()));
          
         $statement = $this->sql->prepareStatementForSqlObject($delete);
         return $statement->execute();
     }
     
     public function fetchAll($params = array())
     {
         $select = $this->sql->select();        
            
         $statement = $this->sql->prepareStatementForSqlObject($select);
         $results = $statement->execute();

         $entityPrototype = new ProfilQuestionCategory();
         $hydrator = new ClassMethods();
         
         $hydrator->setNamingStrategy(new FirstUnderscoreNamingStrategy);
         
         $resultset = new HydratingResultSet($hydrator, $entityPrototype);
         $resultset->initialize($results);
         return $resultset;
     }
 }