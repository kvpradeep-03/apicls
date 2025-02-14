<?php

${basename(__FILE__, '.php')} = function(){
    if($this->get_request_method() == "POST" and $this->isAuthenticated() and isset($this->_request['username'])){
        try{
            $oauth = new OAuth();
            $oauth->logout($this->_request['username']);
            $data = [
                "message" => 'Logout Success',
            ];
            $data = $this->json($data);
            $this->response($data, 200);
        }catch(Exception $e){
            $data = [
                "error" => $e->getMessage(),
            ];
            $data = $this->json($data);
            $this->response($data, 406);
        }
    }else{
        $data = [
            "error" => "Bad request",
        ];
        $data = $this->json($data);
        $this->response($data, 400);
    }

};