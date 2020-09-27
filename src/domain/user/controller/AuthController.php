<?php


namespace straxus\domain\user\controller;

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use straxus\domain\base\controller\BaseController;
use straxus\domain\user\entity\LoginRequest;
use straxus\domain\user\service\validator\Validator;
use straxus\modules\RequestHandler;

class AuthController extends BaseController
{

    public function __construct()
    {
        parent::__construct();
        $this->log = new Logger('AuthController.php');
        $this->log->pushHandler(new StreamHandler($GLOBALS['rootDir'] . '/straxus.log', Logger::DEBUG));
    }

    /**
     * http://straxus/auth/login
     */
    public function loginAction()
    {
        $_POST = json_decode(file_get_contents('php://input'), true);
        $username = RequestHandler::postParam('username') ?: '';
        $password = RequestHandler::postParam('password') ?: '';

        $request = new LoginRequest();
        $request->setUsername($username);
        $request->setPassword($password);

        if (Validator::validateLogin($request)) {
            $response = $this->authService->login($request);
            if ($response) {
                $this->returnJson($response);
            } else {
                $this->returnError('Hibás felhasználónév vagy jelszó!', 401);
            }

        } else {
            $this->returnError('Nem létező felhasználó név!');
        }
    }

    /**
     * http://straxus/auth/logout
     */
    public function logoutAction(){
        $this->authService->logout();
    }
}
