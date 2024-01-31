<?php
namespace bundle\windows\result;

abstract class abstractItem
{
    /**
     * @return array
     */
    public function toArray(){
        $array = [];
        foreach(array_keys(get_class_vars($this)) as $key){
            $array[$key] = $this->{$key};
        }
        return $array;
    }
}