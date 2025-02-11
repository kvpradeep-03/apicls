<?php

${basename(__FILE__, '.php')} = function () {
    if($this->get_request_method() == "POST" and isset($this->_request['refresh_token'])) {
        $refresh_token = $this->_request['refresh_token'];
        try {
            $auth = new OAuth($refresh_token);
            $data = [
                "message" => "Refresh success",
                "tokens" => $auth->refreshAccess()
            ];
            $data = $this->json($data);
            $this->response($data, 200);
        } catch(Exception $e) {
            $data = [
                "error" => $e->getMessage(),
            ];
            $data = $this->json($data);
            $this->response($data, 406);
        }
    } else {
        $data = [
            "error" => "Bad request",
        ];
        $data = $this->json($data);
        $this->response($data, 400);
    }
};
