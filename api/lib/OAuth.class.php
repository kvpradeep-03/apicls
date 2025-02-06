<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/api/lib/Auth.class.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/api/lib/User.class.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/api/lib/Database.class.php');

class OAuth{
    private $db;
    private $username;
    private $access_token;
    private $refresh_token;
    private $valid_for = 7200;

    public function __construct($username, $refresh_token = NULL){
        $this->refresh_token = $refresh_token;
        $this->db = Database::getConnection();;
        $this->username = $username;
        $u = new User($username);

    }

    public function newSession($valid_for = 7200){
        $this->valid_for = $valid_for;
        $this->access_token = Auth::generateRandomHash(32);
        $this->refresh_token = Auth::generateRandomHash(32);
        $query = "INSERT INTO `session` (`username`, `access_token`, `refresh_token`, `valid_for`, `reference_token`) 
                   VALUES ('$this->username', '$this->access_token', '$this->refresh_token', '$this->valid_for', 'auth_grant')";
        if(mysqli_query($this->db, $query)) {
            return array(
                "access_token" => $this->access_token,
                "valid_for" => $this->valid_for,
                "refresh_token" => $this->refresh_token,
                "type" => "api"
            );
        } else {
            throw new Exception("unable to create session");
        }
    }

    public function refreshAccess(){
        if($this->refresh_token){
            $query = "SELECT * FROM `session` WHERE `refresh_token` = '$this->refresh_token'";
            $result = mysqli_query($this->db, $query);
            if($result){
                $data = mysqli_fetch_assoc($result);
                if($data['valid'] == 1){

                }else{
                    throw new Exception("Expired token");
                }

            }else{
                throw new Exception("Invalid request");
            }
        }
    }

}





?>