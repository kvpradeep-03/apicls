<?php

require_once($_SERVER['DOCUMENT_ROOT'].'/api/lib/Database.class.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/api/lib/User.class.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/api/lib/OAuth.class.php');
require $_SERVER['DOCUMENT_ROOT']."/vendor/autoload.php";
class Auth
{
    private $username;
    private $password;
    private $token;
    private $db;
    private $isTockenAuth = false;
    private $loginTokens = null;

    public function __construct($username, $password = null)
    {
        $this->db = Database::getConnection();
        if($password == null) {
            //token based auth
            $this->token = $username;
            $this->isTockenAuth = true;
            //we have to validate the token
        } else {
            //passpord based auth
            $this->username = $username;    //it might be username or email.
            $this->password = $password;

        }

        if($this->isTockenAuth) {
            throw new Exception("Not Implemented");
        } else {
            $user = new User($this->username);
            $hash = $user->getPasswordHash();
            $this->username = $user->getUserName();
            if(password_verify($this->password, $hash)) {
                if(!$user->isActive()) {
                    throw new Exception("Check your email to activate your account.");
                }
                $this->loginTokens = $this->addSession();
            } else {
                throw new Exception("Password mismatch");
            }
        }
    }

    public function getAuthtokens()
    {
        return $this->loginTokens;
    }

    private function addSession()
    {   
        $oauth = new OAuth($this->username);
        $session = $oauth->newSession();
        return $session;
        
    }

    public static function generateRandomHash($len)
    {
        $bytes = openssl_random_pseudo_bytes($len, $cstrong);
        return bin2hex($bytes);
    }
}
