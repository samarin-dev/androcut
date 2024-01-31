<?php
namespace bundle\windows\result;

use bundle\windows\result\abstractResult;
use bundle\windows\result\registryItem;
use bundle\windows\Registry;
use Iterator;

class registryResult extends abstractResult{
    /**
     * @readonly
     * @var string
     */
    public $path;

    public function __construct($path){
        $this->path = $path;
    }

    public function addData($key, $type, $value){
        $this->data[] = new registryItem($key, $type, $value);
    }
 
    public function toArray(){
        return [$this->path => $this->data];
    }

    /**
     * Get path
     * --RU--
     * Получить путь
     * @return string
     */
    public function getPath(){
        return $this->path;
    }

    /**
     * Вернуть класс Registry для текущего пути
     * @return Registry
     */
    public function registry(){
        return new Registry($this->path);
    }
}