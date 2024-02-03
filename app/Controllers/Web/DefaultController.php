<?php

namespace App\Controllers\Web;

use \App\Controllers\Controller;

/**
 * controller for all guest pages
 */
class DefaultController extends Controller
{
    /**
     * the login page
     * @param \App\Http\Request $req
     * @param \App\Http\Response $res
     */
    public function login($req, $res)
    {

        $res->render('login.html');
    }
    /**  the logout page
     * @param \App\Http\Request $req
     * @param \App\Http\Response $res
     */
    public function logout($req, $res)
    {
        unset($_SESSION['username']);
        unset($_SESSION['privillege']);
        $res->redirect('/login', true);
    }
    /**
     * the dashboard front app
     * @param \App\Http\Request $req
     * @param \App\Http\Response $res
     */
    public function dashboard($req, $res)
    {

        $res->render('dashboard/index.html');
    }
    /**
     * the cash desk front app
     * @param \App\Http\Request $req
     * @param \App\Http\Response $res
     */
    public function front($req, $res)
    {

        $res->render('index.html');
    }
}
