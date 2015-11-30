<?php

namespace App;

class Config
{

    private static $instance = null;
    private $config = [];

    private function __construct($config) 
    {
        $this->config = $config;
    } // end __construct

    private function __clone() {} // end __clone

    private static function getInstance()
    {
        if (is_null(self::$instance)) {
            $config = require app_path('config/app.php');
            self::$instance = new self($config);
        }
        return self::$instance;
    } // end getInstance
    
    private function getStatic($ident, $default = null)
    {
        if (!$this->hasOffset($ident)) {
            if (is_null($default)) {
                throw new \Exception('Missing configuration value for: '. $ident);
            }

            return $default;
        }
        return $this->config[$ident];
    } // end get
    
    private function allStatic()
    {
        return $this->config;
    } // end all
    
    private function hasOffset($ident)
    {
        return array_key_exists($ident, $this->config);
    } // end hasOffset

    public static function __callStatic($name, $arguments)
    {
        $instance = self::getInstance();
        $method = $name .'Static';
        
        return call_user_func_array(array($instance, $method), $arguments);
    } // end __callStatic
    
}
