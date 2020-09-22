<?php


namespace Randi\domain\user\controller;

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Randi\domain\base\controller\BaseController;
use Randi\domain\user\entity\LoginRequest;
use Randi\domain\user\entity\RegisterRequest;
use Randi\domain\user\entity\User;
use Randi\domain\user\service\validator\Validator;
use Randi\modules\RequestHandler;

class AuthController extends BaseController
{

    public function __construct()
    {
        parent::__construct();
        $this->log = new Logger('AuthController.php');
        $this->log->pushHandler(new StreamHandler($GLOBALS['rootDir'] . '/randi.log', Logger::DEBUG));
    }

    /**
     * http://straxus/login/login
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
            $this->returnError('Valami hiba történt az adatfeldolgozása közben!');
        }
    }

    /**
     * http://straxus/registration/save
     */
    public function registerAction()
    {
        $_POST = json_decode(file_get_contents('php://input'), true);
        $username = RequestHandler::postParam('username') ?: '';
        $password = RequestHandler::postParam('password') ?: '';
        $passwordAgain = RequestHandler::postParam('passwordAgain') ?: '';

        $request = new RegisterRequest();
        $request->setUsername($username);
        $request->setPassword($password);

        if (Validator::validateRegister($request)) {
            $this->authService->registerUser($request, $passwordAgain);
        } else {
            $this->returnError('Nem sikerült a regisztráció!');
        }

    }

    /**
     * http://straxus/auth/verification
     * @param string $uuid
     */
    public function verificationAction($uuid)
    {
        $this->log->debug("uuid: " . $uuid);
        $this->returnJson($this->authService->verification($uuid));
    }

    /**
     * http://straxus/auth/reset
     */
    public function resetAction()
    {
        $_POST = json_decode(file_get_contents('php://input'), true);
        $email = new User();
        $email->email = RequestHandler::postParam('email') ?: '';
        if (isset($email)) {
            $this->returnJson($this->authService->resetPass($email));
        } else {
            $this->returnError('Üresen hagytad a mezőt!');
        }
    }

    /**
     * http://straxus/auth/passwordChange
     */
    public function passwordChangeAction()
    {
        $_POST = json_decode(file_get_contents('php://input'), true);
        $old = RequestHandler::postParam('old') ?: '';
        $newPass = RequestHandler::postParam('newPass') ?: '';
        $again = RequestHandler::postParam('again') ?: '';
        $this->returnJson($this->authService->changePassword($old, $newPass, $again));
    }
}
