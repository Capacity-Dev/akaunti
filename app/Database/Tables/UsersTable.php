<?php

namespace App\Database\Tables;

class UsersTable extends Table{

    

    public function getLink(){
       return '/@'.$this->username; 
    }
    
}