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
                $stmt = $this->con->prepare("INSERT INTO users (email, password, firstName, lastName, phone) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("sssss", $email, $password, $firstName, $lastName, $phone);
                if($stmt->execute()){
                    return USER_CREATED; 
                }else{
                    return USER_FAILURE;
                }
            }
            return USER_EXISTS; 
        }


        //Login existing user
        public function userLogin($email, $password){
            if($this->isEmailExist($email)){
                $hashed_password = $this->getUsersPasswordByEmail($email); 
                if(password_verify($password, $hashed_password)){
                    return USER_AUTHENTICATED;
                }else{
                    return USER_PASSWORD_DO_NOT_MATCH; 
                }
            }else{
                return USER_NOT_FOUND; 
            }
        }


        //Returns a user from the database using registered email address
        public function getUserByEmail($email){
            $stmt = $this->con->prepare("SELECT id, email, firstName, LastName, phone FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute(); 
            $stmt->bind_result($id, $email, $firstName, $lastName, $phone);
            $stmt->fetch(); 
            $user = array(); 
            $user['id'] = $id; 
            $user['email']=$email; 
            $user['firstName'] = $firstName; 
            $user['lastName'] = $lastName; 
            $user['phone'] = $phone; 
            return $user; 
        }


        //Returns a users associated password for verification during Login
        private function getUsersPasswordByEmail($email){
            $stmt = $this->con->prepare("SELECT password FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute(); 
            $stmt->bind_result($password);
            $stmt->fetch(); 
            return $password; 
        }


        //Check email for user record exists in database
        private function isEmailExist($email){
            $stmt = $this->con->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute(); 
            $stmt->store_result(); 
            return $stmt->num_rows > 0;  
        }

    }