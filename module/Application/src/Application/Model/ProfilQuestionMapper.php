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
     protected $tableName = 'profil_questions';
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
         $select->where(array('question_id' => $id));
    
         $statement = $this->sql->prepareStatementForSqlObject($select);
         $result = $statement->execute()->current();
         
         if (!$result) {
             return null;
         }
    
         $hydrator = new ClassMethods();
         $hydrator->setNamingStrategy(new FirstUnderscoreNamingStrategy);
         
         $question = new ProfilQuestion();
         $hydrator->hydrate($result, $question);

         return $question;
     }
     
     public function save(ProfilQuestion $question)
     {
         $hydrator = new ClassMethods();
          
         $hydrator->setNamingStrategy(new FirstUnderscoreNamingStrategy);
          
         $hydratedData = $hydrator->extract($question);
          
         $data = array();
          
         foreach ($hydratedData as $key => $value)
         {
             $data['question_'.$key] = $value;
         }
          
         $action = $this->sql->insert();
         unset($data['question_id']);
         $action->values($data);
             
         $statement = $this->sql->prepareStatementForSqlObject($action);
         $result = $statement->execute();
          
         return $result;
     }
     
     public function delete(ProfilQuestion $question)
     {
         $delete = $this->sql->delete();
         $delete->where(array('question_id' => $question->getId()));
          
         $statement = $this->sql->prepareStatementForSqlObject($delete);
         return $statement->execute();
     }
     
     public function fetchAll($params = array())
     {
         $select = $this->sql->select();
         
         if(isset($params['category']))
         {
             $select->where(array('question_category = ?'=>$params['category']));
         }            

         $statement = $this->sql->prepareStatementForSqlObject($select);
         $results = $statement->execute();

         $entityPrototype = new ProfilQuestion();
         $hydrator = new ClassMethods();
         
         $hydrator->setNamingStrategy(new FirstUnderscoreNamingStrategy);
         
         $resultset = new HydratingResultSet($hydrator, $entityPrototype);
         $resultset->initialize($results);
         return $resultset;
     }
 }