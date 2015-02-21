<?php

namespace Model;

class ModelsRepository
{
    public $models = array();

    public $di;

    public function __construct($di)
    {
        $this->di = $di;
    }

    /**
     * @return \Phalcon\DiInterface
     */
    public function getDI()
    {
        return $this->di;
    }

    private function getModel($modelName)
    {
        if (!array_key_exists($modelName, $this->models)) {
            $namespace = '\\Model\\'.$modelName;
            $newModel = new $namespace;
            $newModel->setDI($this->di);
            $this->models[$modelName] = $newModel;
        }
        return $this->models[$modelName];
    }

}