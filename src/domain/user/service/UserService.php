<?php


namespace straxus\domain\user\service;


use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use straxus\domain\base\service\BaseService;
use straxus\domain\user\entity\LoginResponse;
use straxus\domain\user\entity\Permission;
use straxus\domain\user\entity\User;
use straxus\modules\Mapper;

class UserService extends BaseService
{
    public function __construct()
    {
        parent::__construct();
        $this->log = new Logger('UserService.php');
        $this->log->pushHandler(new StreamHandler($GLOBALS['rootDir'] . '/straxus.log', Logger::DEBUG));
    }

    /**
     * @return User[]
     */
    public function userList(): array //USEREK LISTÁZÁSA
    {
        $userId = $this->getUser()->id;
        $stmt = $this->db->prepare("select user.username, login.* from login inner join user on login.userId = user.id where login.userId=:userId");
        $stmt->execute([
            "userId" => $userId
        ]);
        $usersData = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        /**
         * @var User[] $users
         */
        $users = [];

        $mapper = new Mapper();
        foreach ($usersData as $user) {
            $users[] = $mapper->classFromArray($user, new User());
        }
        return $users;
    }

    /**
     * @return Permission[]
     */
    public function permissionList(): array
    {
        $roleId = $this->getUser()->roleId;
        $stmt = $this->db->prepare("select * from permission where roleId=:roleId");
        $stmt->execute([
           "roleId" => $roleId,
        ]);
        $roleData = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        /**
         * @var Permission[] $roles
         */
        $roles =[];

        $mapper = new Mapper();
        foreach($roleData as $role) {
            $roles[] = $mapper->classFromArray($role, new Permission());
        }
        return $roles;
    }
}
