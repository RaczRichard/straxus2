<?php

function base64UrlEncode($text) // url friendly
{
    return str_replace(
        ['+', '/', '='],
        ['-', '_', ''],
        base64_encode($text)
    );
}

function dd($object){
    echo "<pre>";
    var_dump($object);
    echo "</pre>";
    die();
}

function ddj($object)
{
    echo "<pre>";
    var_dump(json_encode($object));
    echo "</pre>";
    die();
}

function cors()
{ // stack-overflow

    header('Content-Type: application/json');
    // Allow from any origin
    if (isset($_SERVER['HTTP_ORIGIN'])) {
        $GLOBALS['log']->debug("Server origin -> ".$_SERVER['HTTP_ORIGIN']);
        // Decide if the origin in $_SERVER['HTTP_ORIGIN'] is one
        // you want to allow, and if so:
        header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Max-Age: 86400');    // cache for 1 day
    }else{
        header("Access-Control-Allow-Origin: *");
    }

    // Access-Control headers are received during OPTIONS requests
    if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
        $GLOBALS['log']->debug("REQUEST_METHOD -> ".$_SERVER['REQUEST_METHOD']);
        if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
            // may also be using PUT, PATCH, HEAD etc
            header("Access-Control-Allow-Methods: GET, POST, OPTIONS");

        if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
            header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");

        exit(0);
    }

    $GLOBALS["log"]->debug("You have CORS!");
}
