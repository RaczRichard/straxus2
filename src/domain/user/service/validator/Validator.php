<?php


namespace straxus\domain\user\service\validator;


use straxus\domain\user\entity\LoginRequest;
use straxus\domain\user\entity\RegisterRequest;

class Validator
{
    public static function validateLogin(LoginRequest $request) : bool {
        if(strlen($request->getUsername()) === 0){
            return false;
        }

        if (strlen($request->getPassword()) === 0){
            return false;
        }

        return true;
    }
}
