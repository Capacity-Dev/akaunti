<?php
namespace App\Models;

class UsersModel extends Model{


    public function getUser($data,$one=false){

        $data=$this->prepare('SELECT * FROM users WHERE '.key($data).'=:'.key($data),$data);
        if($one){
            $array=$data->fetch();
            $user=$array;
        }
        else{
            $array=$data->fetchAll();
            $user=$array;
        }
        $data->closeCursor();
        return $user;
    }
    public function getUserDetails($data){
        return $this->getUser($data,true);

    }
    public function updateUser($data){
        $this->prepare("UPDATE users SET profession =:profession, privillege = :privillege WHERE id=:id",$data);
    }
    public function deleteUser($id){
        $id=(int)$id;
        if($id!==0){

            $req=$this->prepare('DELETE FROM users WHERE id=:id',array('id'=>$id));
            $req->closeCursor();
        }else
        {
            throw new \Exception("L'id doit etre un entier",'0011');//errorCode
        }

    }
    public function setUser($user){

        $this->insert($user);

    }
    public function getAllUsers(){
        return $this->query("SELECT id,usrname,profession,privillege FROM users")->fetchAll(\PDO::FETCH_ASSOC);
    }
    public function searchUsers($query){
        $query = "%$query%";
        $statement = $this->prepare("SELECT id,usrname,profession,privillege FROM users WHERE usrname LIKE :query OR profession LIKE :query LIMIT 20", ["query" => $query]);
        return $statement->fetchAll(\PDO::FETCH_ASSOC);
    }
}