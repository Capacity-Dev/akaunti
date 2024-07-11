<?php

namespace App\Controllers\Api;

use \App\Controllers\Controller;

class UsersController extends Controller
{
    /**
     * login function
     * @param \App\Http\Request $req
     * @param \App\Http\Response $res
     */
    public function login($req, $res)
    {
        $user = $req->post('username');
        $passwd = $req->post('passwd');
        if ($user) {
            $userData = $this->model->getUserDetails(["usrname" => $user],true);
            if ($userData) {

                if (password_verify($passwd, $userData["passwd"])) {
                    $user = array(
                        'id' => $userData["id"],
                        'username' => $userData["usrname"],
                        'privillege' => $userData["privillege"],
                        'affectations' => $userData["affectations"]
                    );
                    $token = $res->forgeToken($user);
                    return $res->renderJSON(
                        ["token" => $token]
                    );
                } else {
                    $res->addHeader('HTTP/1.1 401 Unauthorized');
                    return $res->renderJSON(array(
                        'error' => 'mot de passe invalide'
                    ));
                }
            } else {
                $res->addHeader('HTTP/1.1 401 Unauthorized');
                $res->renderJSON(array(
                    'error' => "le nom d'utilisateur est invalide"
                ));
            }
        } else {
            $res->addHeader('HTTP/1.1 400 Bad Request');
            $res->renderJSON(array(
                'error' => "mettez un nom d'utilisateur"
            ));
        }
    }
    /**
     * @param \App\Http\Request $req
     * @param \App\Http\Response $res
     */
    public function create($req, $res)
    {
        $user = $req->post('usrname');
        $affectations = $req->post('affectations');
        $privillege = $req->post('privillege');
        $passwd = $req->post('passwd');
        if ($user && $privillege && $passwd) {
            $userData = $this->userExists($user);
            if ($userData) {
                $res->addHeader('HTTP/1.1 401 Unauthorized');
                $res->renderJSON(array(
                    'error' => "l'utilisateur que vous essayez de creer existe déja"
                ));
            } else {
                $this->model->setUser(array(
                    'usrname' => $user,
                    'privillege' => $privillege,
                    'passwd' => password_hash($passwd, null)
                ));
                if(is_array($affectations) && !empty($affectations)) {
                    $userID = $this->userExists($user)->id;
                    $this->model->addAffectations($userID,$affectations);
                }
                $res->addHeader('HTTP/1.1 201 Created');
            }
        } else {
            $res->addHeader('HTTP/1.1 400 Bad Request');
            $res->renderJSON(array(
                'error' => "complete all Data"
            ));
        }
    }
    /**
     * @param \App\Http\Request $req
     * @param \App\Http\Response $res
     */
    public function update($req, $res)
    {
        $id = (int) $req->post('id');
        $affectations = $req->post('affectations');
        $privillege = $req->post('privillege');
        $passwd = $req->post("passwd");
        if ($id && $privillege) {
            $userData = $this->model->getUser(['id' => $id], true);
            if ($userData) {
                //for root, only password can be modified
                if (in_array($userData["privillege"],["admin","user"] ) || $req->getCInfo("privillege") == "root") {
                    $data = array(
                        'id' => $id,
                        'privillege' => $privillege
                    );
                    if (!is_null($passwd)) $data["passwd"] = password_hash($passwd, null);
                    $this->model->updateUser($data);
                    if(is_array($affectations) && !empty($affectations)) {
                        $this->model->clearAffectation($id);
                        $this->model->addAffectations($id,$affectations);
                    }
                    $res->renderJSON(array(
                        'message' => "données enregistrées !"
                    ));
                }
                else {
                    $res->addHeader('HTTP/1.1 400 Bad Request');
                    return $res->renderJSON(array(
                        'error' => "Vous n'avez pas les autorisations requises!"
                    ));
                }
            } else {
                $res->addHeader('HTTP/1.1 401 Unauthorized');
                $res->renderJSON(array(
                    'error' => "l'utilisateur que vous essayez de modifier n'existe pas"
                ));
            }
        } else {
            $res->addHeader('HTTP/1.1 400 Bad Request');
            $res->renderJSON(array(
                'error' => "complete all Data"
            ));
        }
    }
    /**
     * @param \App\Http\Request $req
     * @param \App\Http\Response $res
     */
    public function delete($req, $res)
    {
        $id = (int) $req->get('id');
        if ($id) {
            $user = $this->model->getUser(['id' => $id], true);
            if ($user) {
                if ($req->getCInfo('usrname') == $user['usrname']) {
                    $res->addHeader('HTTP/1.1 401 Unauthorized');
                    return $res->renderJSON(array(
                        'error' => "Vous ne pouvez pas supprimer votre propre compte"
                    ));
                } else if ($user['privillege'] == "root" && $req->getCInfo("privillege") != "root") {
                    $res->addHeader('HTTP/1.1 401 Unauthorized');
                    return $res->renderJSON(array(
                        'error' => "Pour des raisons techniques, vous ne pouvez pas supprimer ce compte"
                    ));
                } else {
                    return $this->model->deleteUser($id);
                }
            } else {
                $res->addHeader('HTTP/1.1 400 Bad Request');
                 return $res->renderJSON(array(
                    'error' => "l'utilisateur que vous essayez de supprimer n'existe pas"
                ));
            }
        } else {
            $res->addHeader('HTTP/1.1 400 Bad Request');
            return $res->renderJSON(array(
                'error' => "provide ID"
            ));
        }
    }
    /**
     * @param \App\Http\Request $req
     * @param \App\Http\Response $res
     */
    public function getAll($req, $res)
    {
        $res->renderJSON($this->model->getAllUsers());
    }
    /**
     * @param \App\Http\Request $req
     * @param \App\Http\Response $res
     */
    public function getOne($req, $res)
    {
        $id = (int) $req->get('id');
        if ($id) {
            $data = $this->model->getUserDetails(['id' => $id]);
            if ($data) {
                unset($data['passwd']);
                $res->renderJSON($data);
            } else {
                http_response_code(400);
                $res->renderJSON(['error' => 'cet utilisateur n\'existe pas']);
            }
        } else {
            http_response_code(400);
            $res->renderJSON(['error' => 'provide ID']);
        }
    }
    /**
     * @param \App\Http\Request $req
     * @param \App\Http\Response $res
     */
    public function search($req, $res)
    {
        $query = $req->get('q');
        if ($query) {
            $data = $this->model->searchUsers($query);
            $res->renderJSON($data ? $data : []);
        } else {
            http_response_code(400);
            $res->renderJSON(['error' => 'provide Query']);
        }
    }
    /**
     * function that verify if the user exists in the DB and return this if positif and false if negatif
     * @param string $username the username
     * @return \App\Database\Tables\UsersTable
     * @return false
     */
    public function userExists(string $username)
    {
        $userData = $this->model->getUser(array('usrname' => $username), true);
        return $userData ? $this->model->getTableInstances($userData, true) : false;
    }
}
