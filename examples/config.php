<?php

    ini_set( 'display_errors', TRUE );
    error_reporting( E_ALL );
    
    define('DB_HOST','localhost');
    define('DB_NAME','employees');
    define('DB_USER','root');
    define('DB_PASS','password');
    define('DB_PORT',3306);
    
    require_once('../class.MySqlPDO.php');
    
    try {
        
        MySqlPDO::getInstance()->connect(DB_NAME,DB_USER,DB_PASS,DB_HOST,DB_PORT);
        
    } catch (Exception $ex) {
        
        echo 'Failed to connect to database';
        exit();
        
    }