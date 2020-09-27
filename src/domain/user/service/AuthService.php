<?php


namespace straxus\domain\user\service;


use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use straxus\domain\base\service\BaseService;
use straxus\domain\user\entity\LoginRequest;
use straxus\domain\user\entity\LoginResponse;
use straxus\domain\user\entity\Permission;
use straxus\domain\user\entity\Token;
use straxus\domain\user\entity\User;
use straxus\modules\JwtHandler;
use straxus\modules\Mapper;


class AuthService extends BaseService
{

    protected $jwtHandler;
    private $jsonMapper;
    private $token;

    public function __construct()
    {
        parent::__construct();
        $this->jwtHandler = new JwtHandler();
        $this->jsonMapper = new Mapper();
        $this->token = $this->getToken();
        $this->log = new Logger('AuthService.php');
        $this->log->pushHandler(new StreamHandler($GLOBALS['rootDir'] . '/straxus.log', Logger::DEBUG));
    }


    public function getRole()
    {
        if (isset($this->token)) {
            $stmt = $this->db->prepare("select role.code from user inner join role on user.roleId = role.id where users.id=:id");
            $stmt->execute([
                "id" => $this->token->id
            ]);
            $role = $stmt->fetch(\PDO::FETCH_COLUMN);
            return $role;
        }
        return null;
    }


    /**
     * @param LoginRequest $request
     * @return LoginResponse
     */
    public function login(LoginRequest $request): ?LoginResponse
    {
        $stmt = $this->db->prepare("select * from user where username=:username and password=:password");

        $stmt->execute([
            "username" => $request->getUsername(),
            "password" => $request->getPassword(),
        ]);

        $userData = $stmt->fetch(\PDO::FETCH_ASSOC);
        if (isset($userData) && $userData) {
            $this->log->debug("userData debug" . json_encode($userData));
        } else {
            $this->log->error("invalid username and password!");
        }

        if (!$userData) {
            return null;
        }

        $mapper = new Mapper();
        /** @var User $user */
        $user = $mapper->classFromArray($userData, new User());
        $token = new Token();
        $token->id = $user->id;
        $token->username = $user->username;
        $token->role = $user->roleId;
        $token->exp = time() + (3600);
        $jwt = $this->jwtHandler->generateJwt($token);
        $response = new LoginResponse();
        $response->id = $user->id;
        $response->username = $user->username;
        $response->role = $user->roleId;
        $response->token = $jwt;
        $time = date('H:i:s');
        $this->setLogin($user->id, $jwt, $time);
        $response->permissions = $this->getPermissions($user->roleId);
        return $response;
    }


    public function setLogin($id, $jwt, $time)
    {
        $stmt = $this->db->prepare("Select * from login where userId=:userId");
        $stmt->execute([
            "userId" => $id
        ]);
        $count = $stmt->rowCount();
        if ($count === 0) {
            $stmt = $this->db->prepare("insert into login (userId, jwt, loggedTime) value(:userId,:jwt,:loggedTime)");
            $stmt->execute([
                'userId' => $id,
                'jwt' => $jwt,
                'loggedTime' => $time
            ]);
        } else {
            $stmt = $this->db->prepare("update login set loggedTime=:loggedTime where id=:id");
            $stmt->execute([
                "id" => $id,
                "loggedTime" => $time
            ]);
        }
    }

    /**
     * @param $roleId
     * @return Permission[]
     */
    public function getPermissions($roleId): ?array
    {
        $stmt = $this->db->prepare("select * from permission where roleId=:roleId");
        $stmt->execute([
            "roleId" => $roleId
        ]);
        $roleData = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        /** @var Permission[] $roles */
        $roles = [];
        $mapper = new Mapper();
        foreach ($roleData as $role) {
            $roles[] = $mapper->classFromArray($role, new Permission());
        }
        return $roles;
    }

    public function logout()
    {
        if (isset($_COOKIE['remember_user'])) {
            unset($_COOKIE['remember_user']);
            setcookie('remember_user', null, -1, '/');
            return true;
        } else {
            return false;
        }
    }
}
