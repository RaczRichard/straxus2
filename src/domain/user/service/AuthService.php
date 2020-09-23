<?php


namespace Randi\domain\user\service;


use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Randi\domain\base\service\BaseService;
use Randi\domain\user\entity\LoginRequest;
use Randi\domain\user\entity\LoginResponse;
use Randi\domain\user\entity\RegisterRequest;
use Randi\domain\user\entity\Token;
use Randi\domain\user\entity\User;
use Randi\domain\user\entity\Verification;
use Randi\modules\JwtHandler;
use Randi\modules\Mapper;


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
        $this->log->pushHandler(new StreamHandler($GLOBALS['rootDir'] . '/randi.log', Logger::DEBUG));
    }


    public function getRole()
    {
        if (isset($this->token)) {
            $stmt = $this->db->prepare("select role.code from user inner join role on user.roleId = role.id where user.id=:id");
            $stmt->execute([
                "id" => $this->token->id
            ]);
            $role = $stmt->fetch(\PDO::FETCH_COLUMN);
            return $role;
        }
        return null;
    }

    /**
     * @param RegisterRequest $request
     * @return int
     */
    public function registerUser(RegisterRequest $request, $passwordAgain)
    {
        //ellenőrzöm, h van-e már ilyen emailcim
        $stmt = $this->db->prepare('select * from user where email=:email');
        $stmt->execute([
            'email' => $request->getEmail(),
        ]);
        $count = $stmt->rowCount();
        //ha nincs ilyen email
        if ($count === 0) {
            //password és a password újra ellenőrzés
            if ($request->getPassword() === $passwordAgain) {
                //Profile fillelése
                $stmt = $this->db->prepare("insert into profile (status) values (:status)");
                $stmt->execute([
                    'status' => 1
                ]);
                $lastInsertId = $this->db->lastInsertId();

                //user feltöltése
                $stmt = $this->db->prepare("insert into user 
                                              (email, password, profileId, status) 
                                              values 
                                              (:email, :password, :profileId,:status)");
                $success = $stmt->execute([
                    "email" => $request->getEmail(),
                    "password" => $request->getPassword(),
                    "profileId" => $lastInsertId,
                    "status" => 1
                ]);

                //verifications feltöltése
                $uuid = mt_rand(0, 0xffff);
                $stmt = $this->db->prepare("insert into verification (userId, uuid) values (:userId, :uuid)");
                $stmt->execute([
                    "userId" => $lastInsertId,
                    "uuid" => $uuid,
                ]);

                //mail küldés
                $this->log->debug("verify: " . $uuid);
                $subject = "Randi hitelesités";
                $txt = "Regisztráció megerősitéséhez ide katt: http://localhost:4200/verification/" . $uuid;
                $headers = "From: 126456randi@gmail.com";
                mail($request->getEmail(), $subject, $txt, $headers);
                $this->log->debug("MAIL email értéke: " . $request->getEmail());
                $this->log->debug("MAIL full: " . mail($request->getEmail(), $subject, $txt, $headers));
                $this->log->debug(json_encode($request));

                //ellenőrzés
                if (!$success) {
                    $this->log->error("couldn't register user");
                }
            }
        }
    }

    /**
     * @param LoginRequest $request
     * @return LoginResponse
     */
    public function login(LoginRequest $request): ?LoginResponse
    {
        $stmt = $this->db->prepare("select * from user where email=:email and password=:password and status=:status");

        $stmt->execute([
            "email" => $request->getEmail(),
            "password" => $request->getPassword(),
            "status" => 2
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
        $token->email = $user->email;
        $token->id = $user->id;
        $token->exp = time() + (3600);
        $jwt = $this->jwtHandler->generateJwt($token);
        $response = new LoginResponse();
        $response->id = $user->id;
        $response->email = $user->email;
        $response->token = $jwt;
        return $response;
    }

    /**
     * @param string $uuid
     */
    public function verification($uuid)
    {
        //UUID alapján kikeresem a datat
        $this->log->debug('uuid: ' . $uuid);
        $stmt = $this->db->prepare("select * from verification where uuid=:uuid");
        $stmt->execute([
            "uuid" => $uuid,
        ]);
        /** @var Verification $verifyData */
        $verifyData = $stmt->fetch(\PDO::FETCH_ASSOC);
        $this->log->debug("verify: " . json_encode($verifyData));
        $mapper = new Mapper();
        $verifyDatas = $mapper->classFromArray($verifyData, new Verification());
        $this->log->debug("direktben az id: " . $verifyDatas->userId);

        //megkerem profile Id alapján a sort és statust felcsapom 1-re
        $stmt = $this->db->prepare("update user set status=:status where id=:id");
        $stmt->execute([
            "id" => $verifyDatas->userId,
            "status" => 2,
        ]);
    }

    /**
     * @param User $email
     */
    public function resetPass($email)
    {
        //Új jelszót kap a USER
        $this->log->debug('email: ' . json_encode($email));
        $this->log->debug('email: ' . $email->email);
        /** @var string $pass */
        $pass = mt_rand(0, 0xffff);
        $this->log->debug("random pass: " . $pass);
        $stmt = $this->db->prepare("update user set password=:password where email=:email");
        $stmt->execute([
            "email" => $email->email,
            "password" => $pass
        ]);
        //emailt küldök róla
        $subject = "Jelszó változtatás";
        $txt = "Az új jelszó, amivel betudsz lépni: " . $pass;
        $headers = "From: 126456randi@gmail.com";
        mail($email->email, $subject, $txt, $headers);
    }

    /**
     * @param User $password
     */
    public function changePassword($old, $newPass, $again)
    {
        //ellenőrzés
        $this->log->debug('old: ' . json_encode($old));
        $this->log->debug('$newPass: ' . json_encode($newPass));
        $this->log->debug('$again: ' . json_encode($again));
        $getUserId = $this->getUser()->id;
        $getUserPass = $this->getUser()->password;


        //új password berakása
        if ($getUserPass === $old) {
            if ($newPass === $again) {
                $stmt = $this->db->prepare('update user set password=:newPass where id=:id');
                $stmt->execute([
                    'id' => $getUserId,
                    'newPass' => $newPass,
                ]);
            }
        }
    }

    public function changeEmail($email)
    {
        $this->log->debug('email: ' . json_encode($email));
    }
}
