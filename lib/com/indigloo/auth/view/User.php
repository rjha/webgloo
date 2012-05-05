<?php 
namespace com\indigloo\auth\view {


    class User {
        
        public $firstName;
        public $lastName;
        public $userName ;
        public $email ;
        public $id ;
        
        static function create($row) {
            $user = new User();
            
            $user->firstName = $row['first_name'] ;
            $user->lastName = $row['last_name'] ;
            $user->userName = $row['user_name'];
            $user->email = $row['email'];
            $user->id = $row['id'];
            
            return $user ;
        }
    }
}
    
?>