<?php

require 'defaults.php';
require 'config/routing.php';


function loadRequests()
{
    $routing = $GLOBALS['routing'];
    $fails = [];

    $url = isset($_GET['_url']) ? $_GET["_url"] : null;
    $url = explode('/', $url);
    if (isset($url[1]) && !empty($url[1])) {

        $controller = new $routing[$url[1]]();

        if (isset($url[2]) && !empty($url[2])) {
            if (method_exists($controller, $url[2] . 'Action')) {

                if (isset($url[3]) && !empty($url[3])) {
                    $controller->{$url[2] . 'Action'}($url[3]);
                } else {
                    $controller->{$url[2] . 'Action'}();
                }
            } else {
                $fails[] = 'nemtetezo method';
            }
        }
        if (count($fails) > 0) {
            foreach ($fails as $fail) {
                echo $fail . "<br/>";
            }
        }
    }
}
cors();
loadRequests();
