<?php


namespace Randi\modules;


use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class Mapper{

    private $log;

    public function __construct()
    {
        $this->log = new Logger('Mapper.php');
        $this->log->pushHandler(new StreamHandler($GLOBALS['rootDir'].'/randi.log', Logger::DEBUG));
    }

    public function jsonDecode($json, $class){
        $data = json_decode($json,true);
        return $this->classFromArray($data,$class);
    }

    public function classFromArray($array,$class){

        $this->log->info("classFromArray -> ",$array);

        foreach ($array as $key => $value){
            try {$class->{$key} = $value;}
            catch (\Exception $e){
                $this->log->error("error during mapping! ".get_class($class)." ".$key." => ".$value,[$e->getMessage()]);
            }
        }
        return $class;
    }
}
