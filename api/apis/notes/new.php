<?php

${basename(__FILE__, '.php')} = function () {
    if($this->get_request_method() == "POST" and $this->isAuthenticated() and isset($this->_request['title']) and $this->_request['body'] and $this->_request['folder']) {
        $n = new Notes();
        $id = $n->createNew($this->_request['title'], $this->_request['body'], $this->_request['folder']);
        $data = [
            "note_id" => $id
        ];
        $data = $this->json($data);
        $this->response($data, 200);
    }else{
        $data = [
            "error" => "Bad request",
        ];
        $data = $this->json($data);
        $this->response($data, 400);
    }
};
