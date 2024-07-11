<?php

namespace App\Models;

class UsersModel extends Model
{


    public function getUser($data, $one = false)
    {

        $data = $this->prepare('SELECT * FROM users WHERE ' . key($data) . '=:' . key($data), $data);
        if ($one) {
            $array = $data->fetch();
            $user = $array;
        } else {
            $array = $data->fetchAll();
            $user = $array;
        }
        $data->closeCursor();
        return $user;
    }
    public function getUserDetails($data,$withPassword=false)
    {
        $key = key($data);
        $cond = "u.$key = :$key";
        $pass = $withPassword ? ",u.passwd":"";
        $sql = "SELECT 
                    u.id, 
                    u.usrname, 
                    u.privillege,
                    l.location_id, 
                    l.location_name,
                    l.location_type
                    $pass
                FROM 
                    users u
                LEFT JOIN 
                    affectations ON u.id = affectations.user_id
                LEFT JOIN 
                    locations l ON affectations.location_id = l.location_id
                WHERE $cond
            ";
        $result = $this->prepare($sql,$data)->fetchAll(\PDO::FETCH_ASSOC);
        $users = [];
        $affectations = [];

        foreach ($result as $row) {
            $userId = $row["id"];
            if (!isset($affectations[$userId])) {
                $affectations[$userId] = [
                    "id" => $userId,
                    "privillege" => $row["privillege"],
                    "usrname" => $row["usrname"],
                    "affectations" => []
                ];
                $withPassword ? $affectations[$userId]["passwd"] = $row["passwd"] : null;
            }
            if (!empty($row["location_id"])) {
                $affectations[$userId]["affectations"][] = [
                    "location_id" => $row["location_id"],
                    "location_name" => $row["location_name"],
                    "location_type" => $row["location_type"]
                ];
            }
        }

        foreach ($affectations as $user) {
            $users[] = $user;
        }
        return isset($users[0]) ? $users[0] : false ;
    }
    public function updateUser($data)
    {
        $keys = array_keys($data);
        unset($keys["id"]);
        $values = join(",", array_map(function ($value) {
            return "$value = :$value";
        }, $keys));
        $this->prepare("UPDATE users SET $values WHERE id=:id", $data);
    }
    public function addAffectations($userID,$affectations)
    {
        $pdo = $this->db->getPDO();
        $statement = $pdo->prepare("INSERT INTO affectations (location_id,user_id) VALUES(:location_id,:user_id)");
        foreach ($affectations as $affectation) {
            $statement->bindValue(':location_id', $affectation['location_id'], \PDO::PARAM_INT);
            $statement->bindValue(':user_id', $userID, \PDO::PARAM_INT);
            $statement->execute();
        }
    }
    public function clearAffectation($id) {
        return $this->prepare("DELETE FROM affectations WHERE user_id = :user_id",["user_id" => $id]);
    }
    public function deleteUser($id)
    {
        $id = (int)$id;
        if ($id !== 0) {

            $req = $this->prepare('DELETE FROM users WHERE id=:id', array('id' => $id));
            $req->closeCursor();
        } else {
            throw new \Exception("L'id doit etre un entier", '0011'); //errorCode
        }
    }
    public function setUser($user)
    {
        $this->insert($user);
    }
    public function getAllUsers()
    {   
        $sql = "SELECT 
                    u.id, 
                    u.usrname, 
                    u.privillege,
                    l.location_id, 
                    l.location_name
                FROM 
                    users u
                LEFT JOIN
                    affectations ON u.id = affectations.user_id
                LEFT JOIN 
                    locations l ON affectations.location_id = l.location_id
            ";
        $result = $this->query($sql)->fetchAll(\PDO::FETCH_ASSOC);
        $users = [];
        $affectations = [];

        foreach ($result as $row) {
            $userId = $row["id"];
            if (!isset($affectations[$userId])) {
                $affectations[$userId] = [
                    "id" => $userId,
                    "privillege" => $row["privillege"],
                    "usrname" => $row["usrname"],
                    "affectations" => []
                ];
            }
            if (!empty($row["location_id"])) {
                $affectations[$userId]["affectations"][] = [
                    "location_id" => $row["location_id"],
                    "location_name" => $row["location_name"]
                ];
            }
        }

        foreach ($affectations as $user) {
            $users[] = $user;
        }
        return $users;
    }
    public function searchUsers($query)
    {
        $query = "%$query%";
        $sql = "SELECT 
                    u.id, 
                    u.usrname, 
                    u.privillege,
                    l.location_id, 
                    l.location_name
                FROM 
                    users u
                LEFT JOIN 
                    affectations ON u.id = affectations.user_id
                LEFT JOIN 
                    locations l ON affectations.location_id = l.location_id
                WHERE u.usrname LIKE :query
            ";
        $result = $this->prepare($sql, ["query" => $query])->fetchAll(\PDO::FETCH_ASSOC);
        $users = [];
        $affectations = [];

        foreach ($result as $row) {
            $userId = $row["id"];
            if (!isset($affectations[$userId])) {
                $affectations[$userId] = [
                    "id" => $userId,
                    "privillege" => $row["privillege"],
                    "usrname" => $row["usrname"],
                    "affectations" => []
                ];
            }
            if (!empty($row["location_id"])) {
                $affectations[$userId]["affectations"][] = [
                    "location_id" => $row["location_id"],
                    "location_name" => $row["location_name"]
                ];
            }
        }

        foreach ($affectations as $user) {
            $users[] = $user;
        }
        return $users;
    }
}
