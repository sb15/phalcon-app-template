<?php

use Sb\Phalcon\Plugins;
use Sb\Phalcon\Helpers;

class Bootstrap
{

    private $applicationPath = null;
    private $config = null;
    private $eventsManager = null;

    /** @var \Phalcon\DI\FactoryDefault  */
    private $di = null;

    private function __construct($applicationPath)
    {
        $this->applicationPath = $applicationPath;

        $this->eventsManager = new Phalcon\Events\Manager();
        $this->di = new \Phalcon\DI\FactoryDefault();

        $this->defineConstants();
        $this->readConfig();

        $this->defineConstantsAfterAutoloader();

        $this->initErrorHandler();

        $this->initDispatcher();
        $this->initRouter();
        $this->initView();
        $this->initViewHelper();
        $this->initLocale();

        $this->initDb();
        $this->initModels();
        $this->initCache();
        $this->initSession();
        $this->initApplicationEvents();

        $this->handle();
    }

	public static function run($applicationPath)
	{
		new self($applicationPath);
	}

    private function readConfig()
    {
        $this->config = include("{$this->applicationPath}/config/config.php");
        $this->di->set('applicationConfig', $this->config->application);
    }

    private function initLocale()
    {
        setlocale(LC_ALL, 'ru_RU.utf-8', 'rus_RUS.utf-8', 'ru_RU.utf8');
    }

    private function defineConstants()
    {
        $host = $_SERVER['HTTP_HOST'];
        $hostParts = explode(".", $host);

        define('UPLOAD_DIR', $this->applicationPath. '/../static/upload');
        define('APPLICATION_PATH', $this->applicationPath);

        define("STATIC_URL", '//static.' . $hostParts[count($hostParts)-2] . '.' . $hostParts[count($hostParts)-1]);

        define("SITE_URL", 'http://' . $host);

        if (getenv('APPLICATION_ENV') == 'development') {
            define('IS_DEVELOPMENT', true);
        } else {
            define('IS_DEVELOPMENT', false);
        }

        $buildFile = APPLICATION_PATH . '/../.git/refs/heads/master';
        define("BUILD_TIMESTAMP", filemtime($buildFile));
        define("BUILD_UNIQUE_ID", substr(file_get_contents($buildFile), 0, 15));

        define("YANDEX_WEBMASTER_API_TOKEN", '');
    }

    private function defineConstantsAfterAutoloader()
    {

    }

    private function initErrorHandler()
    {
        if (IS_DEVELOPMENT) {
            error_reporting(E_ALL);
            ini_set('display_errors' , 1);
        }

        $client = new Raven_Client('');

        if (!IS_DEVELOPMENT) {
            $error_handler = new Raven_ErrorHandler($client);
            $error_handler->registerExceptionHandler();
            $error_handler->registerErrorHandler();
            $error_handler->registerShutdownFunction();
        }

        $this->di->set('sentry', function() use ($client) {
            return $client;
        });
    }

    private function initDispatcher()
    {
        $eventsManager = $this->eventsManager;
        $this->di->set('dispatcher', function() use ($eventsManager) {

            $eventsManager->attach("dispatch:afterExecuteRoute", new Plugins\ViewReturnPlugin());
            $eventsManager->attach("dispatch:afterExecuteRoute", new Plugins\UrlPlugin());
            $eventsManager->attach("dispatch:afterExecuteRoute", new Plugins\SeoPlugin());
            $eventsManager->attach("dispatch:afterExecuteRoute", new Plugins\BreadcrumbPlugin());
            $eventsManager->attach("dispatch:beforeException", new Plugins\ErrorHandlingPlugin());

            $dispatcher = new \Phalcon\Mvc\Dispatcher();
            $dispatcher->setEventsManager($eventsManager);

            return $dispatcher;
        });
    }

    private function initApplicationEvents()
    {

    }

    private function initViewHelper()
    {
        $this->di->set('helper', new Helpers\ViewHelper($this->di));
        $this->di->set(Helpers\SeoHelper::SERVICE_NAME, new Helpers\SeoHelper($this->di));
        $this->di->set(Helpers\BreadcrumbHelper::SERVICE_NAME, new Helpers\BreadcrumbHelper($this->di));
        $this->di->set(Helpers\UrlHelper::SERVICE_NAME, new Helpers\UrlHelper($this->di));
    }

    private function initView()
    {
        $config = $this->config;
        $eventsManager = $this->eventsManager;

        $this->di->setShared('view', function() use ($config, $eventsManager) {

            $view = new \Phalcon\Mvc\View();

            $view->setViewsDir($config->application->viewsDir);
            $view->setEventsManager($eventsManager);

            return $view;
        });
    }

    private function initSession()
    {
        $this->di->setShared('session', function() {
            $session = new \Phalcon\Session\Adapter\Files();
            $session->start();
            return $session;
        });
    }

    private function initDb()
    {
        $config = $this->config;
        $eventsManager = $this->eventsManager;

        $this->di->set('db', function() use ($config, $eventsManager) {

            //Listen all the database events
            /*$eventsManager->attach('db', function($event, $connection) {
                if ($event->getType() == 'beforeQuery') {
                    echo "<pre>" . $connection->getSQLStatement() . "</pre>";
                }
            });*/

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
    }

    private function initModels()
    {
        $config = $this->config;

        $this->di->set('modelsMetadata', function() use ($config) {
            if (isset($config->models->metadata)) {
                $metadataAdapter = $config->models->metadata->adapter;
                return new $metadataAdapter((array)$config->models->metadata);
            } else {
                return new \Phalcon\Mvc\Model\Metadata\Memory();
            }
        });

        $di = $this->di;

        $this->di->set('modelsRepository', function() use ($config, $di) {
            return new \Model\ModelsRepository($di);
        });
    }

    private function initRouter()
    {
        $config = $this->config;
        $this->di->set('router', function() use ($config) {
            return $config->routes;
        });
    }

    private function initCache()
    {
        $config = $this->config;
        $this->di->set('fastCache', function() use ($config) {

            $frontCache = new Phalcon\Cache\Frontend\Data(array(
                "lifetime" => $config->caches->fast->options->lifetime
            ));

            $cache = new $config->caches->fast->adapter($frontCache,
                (array)$config->caches->fast->options
            );

            return $cache;
        });

        $this->di->set('slowCache', function() use ($config) {

            $frontCache = new Phalcon\Cache\Frontend\Data(array(
                "lifetime" => $config->caches->slow->options->lifetime
            ));

            $cache = new $config->caches->slow->adapter($frontCache,
                (array)$config->caches->slow->options
            );

            return $cache;
        });
    }

    private function handle()
    {
        $application = new \Phalcon\Mvc\Application();
        $application->setDI($this->di);
        echo $application->handle()->getContent();
    }

}
