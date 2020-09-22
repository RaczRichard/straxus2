<?php


namespace Randi\domain\user\service\validator;


use Randi\domain\user\entity\LoginRequest;
use Randi\domain\user\entity\RegisterRequest;

class Validator
{
    public static function validateRegister(RegisterRequest $request) : bool {
        if(!filter_var($request->getEmail(), FILTER_VALIDATE_EMAIL)){
            return false;
        }

        if (strlen($request->getPassword()) === 0){
            return false;
        }

        return true;

    }

    public static function validateLogin(LoginRequest $request) : bool {
        if(strlen($request->getEmail()) === 0){
            return false;
        }

        if (strlen($request->getPassword()) === 0){
            return false;
        }

        return true;
    }
}
