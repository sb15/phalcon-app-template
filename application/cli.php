<?php

 use Phalcon\DI\FactoryDefault\CLI as CliDI,
     Phalcon\CLI\Console as ConsoleApp;

 define('VERSION', '1.0.0');

if (getenv('APPLICATION_ENV') == 'development') {
    define('IS_DEVELOPMENT', true);
} else {
    define('IS_DEVELOPMENT', false);
}

 //Using the CLI factory default services container
 $di = new CliDI();

 // Define path to application directory
 defined('APPLICATION_PATH')
 || define('APPLICATION_PATH', realpath(dirname(__FILE__)));

 /**
  * Register the autoloader and tell it to register the tasks directory
  */

include (APPLICATION_PATH . '/../vendor/autoload.php');

$buildFile = APPLICATION_PATH . '/../.git/refs/heads/master';
define("BUILD_UNIQUE_ID", substr(file_get_contents($buildFile), 0, 15));

if (!IS_DEVELOPMENT) {
    $client = new Raven_Client('');
    $error_handler = new Raven_ErrorHandler($client);
    $error_handler->registerExceptionHandler();
    $error_handler->registerErrorHandler();
    $error_handler->registerShutdownFunction();
}

// Load the configuration file (if any)
if(is_readable(APPLICATION_PATH . '/config/config.php')) {
    $config = include APPLICATION_PATH . '/config/config.php';
    $di->set('config', $config);
}

$eventsManager = new Phalcon\Events\Manager();

$di->set('db', function() use ($config, $eventsManager) {

    $connection = new \Phalcon\Db\Adapter\Pdo\Mysql(array(
        "host" => $config->database->host,
        "username" => $config->database->username,
        "password" => $config->database->password,
        "dbname" => $config->database->name,
        "charset" => $config->database->charset,
        "collation" => $config->database->collation,
    ));

    $connection->setEventsManager($eventsManager);
    return $connection;
});

$di->set('modelsRepository', function() use ($config, $di) {
    return new \Model\ModelsRepository($di);
});

$di->set('fastCache', function() use ($config) {

    $frontCache = new Phalcon\Cache\Frontend\Data(array(
        "lifetime" => $config->caches->fast->options->lifetime
    ));

    $cache = new $config->caches->fast->adapter($frontCache,
        (array)$config->caches->fast->options
    );

    return $cache;
});

$di->set('slowCache', function() use ($config) {

    $frontCache = new Phalcon\Cache\Frontend\Data(array(
        "lifetime" => $config->caches->slow->options->lifetime
    ));

    $cache = new $config->caches->slow->adapter($frontCache,
        (array)$config->caches->slow->options
    );

    return $cache;
});

 //Create a console application
 $console = new ConsoleApp();
 $console->setDI($di);

 /**
 * Process the console arguments
 */
 $arguments = array();
 $params = array();

 foreach($argv as $k => $arg) {
     if($k == 1) {
         $arguments['task'] = $arg;
     } elseif($k == 2) {
         $arguments['action'] = $arg;
     } elseif($k >= 3) {
        $params[] = $arg;
     }
 }
 if(count($params) > 0) {
     $arguments['params'] = $params;
 }

 // define global constants for the current task and action
 define('CURRENT_TASK', (isset($argv[1]) ? $argv[1] : null));
 define('CURRENT_ACTION', (isset($argv[2]) ? $argv[2] : null));

 try {
     // handle incoming arguments
     $console->handle($arguments);
 }
 catch (\Phalcon\Exception $e) {
     echo $e->getMessage();
     exit(255);
 }