<?php 

    class DbOperations{

        private $con; 

        //Creates the connection from the DbConnect file
        function __construct(){
            require_once dirname(__FILE__) . '/DbConnect.php';
            $db = new DbConnect; 
            $this->con = $db->connect(); 
        }

        //Creates a new user
        public function createUser($email, $password, $firstName, $lastName, $phone){
            if(!$this->isEmailExist($email)){
                 $stmt = $this->con->prepare("INSERT INTO users (email, password, first name, last name, phone) VALUES (?, ?, ?, ?, ?)");
                 $stmt->bind_param("sssss", $email, $password, $firstName, $lastName, $phone);
                 if($stmt->execute()){
                     return USER_CREATED; 
                 }else{
                     return USER_FAILURE;
                 }
            }
            return USER_EXISTS; 
         }


        private function isEmailExist($email){
            $stmt = $this->con->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute(); 
            $stmt->store_result(); 
            return $stmt->num_rows > 0;  
        }

    }