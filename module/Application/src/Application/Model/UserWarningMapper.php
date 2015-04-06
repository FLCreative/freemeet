<?php
 namespace Application\Model;

 use Zend\Db\Adapter\Adapter;
 use Application\Model\UserWarning;
 use Zend\Stdlib\Hydrator\ClassMethods;
 use Zend\Db\Sql\Sql;
 use Zend\Db\ResultSet\HydratingResultSet;
 use Zend\Stdlib\Hydrator\NamingStrategy\FirstUnderscoreNamingStrategy;
 
 class UserWarningMapper
 {
     protected $tableName = 'users_warnings';
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
         $select->where(array('warning_id' => $id));
    
         $statement = $this->sql->prepareStatementForSqlObject($select);
         $result = $statement->execute()->current();
         
         if (!$result) {
             return null;
         }
    
         $hydrator = new ClassMethods();
         $hydrator->setNamingStrategy(new FirstUnderscoreNamingStrategy);
         
         $warning = new UserWarning();
         $hydrator->hydrate($result, $warning);

         return $warning;
     }
     
     public function save(UserWarning $warning)
     {
         $hydrator = new ClassMethods();
          
         $hydrator->setNamingStrategy(new FirstUnderscoreNamingStrategy);
          
         $hydratedData = $hydrator->extract($warning);
          
         $data = array();
          
         foreach ($hydratedData as $key => $value)
         {
             $data['warning_'.$key] = $value;
         }
          
         if ($warning->getId()) {
             // update action
             $action = $this->sql->update();
             $action->set($data);
             $action->where(array('warning_id' => $warning->getId()));
         } else {
             // insert action
             $action = $this->sql->insert();
             unset($data['warning_id']);
             $action->values($data);
         }
         $statement = $this->sql->prepareStatementForSqlObject($action);
         $result = $statement->execute();
          
         if (!$warning->getId()) {
             $warning->setId($result->getGeneratedValue());
         }
          
         return $result;
     }
     
     public function delete(UserWarning $warning)
     {
         $delete = $this->sql->delete();
         $delete->where(array('warning_id' => $warning->getId()));
          
         $statement = $this->sql->prepareStatementForSqlObject($delete);
         return $statement->execute();
     }
     
     public function fetchAll($params = array())
     {
         $select = $this->sql->select();
                     
         $statement = $this->sql->prepareStatementForSqlObject($select);
         $results = $statement->execute();

         $entityPrototype = new UserWarning();
         $hydrator = new ClassMethods();
         
         $hydrator->setNamingStrategy(new FirstUnderscoreNamingStrategy);
         
         $resultset = new HydratingResultSet($hydrator, $entityPrototype);
         $resultset->initialize($results);
         return $resultset;
     }
 }