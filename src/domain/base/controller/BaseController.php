<?php


namespace straxus\domain\base\controller;


use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use straxus\domain\user\service\AuthService;

class BaseController
{

    protected $log;
    protected $authService;

    public function __construct()
    {
        $this->log = new Logger('BaseController.php');
        $this->log->pushHandler(new StreamHandler($GLOBALS['rootDir'].'/straxus.log', Logger::DEBUG));
        $this->authService = new AuthService();
    }

//    protected function hasRole(array $roles) : bool {
//        $role = $this->authService->getRole();
//        return in_array($role,$roles);
//   }

   protected function returnJson($data){
       $this->log->debug("return json " . json_encode($data));
        echo json_encode($data);
   }

    protected function returnError($message, $statusCode = 400)
    {
        $error["message"] = $message;
        echo json_encode($error);
        http_response_code($statusCode);
        exit;
    }
}
