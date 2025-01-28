<?php
    error_reporting(E_ALL ^ E_DEPRECATED);
    require_once($_SERVER['DOCUMENT_ROOT']."/api/REST.api.php");
    require_once($_SERVER['DOCUMENT_ROOT']."/api/lib/Database.class.php");
    require_once($_SERVER['DOCUMENT_ROOT']."/api/lib/Signup.class.php");
    

    class API extends REST {

        public $data = "";

        private $db = NULL;

        public function __construct(){
            parent::__construct();                  // Init parent contructor
            $this->db = Database::getConnection();  // Initiate Database connection
        }

        /*
         * Public method for access api.
         * This method dynmically call the method based on the query string
         *
         */
        public function processApi(){
            $func = strtolower(trim(str_replace("/","",$_REQUEST['rquest'])));
            if((int)method_exists($this,$func) > 0)
                $this->$func();
            else
                $this->response('',400);                // If the method not exist with in this class, response would be "Page not found".
        }

        /*************API SPACE START*******************/

        private function about(){

            if($this->get_request_method() != "POST"){
                $error = array('status' => 'WRONG_CALL', "msg" => "The type of call cannot be accepted by our servers.");
                $error = $this->json($error);
                $this->response($error,406);
            }
            $data = array('version' => $this->_request['version'], 'desc' => 'This API is created by Blovia Technologies Pvt. Ltd., for the public usage for accessing data about vehicles.');
            $data = $this->json($data);
            $this->response($data,200);

        }

        private function test(){
                $data = $this->json(getallheaders());
                $this->response($data,200);
        }

        private function gen_hash(){
            if(isset($this->_request['pass'])){
                $s = new Signup("", $this->_request['pass'], "");
                $hash = $s->hashPassword();
                $data = [
                    "hash" => $hash,
                    "val" => $this->_request['pass'],
                    "verify" => password_verify($this->_request['pass'], $hash)
                ];
                $data = $this->json($data);
                $this->response($data,200);
            }
        }

        public function signup(){
            if($this->get_request_method() == "POST" and isset($this->_request['username']) and isset($this->_request['email']) and isset($this->_request['password'])){
                $username = $this->_request['username'];
                $password = $this->_request['password'];
                $email = $this->_request['email']; 
                
                try{
                    $s = new Signup($username, $password, $email);
                    $data = [
                        "message" => "Signup success",
                        "userid" => $s->getInsertID()
                    ];
                    $this->response($this->json($data),200);
                }catch(Exception $e){
                    $data = [
                        "error" => $e->getMessage(),
                    ];
                    $this->response($this->json($data),409);
                }
                
                
            }else{
                $data = [
                    "error" => "bad request"
                ];
                $data = $this->json($data);
                $this->response($data, 400);
            }
        }




        /*************API SPACE END*********************/

        /*
            Encode array into JSON
        */
        private function json($data){
            if(is_array($data)){
                return json_encode($data, JSON_PRETTY_PRINT);
            } else {
                return "{}";
            }
        }

    }

    // Initiiate Library

    $api = new API;
    $api->processApi();
?>

