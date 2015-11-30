<?php

namespace App;

use PDO;

class DB extends PDO
{
    
    public function __construct($config)
    {
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC 
        ];

        parent::__construct($config['dsn'], $config['user'], $config['password'], $options);    
    } // end __construct

    public function __call($func, $args)
    {
        if (!in_array($func, ['first', 'select', 'update'])) {
            throw new \Exception($func .' is not a valid mysql statement');
        }
        
        if (count($args) == 2) {
            $stmt = parent::prepare($args[0]);
            $stmt->execute($args[1]);
        } elseif ($args) {
            $stmt = parent::query($args[0]);
        }
        
        if ($func == 'first') {
            return $stmt->fetch();
        }

        if ($func == 'select') {
            return $stmt->fetchAll();
        }
        
        return $stmt->rowCount();
    } // end __call

}
