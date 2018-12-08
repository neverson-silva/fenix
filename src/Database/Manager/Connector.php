<?php

namespace Fenix\Database\Manager;

use PDOException;
use PDO;

/**
 * Connect to databases
 *
 * @package Feniz
 * @subpackage Database\Manager
 * @license MIT
 * @author Neverson Bento da Silva <neversonbs13@gmail.com>
 */
class Connector
{
    private static $instance;
    
    private function __construct(){}
    
    private function __clone(){}
    
    private function __wakeup(){}

           /**
     *
     * Connect to database
     *
     * @param array $config An associate array with database configuration
     * @return \PDO
     */
    public static function getConnection(array $config)
    {
        $config = $config;
        if (!isset(self::$instance)) {
            $dns = sprintf(
                '%s:host=%s;dbname=%s;charset=%s',
                $config['driver'],
                $config['host'],
                $config['database'],
                $config['charset']
                );
            try {
                self::$instance = new PDO($dns, $config['username'], $config['password'], $config['options']);
            } catch (PDOException $e) {
                exit(utf8_encode($e->getMessage()));
            }
        }
        return self::$instance;
    } 
}