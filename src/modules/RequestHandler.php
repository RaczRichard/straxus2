<?php


namespace Randi\modules;


class RequestHandler
{
    public static function getParam($name)
    {
        return isset($_GET[$name]) ? $_GET[$name] : null;
    }

    public static function postParam($name)
    {
        return isset($_POST[$name]) ? $_POST[$name] : null;
    }

}
