<?php

/**
 * @author BEN BRAHIM FOUED
 */
class DbConnect {

    private $conn;

    function __construct() {        
    }

    /**
     * Establishing database connection
     * @return database connection handler
     */
    function connect() {
        $servername='localhost:3306';
		$username='root';
		$password='';
		$dbname='appartoo_CM_api';
		$conn = new PDO("mysql:host=$servername;
		dbname=$dbname", 
		$username, $password);
		return $conn;
       
    }

}

?>
