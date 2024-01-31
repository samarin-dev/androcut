<?php
namespace bundle\windows\result;

use Iterator;

abstract class abstractResult implements Iterator{

    protected $data = [];

    /**
     * @param mixed $key
     * @param mixed $value
     */
    public function add($key, $value){
        $this->data[$key] = $value;
    }

    public function first(){
        return array_values($this->data)[0];
    }

    public function rewind(){
        reset($this->data);
        $this->next();
    }
  
    public function length(){
        return sizeof($this->data);
    }

    public function current(){
        return current($this->data);
    }
  
    public function key(){
        return key($this->data);
    }
  
    public function next(){
        return next($this->data);
    }
  
    public function valid(){
        $key = key($this->data);
        return ($key !== NULL && $key !== FALSE);
    }

    public function toArray(){
        return $this->data;
    }
}