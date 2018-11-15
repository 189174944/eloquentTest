<?php

include 'vendor/autoload.php';
use Test\Config;
class Database
{
    private static $instance = null;
    private static $config;

    static public function getInstance()
    {
        if (self::$instance != null) {
            return self::$instance;
        }
        return new self();
    }

    public function getDB()
    {
        echo 10001;
        return $this;
    }
}

Database::getInstance()->getDB();

\Test\Person::say();

$c = new Config();
echo $c->item;