<?php
return array(
    // MySQL
    'mysql_dao' => array(
        'default' => array(
            'host' => 'localhost', // The hostname of your database server. 
            'port' => '3306', // Port number, string
            'name' => '', // The name of the database you want to connect to
            'user' => '', // The username used to connect to the database
            'pass' => '', // The password used to connect to the database
            'charset' => 'utf8mb4', // The character collation used in communicating with the database
        ),
    ),
    
    // MySQLi
    'mysqli_dao' => array(
        'default' => array(
            'host' => 'localhost',
            'port' => 3306, // Port number, integer
            'name' => '',
            'user' => '',
            'pass' => '',
            'charset' => 'utf8mb4',
        ),
    ),
    
    // PostgreSQL
    'postgresql_dao' => array(
        'default' => array(
            'dsn' => 'host=localhost port=5432 user= password= dbname=',
            'charset' => 'UTF8',
        ),
    ),

    // SQLite3 - PDO
    'sqlite_dao' => array(
        'default' => array(
            'dsn' => 'sqlite:'.APP_DIR.'app.db',
        ),
    ),
);

// End of file: ./application/configs/data_source.php 