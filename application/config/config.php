<?php

if (IS_DEVELOPMENT) {

    return new \Phalcon\Config(array(
        'database' => array(
            'adapter'  => 'Pdo_Mysql',
            'host'     => '',
            'username' => '',
            'password' => '',
            'name'     => '',
            'charset'  => "utf8",
            'collation' => "utf8_unicode_ci"
        ),
        'application' => array(
            'controllersDir' => APPLICATION_PATH . '/controllers/',
            'modelsDir'      => APPLICATION_PATH . '/entities/',
            'viewsDir'       => APPLICATION_PATH . '/views/',
            'helpersDir'       => APPLICATION_PATH . '/helpers/',
            'pluginsDir'     => APPLICATION_PATH . '/plugins/',
            'libraryDir'     => APPLICATION_PATH . '/library/',
            'baseUri'        => '/',
        ),
        'models' => array(
            'metadata' => array(
                'adapter' => '\Phalcon\Mvc\Model\Metadata\Memory'
            )
        ),
        'caches' => array(
            'fast' => array(
                'adapter' => 'Phalcon\Cache\Backend\File',
                'options' => array(
                    'cacheDir' => APPLICATION_PATH . '/../data/cache/',
                    "lifetime" => 3600
                )
            ),
            'slow' => array(
                'adapter' => 'Phalcon\Cache\Backend\File',
                'options' => array(
                    'cacheDir' => APPLICATION_PATH . '/../data/cache/',
                    "lifetime" => 3600
                )
            )
        ),
        'routes' => include('routes.php')
    ));
} else {

    return new \Phalcon\Config(array(
        'database' => array(
            'adapter'  => 'Pdo_Mysql',
            'host'     => '',
            'username' => '',
            'password' => '',
            'name'     => '',
            'charset'  => "utf8",
            'collation' => "utf8_unicode_ci"
        ),
        'application' => array(
            'controllersDir' => APPLICATION_PATH . '/controllers/',
            'modelsDir'      => APPLICATION_PATH . '/entities/',
            'viewsDir'       => APPLICATION_PATH . '/views/',
            'helpersDir'     => APPLICATION_PATH . '/helpers/',
            'pluginsDir'     => APPLICATION_PATH . '/plugins/',
            'libraryDir'     => APPLICATION_PATH . '/library/',
            'baseUri'        => '/',
        ),
        'models' => array(
            'metadata' => array(
                'adapter' => '\Sb\Phalcon\Mvc\Model\MetaData\Memcache',
                'lifetime' => 86400,
                'prefix'  => BUILD_UNIQUE_ID,
                'host' => 'localhost',
                'port' => 11211
            )
        ),
        'caches' => array(
            'fast' => array(
                'adapter' => 'Phalcon\Cache\Backend\Memcache',
                'options' => array(
                    "host" => "localhost",
                    "port" => "11211",
                    "lifetime" => 3600
                )
            ),
            'slow' => array(
                'adapter' => 'Phalcon\Cache\Backend\File',
                'options' => array(
                    'cacheDir' => APPLICATION_PATH . '/../data/cache/',
                    "lifetime" => 3600
                )
            )
        ),
        'routes' => include('routes.php')
    ));
}


