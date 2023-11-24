<?php
namespace App\Controllers\Api;
use \App\Controllers\Controller;

class TasksController extends Controller{
    /**
     * @param \App\Http\Request $req
     * @param \App\Http\Response $res
     */
    public function addTask($req,$res){
        $service=$req->post('service');
        $description=$req->post('description');
        $price=$req->post('price');
        if($service=$req->post('service') && $description=$req->post('price')){

            $this->model->insert(array(
                'user'=>$req->getCInfo('usrname'),
                'service'=>$service?$service:'',
                'description'=>$description,
                'price'=>$price
            ));
            $res->addHeader('HTTP/1.1 201 Created');
            $res->renderJSON(array(
                'message'=>"la facture a $price fc a été enregistrer avec succés"
            ));
        }else{

        }

    }
    
    /**
     * @param \App\Http\Request $req
     * @param \App\Http\Response $res
     */

    public function getUnfinishedTasks($req,$res){
        $data=$this->model->getLastBills();
        $res->renderJSON($data);
    }
    /**
     * @param \App\Http\Request $req
     * @param \App\Http\Response $res
     */

    public function getTotal($req,$res){
        $user=$req->get('user');
        if($user && $req->getCInfo('privillege')=='admin'){
            
            $data=array(
                'usrname'=>$user,
                'mth'=>date('n'),
                'yr'=>date('Y')
            );
        }else{

            $data=array(
                'usrname'=>$req->getCInfo('usrname'),
                'mth'=>date('n'),
                'yr'=>date('Y')
            );
        }
        $data=$this->model->getByMonth($data);
        $parcent=($data[0]*30)/100;
        $res->renderJSON(array(
            'total'=>$data[0],
            'parcent'=>$parcent
        ));
    }
    /**
     * @param \App\Http\Request $req
     * @param \App\Http\Response $res
     */

    public function getHistory($req,$res){
        $user=$req->get('user');
        if(is_null($user)){
            $data=$this->model->getLastBills($req->getCInfo('usrname'));
            $res->renderJSON($data);
            return;
        }
        if($req->getCInfo('privillege')=='admin'){
            
            $data=$this->model->getLastBills($user);
            $res->renderJSON($data);
        }
    }
    /**
     * @param \App\Http\Request $req
     * @param \App\Http\Response $res
     */

     public function setAsfinished($req,$res){
        $id=(int) $req->get('id');
        if($id && $req->getCInfo('privillege')=='admin'){
            $this->model->delete(array(
                'id'=>$id
            ));
            $res->renderJSON(array(
                'message'=>"la facture a $id fc a été supprimée avec succés"
            ));
        }
     }
}