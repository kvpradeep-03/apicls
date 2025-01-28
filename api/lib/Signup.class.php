<?php

require_once($_SERVER['DOCUMENT_ROOT'].'/api/lib/Database.class.php');
require $_SERVER['DOCUMENT_ROOT']."/vendor/autoload.php";

use Brevo\Client\Api\TransactionalEmailsApi;
use Brevo\Client\Configuration;
use GuzzleHttp\Client;

class Signup {

    private $username;
    private $password;
    private $email;
    public $id;
    private $token;

    private $db;

    public function __construct($username, $password, $email){
        $this->db = Database::getConnection();
        $this->username = $username;
        $this->password = $password;
        $this->email = $email;
        $bytes = random_bytes(16);
        $this->token = $token = bin2hex($bytes);
        $password = $this->hashPassword();
        $query = "INSERT INTO `auth` (`username`, `password`, `email`, `active`, `token`, `signup_time`) 
                  VALUES ('$username', '$password', '$email', '0', '$token', now());";

        if(!mysqli_query($this->db, $query)){
            throw new Exception("Unable to signup.");
        }else{
            $this->id = mysqli_insert_id($this->db);
            $this->sendVerificationMail();
        }
    }

    public function getInsertID(){
        return $this->id;
    }

    public function sendVerificationMail(){
        $config_json = file_get_contents('../../env.json');
        $config = json_decode($config_json, true);  

        $token = $this->token;
        // Replace with your Brevo API Key
        $apiKey = $config['email_api_key'];

        // Configure API key authorization
        $transConfig = Configuration::getDefaultConfiguration()->setApiKey('api-key', $apiKey);
        $apiInstance = new TransactionalEmailsApi(new Client(), $transConfig);

        // Email content
        $emailData = [
            'sender' => ['name' => 'apicls', 'email' => 'kvpradeep60@gmail.com'],
            'to' => [['email' => $this->email, 'name' => $this->username]],
            'subject' => 'Verify your account',
            'htmlContent' => '<h3> Please verify your account by <a href="https://apicls.zeal.wtf/verify?token=' . $token . '">clicking here</a> </h3>'

        ];

        try {
            $apiInstance->sendTransacEmail($emailData);
            //return "Email sent successfully!";
        } catch (Exception $e) {
            echo 'Error: ' . $e->getMessage();
        }

    }

    public function hashPassword(){
        return password_hash($this->password, PASSWORD_BCRYPT);
    }

    public static function verifyAccount($token){
        $query = "SELECT * FROM auth WHERE token='$token';";
        $db = Database::getConnection();
        $result = mysqli_query($db, $query);
        if($result and mysqli_num_rows($result) == 1){
            $data = mysqli_fetch_assoc($result);
            if($data['active'] == 1){
                throw new Exception("Already Verified");
            }

            mysqli_query($db, "UPDATE `auth` SET `active` = '1' WHERE (`token` = '$token');");
            return true;
        } else {
            return false;
        }
    }


}