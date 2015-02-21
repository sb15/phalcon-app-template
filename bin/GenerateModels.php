<?php

include dirname(__DIR__) . "/vendor/autoload.php";

define('APPLICATION_PATH', dirname(__DIR__) . '/application');
define('IS_DEVELOPMENT', true);

$config = include(APPLICATION_PATH . "/config/config.php");
$di = new \Phalcon\DI\FactoryDefault();
$di->set('db', function() use ($config) {
    $connection = new \Phalcon\Db\Adapter\Pdo\Mysql(array(
        "host" => $config->database->host,
        "username" => $config->database->username,
        "password" => $config->database->password,
        "dbname" => $config->database->name,
        "charset"  => $config->database->charset,
        "collation" => $config->database->collation
    ));
    return $connection;
});

$generator = new Sb\DbGenerator($di);
$generator->setEntityDir(APPLICATION_PATH . '/entities');
$generator->setModelDir(APPLICATION_PATH . '/models');
$options = array(
    'ignore' => array(
        'wp_commentmeta',
        'wp_comments',
        'wp_links',
        'wp_options',
        'wp_postmeta',
        'wp_terms',
        'wp_term_relationships',
        'wp_term_taxonomy',
        'wp_usermeta',
        'wp_users'
    )
);
$generator->generate($options);