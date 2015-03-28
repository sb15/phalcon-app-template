<?php

class ErrorController extends BaseController
{
    public function notFoundAction()
    {
        $this->response->setStatusCode(404, 'Not Found');
        $this->view->pick('error/not-found');
    }

    public function uncaughtExceptionAction()
    {
        $this->response->setStatusCode(500, 'Internal Server Error');
        $this->view->pick('error/uncaught-exception');
    }
}