<?php
/**
 * Share function is deprecated
 */
class share{

    public function __construct($id, $type){
        if($type == 'note' or $type == 'folder'){

        }else{
            throw new exception("Unknown share type");
        }
    
    }

    public function shareWith($username){

    }

    public function revoke($username){

    }

    public function hasAccess($usename){

    }

}


?>