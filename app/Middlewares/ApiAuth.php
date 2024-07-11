<?php
namespace App\Middlewares;

use \App\Controllers\Api\UsersController;
use Firebase\JWT\JWT;

/**
 * All about user authentification
 */
class ApiAuth extends Middleware
{

    protected $guest = array(
        '/api/last-bill',
        '/api/login',
    );
    protected $user = array(
        '/api/save-bill',
        '/api/search-products',
        '/api/get-config',
        '/api/get',
        '/api/get-sells/',
        '/api/get-sells'
    );

    public function __construct()
    {

    }
    /**
     * login function
     * @param \App\Http\Request $req
     * @param \App\Http\Response $res
     */
    public function run($req, $res)
    {
        $url = $req->getPath();

        if (!in_array($url, $this->guest) && !$req->getCInfo('username')) {
            $res->addHeader('HTTP/1.0 401 Unauthorized');
            $res->renderJSON(array('error' => 'Echec d\'identification' . $req->getCInfo('username')));
            $this->breakScript();
        } else if ($req->getCInfo('privillege') == "user" && (!in_array($url, $this->user) && !in_array($url, $this->guest))) {
            $res->addHeader('HTTP/1.0 401 Unauthorized');
            $res->renderJSON(array('error' => 'Action non autorisé' . $url, 'url' => $url));
            $this->breakScript();
        }
    }
}
