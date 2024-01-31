<?php
namespace bundle\windows\result;

use bundle\windows\result\abstractItem;

class registryItem extends abstractItem
{
    /**
     * --RU--
     * Ключ
     * @readonly
     * @var mixed
     */
    public $key;

    /**
     * --RU--
     * Значение
     * @readonly
     * @var mixed
     */
    public $value;

    /**
     * --RU--
     * Тип значения
     * @var string
     * @readonly
     */
    public $type;

    public function __construct($key, $type, $value){
        $this->key = $key;
        $this->value = $value;
        $this->type = $type;        
    }

     /**
     * Type.
     * --RU--
     * Тип
     * @return string
     */
    public function getType(){
        return $this->type;
    }

    /**
     * Key name.
     * --RU--
     * Название ключа.
     * @return string
     */
    public function getKey(){
        return $this->key;
    }

    /**
     * Value.
     * --RU--
     * Значение.
     * @return string
     */
    public function getValue(){
        return $this->value;
    }

    public function __toString(){
        return (string) $this->value;
    }

}