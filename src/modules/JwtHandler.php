<?php


namespace Randi\modules;

use http\Exception\RuntimeException;
use Carbon\Carbon;
use MongoDB\Driver\Exception\InvalidArgumentException;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Randi\domain\user\entity\Token;


class JwtHandler
{

    private $log;

    public function __construct()
    {
        $this->log = new Logger('JwtHandler.php');
        $this->log->pushHandler(new StreamHandler($GLOBALS['rootDir'].'/randi.log', Logger::DEBUG));
    }

    public function generateSecret() : string {

        try {
            return bin2hex(random_bytes(32));
        } catch (\Exception $e) {
            throw new RuntimeException("Error creating secret!");
        }
    }

    public function generateJwt(Token $object) : string {
        $header = json_encode([
            'typ' => 'JWT',
            'alg' => 'HS256'
        ]);

        $payload = json_encode($object);

        $base64UrlHeader = base64UrlEncode($header);

        $base64UrlPayload = base64UrlEncode($payload);

        $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, getenv('SECRET'), true);

        $base64UrlSignature = base64UrlEncode($signature);

        $jwt = $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
        $this->log->debug("Jwt token : ".$jwt);
        return $jwt;
    }

    public function parseJwt($jwt) : Token{
        $tokenParts = explode('.', $jwt);
        $header = base64_decode($tokenParts[0]);
        $payload = base64_decode($tokenParts[1]);
        $signatureProvided = $tokenParts[2];

        $expiration = Carbon::createFromTimestamp(json_decode($payload)->exp);
        $tokenExpired = (Carbon::now()->diffInSeconds($expiration, false) < 0);

        $base64UrlHeader = base64UrlEncode($header);
        $base64UrlPayload = base64UrlEncode($payload);
        $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, getenv('SECRET'), true);
        $base64UrlSignature = base64UrlEncode($signature);

        $signatureValid = ($base64UrlSignature === $signatureProvided);

        if ($tokenExpired) {
            throw new InvalidArgumentException("Token has expired!");
        }

        if (!$signatureValid) {
            throw new InvalidArgumentException("Signature isn't valid!");
        }

        $mapper = new Mapper();

        return $mapper->jsonDecode($payload,new Token());
    }
}
