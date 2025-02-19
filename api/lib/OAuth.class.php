<?php

require_once($_SERVER['DOCUMENT_ROOT'].'/api/lib/Auth.class.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/api/lib/User.class.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/api/lib/Database.class.php');

class OAuth
{
    private $db;
    private $username;
    private $access_token = NULL;
    private $refresh_token = NULL;
    private $valid_for = 7200;
    private $user;

    /**
     * Can construct without refresh token for new session.
     * Can construct with refresh token for refresh session.
     */
    public function __construct($token = NULL)
    {
        $this->db = Database::getConnection();
        if($token !=NULL){
            if($this->startsWith($token, 'a.')) {
                $this->access_token = $token;
            } elseif($this->startsWith($token, 'r.')) {
                $this->refresh_token = $token;
            } else {
                $this->setUsername($token);
            }
        }

    }

    public function setUsername($username)
    {
        $this->username = $username;
        $this->user = new User($this->username);
    }

    public function getUsername()
    {
        return $this->username;
    }

    public function authenticate()
    {
        if($this->access_token != NULL) {
            $query = "SELECT * FROM `session` WHERE `access_token` = '$this->access_token'";
            $result = mysqli_query($this->db, $query);
            if($result) {
                $data = mysqli_fetch_assoc($result);
                $created_at = strtotime($data['created_at']);
                $expires_at = $created_at + $data['valid_for'];
                if(time() <= $expires_at) {
                    if(session_status() === PHP_SESSION_NONE) {
                        session_start();
                    }
                    $this->username = $_SESSION['username'] = $data['username'];
                    $_SESSION['token'] = $this->access_token;
                    return true;
                }else {
                    throw new Exception("Expired token");
                }
            }else {
                throw new Exception("Error: ". mysqli_error($this->db));
            } 
        }
    }

    public function newSession($valid_for = 7200, $reference_token = 'auth_grant')
    {
        if($this->username == NULL) {
            throw new Exception("Username not set for OAuth");
        }
        $this->valid_for = $valid_for;
        $this->access_token = 'a.'.Auth::generateRandomHash(32);
        if($reference_token == 'auth_grant') {
            $this->refresh_token = 'r.'.Auth::generateRandomHash(32);
        } else {
            $this->refresh_token = "d.".Auth::generateRandomHash(16);
        }
        $query = "INSERT INTO `session` (`username`, `access_token`, `refresh_token`, `valid_for`, `reference_token`) 
                   VALUES ('$this->username', '$this->access_token', '$this->refresh_token', '$this->valid_for', '$reference_token')";
        if(mysqli_query($this->db, $query)) {
            return array(
                "access_token" => $this->access_token,
                "valid_for" => $this->valid_for,
                "refresh_token" => $this->refresh_token,
                "reference_token" => $reference_token,
                "type" => "api"
            );
        } else {
            throw new Exception("unable to create session");
        }
    }

    public function refreshAccess()
    {
        if($this->refresh_token != NULL and !$this->startsWith($this->refresh_token, 'd.')) {
            $query = "SELECT * FROM `session` WHERE `refresh_token` = '$this->refresh_token'";
            $result = mysqli_query($this->db, $query);
            if($result) {
                $data = mysqli_fetch_assoc($result);
                if($data['valid'] == 1) {
                    $this->setUsername($data['username']);
                    return $this->newSession(7000, $this->refresh_token);

                } else {
                    throw new Exception("Expired token");
                }

            } else {
                throw new Exception("Error: ". mysqli_error($this->db));
            }
        } else {
            throw new Exception("Invalid request");
        }
    }

    private function startsWith($string, $startString)
    {
        $len = strlen($startString);
        return (substr($string, 0, $len) === $startString);
    }

    public function logout($username)
    {
        if($username == $_SESSION['username']){
            $query = "DELETE FROM `session` WHERE `username` = '$username' ";
            $result = mysqli_query($this->db, $query);
            if ($result) {
                return true;
            } else {
                throw new Exception("Accesstoken is required to logout");
            }
        }else{
            throw new exception("Unauthorized");
        }
    
    }
    

}
