<pre>
<?php

require_once($_SERVER['DOCUMENT_ROOT'].'/api/lib/User.class.php');
require $_SERVER['DOCUMENT_ROOT']."/vendor/autoload.php";

try{
    $user =  new User('thechronicles5555@gmail.com');
    echo $user->getUserName();
}catch(Exception $e){
    echo $e->getMessage();
}
?>
</pre>