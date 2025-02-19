<?php

use Carbon\Carbon;

require_once($_SERVER['DOCUMENT_ROOT'].'/api/lib/Database.class.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/api/lib/Share.class.php');
require $_SERVER['DOCUMENT_ROOT']."/vendor/autoload.php";

class Folder extends share
{
    private $db;
    private $data = null;
    private $id = null;

    public function __construct($id = null)
    {
        parent::__construct($id, 'folder');
        $this->db = Database::getConnection();
        if($id != null) {
            $this->id = $id;
            $this->refresh();
        }

    }

    public function refresh()
    {
        if($this->id != null) {
            $query = "SELECT * FROM folder WHERE id=$this->id";
            $result = mysqli_query($this->db, $query);
            if ($result and mysqli_num_rows($result) == 1) {
                $this->data = mysqli_fetch_assoc($result);
                if ($this->getOwner() != $_SESSION['username']) {
                    throw new Exception("Unauthorized");
                } 
                $this->id = $this->data['id'];
            } else {
                throw new Exception("Not found");
            }
        }
    }

    public function getOwner()
    {
        if($this->data and isset($this->data['owner'])) {
            return $this->data['owner'];
        }
    }

    public function getName()
    {
        if($this->data and isset($this->data['name'])) {
            return $this->data['name'];
        }
    }

    public function getId()
    {
        if($this->id) {
            return $this->id;
        }
    }

    public function createdAt()
    {
        if($this->data and isset($this->data['created_at'])) {
            return $this->data['created_at'];
        }
    }

    public function createNew($name = 'New Folder')
    {
        if(isset($_SESSION['username']) and strlen($name) <= 45) {
            $query = "INSERT INTO `folder` (`name`, `owner`) VALUES ('$name', '$_SESSION[username]');";
            if(mysqli_query($this->db, $query)) {
                $this->id = mysqli_insert_id($this->db);
                $this->refresh();
                return $this->id;
            }
        } else {
            throw new Exception("Cannot create note");
        }
    }

    public function rename($name)
    {
        if($this->id) {
            $query = "UPDATE `folder` SET `name` = '$name' WHERE(`id` = $this->id);";
            $result =  mysqli_query($this->db, $query);
            $this->refresh();
            return $result;
        } else {
            throw new Exception("Not found");
        }
    }

    public function delete()
    {
        if(isset($_SESSION['username']) and $this->getOwner() == $_SESSION['username']) {
            $notes = $this->getAllNotes();
            foreach($notes as $note) {
                $n = new Notes($note['id']);
                $n->delete();
            }
            if($this->id) {
                $query = "DELETE FROM `folder` WHERE (`id` = $this->id);";
                $result =  mysqli_query($this->db, $query);
                return $result;
            } else {
                throw new Exception("Not found");
            }
        } else {
            throw new Exception("Unauthorized");
        }

    }

    public function getAllNotes()
    {
        $query = "SELECT * FROM notes WHERE folder_id = $this->id";
        $result = mysqli_query($this->db, $query);
        if($result) {
            $data = mysqli_fetch_all($result, MYSQLI_ASSOC);
            for ($i = 0; $i < count($data); $i++) {
                $created_at = $data[$i]['created_at']; 
                $updated_at = $data[$i]['updated_at']; 
                $created_carbon = new Carbon($created_at);
                $updated_carbon = new Carbon($updated_at);
                $data[$i]['created'] = $created_carbon->diffForHumans();
                $data[$i]['updated'] = $updated_carbon->diffForHumans();
            }
            return $data;
        } else {
            return [];
        }
    }

    public function countNotes()
    {
        $query = "SELECT COUNT(*) FROM notes WHERE folder_id = $this->id";
        $result = mysqli_query($this->db, $query);
        if($result) {
            $data = mysqli_fetch_assoc($result);
            return $data['COUNT(*)'];
        }
    }

    public static function getAllFolders(){
        $db = Database::getConnection();
        $query = "SELECT * FROM `folder` WHERE `owner`='$_SESSION[username]'";
        $result = mysqli_query($db, $query);
        if($query){
            $data = mysqli_fetch_all($result, MYSQLI_ASSOC);
            for ($i = 0; $i < count($data); $i++) {
                $date = $data[$i]['created_at'];  // Store only the date
                $c = new Carbon($date);
                $data[$i]['created'] = $c->diffForHumans();
                $f = new Folder($data[$i]['id']);
                $data[$i]['count'] = $f->countNotes();
            }
            return $data;
        }else{
            return [];
        }
    }
    
}
