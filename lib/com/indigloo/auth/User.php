<?php

namespace com\indigloo\auth {

    use \com\indigloo\Util as Util;
    use \com\indigloo\mysql as MySQL;
    use \com\indigloo\exception\DBException;


    /**
     *
     * table structure used with this class
     * ======================================
     *  id
     *  user_name ,
     *  password ,
     *  first_name ,
     *   last_name ,
     *   email ,
     *   is_staff int default 0 ,
     *   is_admin int default 0,
     *   is_active int not null default 1,
     *  salt ,
     *  login_on TIMESTAMP,
     *  ip_address,
     *   created_on TIMESTAMP,
     *   updated_on TIMESTAMP
     *   =======================================
     *
     */
    class User {

        const USER_DATA = "WEBGLOO_USER_DATA" ;

        static function login($tableName,$email,$password) {
            // signal successful login by returning a valid loginId
            // otherwise throw an exception
            // caller should handle this exception
            
            $loginId = NULL ;

            if(empty($tableName)) {
                trigger_error("User Table name is not supplied",E_USER_ERROR);
                exit(1);
            }

            $mysqli = MySQL\Connection::getInstance()->getHandle();

            $password = trim($password);
            $email = trim($email);

            // change empty password - for time resistant attacks
            if (empty($password)) {
                $password = "123456789000000000";
            }

            $sql = " select * from {table} where is_active = 1 and email = '".$email. "' " ;
            $sql = str_replace("{table}", $tableName, $sql);

            $row = MySQL\Helper::fetchRow($mysqli, $sql);

            if (!empty($row)) {

                $dbSalt = $row['salt'];
                $dbPassword = $row['password'];
                // compute the digest using form password and db Salt
                $message = $password.$dbSalt;
                $computedDigest = sha1($message);

                $outcome = strcmp($dbPassword, $computedDigest);

                //good password
                // get and return loginId
                if ($outcome == 0) {
                    $loginId = $row["login_id"] ;
                    
                    //set user data in session
                    $udata = array();
                    $udata["is_admin"] = $row["is_admin"];
                    $udata["is_staff"] = $row["is_staff"];
                    $udata["login_id"] = $loginId ;

                    
                    $_SESSION[self::USER_DATA] = $udata;
                }
            }

            if(empty($loginId) || is_null($loginId)) {
                throw new \com\indigloo\exception\DBException("wrong email or password",401);
            }

            return $loginId;
        }

        static function create(
            $tableName,
            $firstName,
            $lastName,
            $userName,
            $email,
            $password,
            $loginId,
            $remoteIp) {

            if(empty($tableName)) {
                throw new \com\indigloo\exception\DBException("User Table name is not supplied",1);
                exit(1);
            }

            Util::isEmpty('Email',$email);
            Util::isEmpty('User Name',$userName);

            $mysqli = MySQL\Connection::getInstance()->getHandle();

            // use random salt + login and password
            // to create SHA-1 digest
            $salt = substr(md5(uniqid(rand(), true)), 0, 8);

            $password = trim($password);
            $userName = trim($userName);
            $email = trim($email);

            $message = $password.$salt;
            $digest = sha1($message);

            $sql = " insert into {table} (first_name, last_name, user_name,email,password, " ;
            $sql .= " salt,created_on,is_staff,login_id,ip_address) ";
            $sql .= " values(?,?,?,?,?,?,now(),0,?,?) ";
            $sql = str_replace("{table}", $tableName,$sql);

            //store computed password and random salt
            $stmt = $mysqli->prepare($sql);
            if ($stmt) {
                $stmt->bind_param("ssssssis",
                        $firstName,
                        $lastName,
                        $userName,
                        $email,
                        $digest,
                        $salt,
                        $loginId,
                        $remoteIp);

                $stmt->execute();

                if ($mysqli->affected_rows != 1) {
                    MySQL\Error::handle($stmt);
                }
                $stmt->close();
            } else {
                MySQL\Error::handle($mysqli);
            }

        }

        static function changePassword($tableName,$loginId,$email,$password) {

            if(empty($tableName)) {
                trigger_error("User Table name is not supplied",E_USER_ERROR);
                exit(1);
            }

            Util::isEmpty('Email',$email);
            Util::isEmpty('Password',$password);
            
            $mysqli = MySQL\Connection::getInstance()->getHandle();

            // get random salt
            $salt = substr(md5(uniqid(rand(), true)), 0, 8);
            $password = trim($password);
            $message = $password.$salt ;

            //create SHA-1 digest from email and password
            // we store this digest in table
            $digest = sha1($message);

            $sql = " update {table} set updated_on=now(), salt=?, password=? where email = ? and login_id = ?" ;
            $sql = str_replace("{table}", $tableName, $sql);

            $stmt = $mysqli->prepare($sql);

            if($stmt) {
                $stmt->bind_param("sssi", $salt, $digest,$email,$loginId);
                $stmt->execute();
                $stmt->close();

            } else {
                MySQL\Error::handle($mysqli);
            }

        }

    }

}
?>
