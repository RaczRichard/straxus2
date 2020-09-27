<?php


namespace straxus\domain\user\controller;


use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use straxus\domain\base\controller\BaseController;
use straxus\domain\user\service\UserService;
use straxus\modules\RequestHandler;

class UserController extends BaseController
{
    private $userService;
    public function __construct()
    {
        parent::__construct();
        $this->userService = new UserService();
        $this->log = new Logger('AuthController.php');
        $this->log->pushHandler(new StreamHandler($GLOBALS['rootDir'].'/straxus.log', Logger::DEBUG));
    }

    /** http://straxus/User/userlist */
    public function userListAction(){
        $this->returnJson($this->userService->userList());
    }
    /** http://straxus/User/tokenlist */
    public function permissionListAction(){
        $this->returnJson($this->userService->permissionList());
    }
}
