<?php
 namespace Application\Model;
 // Add these import statements
 use Zend\InputFilter\InputFilter;
 use Zend\InputFilter\InputFilterAwareInterface;
 use Zend\InputFilter\InputFilterInterface;
 use Zend\Validator\Db\NoRecordExists;

 class User implements InputFilterAwareInterface
 {
    protected $id;
    protected $name;
    protected $password;
    protected $secretKey;
    protected $email;
    protected $birthday;
    protected $country;
    protected $city;
    protected $type;
    protected $search;
    protected $added;
    protected $description;
    protected $last_login;
    protected $last_action;
    protected $newsletter;
    protected $mail_flash;
    protected $mail_view;
    protected $mail_message;
    protected $mail_contact;
    protected $status;
    protected $isOnline;
    
    protected $photo;

    protected $city_name;
    protected $countryName;
    protected $departmentName;
    
    private $_dbAdapter;
    private $inputFilter;

    public function getStatus()
    {
        return $this->status;
    }

 public function setStatus($status)
    {
        $this->status = $status;
    }

 public function getSecretKey()
    {
        return $this->secretKey;
    }

 public function setSecretKey($secretKey)
    {
        $this->secretKey = $secretKey;
    }

 public function getId()
    {
        return $this->id;
    }

    public function setId($Value)
    {
        $this->id = $Value;
    }
    
	public function getName()
    {
        return $this->name;
    }

	public function getPassword()
    {
        return $this->password;
    }

	public function getEmail()
    {
        return $this->email;
    }

	public function getBirthday()
    {
        return $this->birthday;
    }

	public function getCountry()
    {
        return $this->country;
    }

	public function getCity()
    {
        return $this->city;
    }

	public function getType()
    {
        return $this->type;
    }

	public function getSearch()
    {
        return $this->search;
    }

	public function getAdded()
    {
        return $this->added;
    }

	public function getDescription()
    {
        return $this->description;
    }

	public function getLastLogin()
    {
        return $this->last_login;
    }

	public function getLast_action()
    {
        return $this->last_action;
    }

	public function getNewsletter()
    {
        return $this->newsletter;
    }

	public function getMailFlash()
    {
        return $this->mail_flash;
    }

	public function getMail_view()
    {
        return $this->mail_view;
    }

	public function getMail_message()
    {
        return $this->mail_message;
    }

	public function getMail_contact()
    {
        return $this->mail_contact;
    }

	public function setName($name)
    {
        $this->name = $name;
    }

	public function setPassword($password)
    {
        $this->password = $password;
    }

	public function setEmail($email)
    {
        $this->email = $email;
    }

	public function setBirthday($birthday)
    {
        $this->birthday = $birthday;
    }

	public function setCountry($country)
    {
        $this->country = $country;
    }

	public function setCity($city)
    {
        $this->city = $city;
    }

	public function setType($type)
    {
        $this->type = $type;
    }

	public function setSearch($search)
    {
        $this->search = $search;
    }

	public function setAdded($added)
    {
        $this->added = $added;
    }

	public function setDescription($description)
    {
        $this->description = $description;
    }

	public function setLastLogin($last_login)
    {
        $this->last_login = $last_login;
    }

	public function setLast_action($last_action)
    {
        $this->last_action = $last_action;
    }

	public function setNewsletter($newsletter)
    {
        $this->newsletter = $newsletter;
    }

	public function setMailFlash($mail_flash)
    {
        $this->mail_flash = $mail_flash;
    }

	public function setMail_view($mail_view)
    {
        $this->mail_view = $mail_view;
    }

	public function setMail_message($mail_message)
    {
        $this->mail_message = $mail_message;
    }

	public function setMail_contact($mail_contact)
    {
        $this->mail_contact = $mail_contact;
    }
	
	public function getPhoto($size = false)
    {
        if(!strlen($this->photo))
        {
            if($this->type == 1)
            {
                $file =  '/images/default_man.png';
            }
            else
            {
                $file = '/images/default_woman.png';
            }
            
            if($size)
            {
                $path_parts = pathinfo($file);
                
                $ext = $path_parts['extension'];
                $filename = $path_parts['filename'];
                
                return '/images/'.$filename.'_'.$size.'.'.$ext;
            }
            
            return $file;
        }
        
        if($size)
        {
            $path_parts = pathinfo($this->photo);
            
            $ext = $path_parts['extension'];
            $filename = $path_parts['filename'];
            
            return '/photos/'.$filename.'_'.$size.'.'.$ext;
        }
        
        return '/photos/'.$this->photo;
    }

	public function setPhoto($photo)
    {
        $this->photo = $photo;
    }
    
    public function getCityName()
    {
        return $this->city_name;
    }
    
    public function setCityName($city_name)
    {
        $this->city_name = $city_name;
    }
    
    public function getCountryName()
    {
        return $this->countryName;
    }

	public function getDepartmentName()
    {
        return $this->departmentName;
    }

	public function setCountryName($countryName)
    {
        $this->countryName = $countryName;
    }

	public function setDepartmentName($departmentName)
    {
        $this->departmentName = $departmentName;
    }

	public function setDbAdapter($dbAdapter) {
        $this->_dbAdapter = $dbAdapter;
    }
    
    public function getDbAdapter() {
        return $this->_dbAdapter;
    }
    
    // Add content to these methods:
    public function setInputFilter(InputFilterInterface $inputFilter)
    {
        throw new \Exception("Not used");
    }
    
    public function getInputFilter()
    {
        if (!$this->inputFilter) {
            
            $dbAdapter = $this->_dbAdapter;
            
            $validator = new NoRecordExists(
                array(
                    'table'   => 'users',
                    'field'   => 'user_email',
                    'adapter' => $dbAdapter
                )
            );
            
            $inputFilter = new InputFilter();
    
            $inputFilter->add(array(
                'name'     => 'name',
                'required' => true,
                'filters'  => array(
                    array('name' => 'StripTags'),
                    array('name' => 'StringTrim'),
                ),
            ));
    
            $inputFilter->add(array(
                'name'     => 'email',
                'required' => true,
                'filters'  => array(
                    array('name' => 'StripTags'),
                    array('name' => 'StringTrim'),
                ),
                'validators' => array(
                    array(
                        'name'    => 'StringLength',
                        'options' => array(
                            'encoding' => 'UTF-8',
                            'min'      => 1,
                            'max'      => 100,
                        ),
                    ),
                    $validator
                ),
            ));
    
            $inputFilter->add(array(
                'name'     => 'password',
                'required' => true,
                'filters'  => array(
                    array('name' => 'StripTags'),
                    array('name' => 'StringTrim'),
                ),
                'validators' => array(
                    array(
                        'name'    => 'StringLength',
                        'options' => array(
                            'encoding' => 'UTF-8',
                            'min'      => 6                             ,
                            'max'      => 100,
                        ),
                    ),
                ),
            ));
    
            $this->inputFilter = $inputFilter;
        }
    
        return $this->inputFilter;
    }
 public function getIsOnline()
    {
        return $this->isOnline;
    }

 public function setIsOnline($isOnline)
    {
        $this->isOnline = $isOnline;
    }




 }