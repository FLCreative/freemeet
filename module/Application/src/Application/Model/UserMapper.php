<?php
 namespace Application\Model;

 use Zend\Db\Adapter\Adapter;
 use Application\Model\User;
 use Zend\Stdlib\Hydrator\ClassMethods;
 use Zend\Db\Sql\Sql;
 use Zend\Paginator\Adapter\DbSelect;
 use Zend\Db\ResultSet\HydratingResultSet;
 use Zend\Stdlib\Hydrator\NamingStrategy\FirstUnderscoreNamingStrategy;
 use Zend\Db\Sql\Expression;
 use Zend\Stdlib\Hydrator\Filter\MethodMatchFilter;
 use Zend\Stdlib\Hydrator\Filter\FilterComposite;
 use Zend\Paginator\Paginator;
 
 class UserMapper
 {
     protected $tableName = 'users';
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
         $select->where(array(
             'user_id' => $id,
             'user_status' => 'active'
             
         ));
         
         $expression = new Expression('photo_owner = user_id AND photo_type ="main" AND photo_status = "validated"');
         $select->join(array('p'=>'users_photos'),$expression,array('user_photo'=>'photo_filename'),'left');
         $select->join('cities','city_id = user_city',array('user_city_name'=>'city_name','user_department_name'=>'city_admin_name2'));
         $select->join('countries','country_code = city_country_code',array('user_country_name'=>'country_name'));
         
         $statement = $this->sql->prepareStatementForSqlObject($select);         
         
         $result = $statement->execute()->current();
         
         if (!$result) {
             return null;
         }
    
         $hydrator = new ClassMethods();
         $hydrator->setNamingStrategy(new FirstUnderscoreNamingStrategy);
         
         $user = new User();
         $hydrator->hydrate($result, $user);

         return $user;
     }
     
     public function save(User $user)
     {
         $hydrator = new ClassMethods();
         
         $hydrator->addFilter(
             "dbadapter",
             new MethodMatchFilter("getDbAdapter"),
             FilterComposite::CONDITION_AND
         );
         
         $hydrator->addFilter(
             "inputfilter",
             new MethodMatchFilter("getInputFilter"),
             FilterComposite::CONDITION_AND
         );
         
         $hydrator->addFilter(
             "photo",
             new MethodMatchFilter("getPhoto"),
             FilterComposite::CONDITION_AND
         );
         
         $hydrator->addFilter(
             "getCityName",
             new MethodMatchFilter("getCityName"),
             FilterComposite::CONDITION_AND
         );
         
         $hydrator->addFilter(
             "getCountryName",
             new MethodMatchFilter("getCountryName"),
             FilterComposite::CONDITION_AND
         );
         
         $hydrator->addFilter(
             "Department",
             new MethodMatchFilter("getDepartmentName"),
             FilterComposite::CONDITION_AND
         );
         
         $hydrator->setNamingStrategy(new FirstUnderscoreNamingStrategy);
         
         $hydratedData = $hydrator->extract($user,'user');
         
         $data = array();
         
         foreach ($hydratedData as $key => $value)
         {
             $data['user_'.$key] = $value;
         }
         
         if ($user->getId()) {
             // update action
             $action = $this->sql->update();
             $action->set($data);
             $action->where(array('user_id' => $user->getId()));
         } else {
             // insert action
             $action = $this->sql->insert();
             unset($data['user_id']);
             $action->values($data);
         }
         $statement = $this->sql->prepareStatementForSqlObject($action);
         $result = $statement->execute();
         
         if (!$user->getId()) {
             $user->setId($result->getGeneratedValue());
         }
         
         return $result;
     }
     
     public function delete(User $user)
     {         
         $delete = $this->sql->delete();
         $delete->where(array('id' => $user->getId()));
         
         $statement = $this->sql->prepareStatementForSqlObject($delete);
         return $statement->execute();
     }
     
     public function fetchOnline($id)
     {
         $select = $this->sql->select();
         
         $select->where(array('user_is_online'=>'yes'))
                ->where(array('user_id != ?'=>$id));
     
         $statement = $this->sql->prepareStatementForSqlObject($select);
         $results = $statement->execute();
     
         $entityPrototype = new User();
         $hydrator = new ClassMethods();
          
         $hydrator->setNamingStrategy(new FirstUnderscoreNamingStrategy);
          
         $resultset = new HydratingResultSet($hydrator, $entityPrototype);
         $resultset->initialize($results);
         return $resultset;
     }
     
     public function fetchVisitors($id, $paginated = false)
     {               
         $select = $this->sql->select();
         
         $expression = new Expression('photo_owner = user_id AND photo_type ="main" AND photo_status = "validated"');
         
         $select->join(array('a'=>'users_actions'),'action_author = user_id',array())
                ->join(array('p'=>'users_photos'),$expression,array('user_photo'=>'photo_filename'),'left')
                ->where(array('action_user'=>$id))
                ->where(array('user_status = ?'=>'active'))
                ->order('action_id DESC');
              
         $statement = $this->sql->prepareStatementForSqlObject($select);         
                 
         $results = $statement->execute();
         
         
         $entityPrototype = new User();
         $hydrator = new ClassMethods();
          
         $hydrator->setNamingStrategy(new FirstUnderscoreNamingStrategy);
          
         $resultset = new HydratingResultSet($hydrator, $entityPrototype);
         
         if ($paginated) {
         
             // create a new pagination adapter object
             $paginatorAdapter = new DbSelect(
                 // our configured select object
                 $select,
                 // the adapter to run it against
                 $this->dbAdapter,
                 // the result set to hydrate
                 $resultset
             );
             $paginator = new Paginator($paginatorAdapter);
             return $paginator;
         }
         
         $resultset->initialize($results);

         return $resultset;
     }
     
     public function fetchFlashs($id, $paginated = false)
     {
         $select = $this->sql->select();
          
         $expression = new Expression('photo_owner = user_id AND photo_type ="main" AND photo_status = "validated"');
          
         $select->join(array('a'=>'users_actions'),'action_author = user_id',array())
         ->join(array('p'=>'users_photos'),$expression,array('user_photo'=>'photo_filename'),'left')
         ->where(array('action_user'=>$id))
         ->where(array('action_type'=>'flash'))
         ->where(array('user_status = ?'=>'active'))
         ->order('action_id DESC');
     
         $statement = $this->sql->prepareStatementForSqlObject($select);
          
         $results = $statement->execute();
          
          
         $entityPrototype = new User();
         $hydrator = new ClassMethods();
     
         $hydrator->setNamingStrategy(new FirstUnderscoreNamingStrategy);
     
         $resultset = new HydratingResultSet($hydrator, $entityPrototype);
          
         if ($paginated) {
              
             // create a new pagination adapter object
             $paginatorAdapter = new DbSelect(
                 // our configured select object
                 $select,
                 // the adapter to run it against
                 $this->dbAdapter,
                 // the result set to hydrate
                 $resultset
             );
             $paginator = new Paginator($paginatorAdapter);
             return $paginator;
         }
          
         $resultset->initialize($results);
     
         return $resultset;
     }
     
     public function fetchFavorites($id, $paginated = false)
     {
         $select = $this->sql->select();
     
         $expression = new Expression('photo_owner = user_id AND photo_type ="main" AND photo_status = "validated"');
     
         $select->join(array('a'=>'users_favorites'),'favorite_user = user_id',array())
         ->join(array('p'=>'users_photos'),$expression,array('user_photo'=>'photo_filename'),'left')
         ->where(array('favorite_owner = ?'=>$id))
         ->where(array('user_status = ?'=>'active'))
         ->order('favorite_added DESC');
          
         $statement = $this->sql->prepareStatementForSqlObject($select);
     
         $results = $statement->execute();
     
     
         $entityPrototype = new User();
         $hydrator = new ClassMethods();
          
         $hydrator->setNamingStrategy(new FirstUnderscoreNamingStrategy);
          
         $resultset = new HydratingResultSet($hydrator, $entityPrototype);
     
         if ($paginated) {
     
             // create a new pagination adapter object
             $paginatorAdapter = new DbSelect(
                 // our configured select object
                 $select,
                 // the adapter to run it against
                 $this->dbAdapter,
                 // the result set to hydrate
                 $resultset
             );
             $paginator = new Paginator($paginatorAdapter);
             return $paginator;
         }
     
         $resultset->initialize($results);
          
         return $resultset;
     }
     
     public function fetchAll($params = array())
     {
         $select = $this->sql->select();
         
         if(isset($params['where']))
         {
         	$select->where($params['where']);
         }
         
         if(isset($params['status']))
         {
             $select->where(array('user_status'=>$params['status']));
         }
         
         if(isset($params['order']))
         {
             $select->order($params['order']);
         }
          
         if(isset($params['limit']))
         {
             $select->limit($params['limit']);
         }
         
         if(isset($params['exclude']))
         {
             $select->where(array('user_id != ?'=>$params['exclude']));
         }

         $statement = $this->sql->prepareStatementForSqlObject($select);
         $results = $statement->execute();

         $entityPrototype = new User();
         $hydrator = new ClassMethods();
         
         $hydrator->setNamingStrategy(new FirstUnderscoreNamingStrategy);
         
         $resultset = new HydratingResultSet($hydrator, $entityPrototype);
         $resultset->initialize($results);
         return $resultset;
     }
 }