<?php

$router = new \Phalcon\Mvc\Router(false);

$router->add("/",
    array(
        "controller" => "index",
        "action"     => "index"
    ))->setName('main');

$router->notFound(array(
    "controller" => "index",
    "action" => "error"
));

return $router;