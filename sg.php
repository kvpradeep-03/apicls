<pre>
<?php

use Brevo\Client\Model\Note;

require_once($_SERVER['DOCUMENT_ROOT'].'/api/lib/User.class.php');
require $_SERVER['DOCUMENT_ROOT']."/vendor/autoload.php";
require_once($_SERVER['DOCUMENT_ROOT'].'/api/lib/Folder.class.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/api/lib/Notes.class.php');

// try{
//     $user =  new User('thechronicles5555@gmail.com');
//     echo $user->getUserName();
// }catch(Exception $e){
//     echo $e->getMessage();
// }

session_start();
$_SESSION['username'] = 'developer';

try {
    print_r(Folder::getAllFolders());
} catch(Exception $e) {
    echo $e->getMessage();
}



?>
</pre>