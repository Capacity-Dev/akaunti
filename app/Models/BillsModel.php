<?php

namespace App\Models;

class BillsModel extends Model{


    public function getLastBills($user=null){
        if(is_null($user)){

            $data=$this->query("SELECT * FROM bills ORDER BY created_at DESC LIMIT 0,10");
        }else{
            $data=$this->prepare("SELECT * FROM bills WHERE user= :user ORDER BY created_at DESC LIMIT 0,10",array(
                'user'=>$user
            ));
        }
        return $data->fetchAll(\PDO::FETCH_ASSOC);
       
    }
    public function getByMonth($data){
        $data=$this->prepare("SELECT SUM(price) FROM bills WHERE user = :usrname AND MONTH(created_at)= :mth AND YEAR(created_at)= :yr",$data);
        return $data->fetch();
    }
}