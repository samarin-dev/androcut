<?php
namespace bundle\windows;

use php\util\Regex;
use php\lib\str;

/**
 * Класс позволяет создавать подготовленные запросы (как в PDO).
 * Далее подготовленные запросы будут использоваться в запросах к API Windows
 */
class Prepare
{
    const STRING = 'STRING';
    const STR = 'STRING';
    const INTEGER = 'INTEGER';
    const INT = 'INTEGER';
    const FLOAT = 'FLOAT';
    const BOOLEAN = 'BOOLEAN';
    const BOOL = 'BOOLEAN';
          
    private $source; 
    private $vars = []; // 'key' => 'value'
    private $safeQuery;
    
    /**
     * Обрамлять переменную кавычками
     * @var boolean
     */
    public $addStringQuotes = false;

    /**
     * Режим управления кавычками
     * 0 - ничего не делаем
     * 1 - кавычки экранируются \"
     * 2 - кавычки экранируются ""
     * @var int
     */
    public $quotesPolicy = 0;


    /**
     * Заменить отсутствующие переменные на NULL
     * @var boolean
     */
    public $replaceEmpty = false;

    public function __construct($query){
        $this->source = $query;
    }
    
    /**
     * @param array $bindParams [key => value] || [key => [value, type]]
     */
    public function bindAll($bindParams){
        foreach($bindParams as $key => $value){
            if(is_array($value)){
                $this->bind($key, $value[0], $value[1]);
            } else {
                $this->bind($key, $value);
            }
        }
    }

    public function bind($key, $value, $type = 'STRING'){
        $key = (str::sub($key, 0, 1) == ':') ? str::sub($key, 1) : $key;
        $key = str::lower($key);
        
        switch($type){
            case self::STRING:
                $value = strval($value);                
                $value = str::replace($value, "\\", "\\\\");
                
                switch($this->quotesPolicy){
                    case 1:
                        $value = str::replace($value, "\"", "\\\"");
                    break;

                    case 2:
                        $value = str::replace($value, '"', '""');
                    break;
                }
                
                if($this->addStringQuotes){
                    $value = '"' . $value . '"';
                } 

                $this->vars[$key] = $value;
            break;            
            
            case self::INTEGER:
                $this->vars[$key] = intval($value);
            break;       
            
            case self::FLOAT:
                $this->vars[$key] = floatval($value);
            break;  
            
            case self::BOOLEAN:
                $this->vars[$key] = boolval($value);
            break;
        }
    }
    
    public function getQuery($bindParams = []){
        $this->bindAll($bindParams);
        
        $reg = Regex::of(':([\w\d_]+)', Regex::UNICODE_CASE | Regex::CASE_INSENSITIVE | Regex::MULTILINE)->with($this->source);
        return $reg->replaceWithCallback(function($reg){
            $key = str::lower($reg->group(1));
   
            if(isset($this->vars[$key])){
                return $this->vars[$key];
            }
            
            if($this->replaceEmpty === true){
                return 'NULL';
            }   
            else return $reg->group(0);
        });
    }
    
    public static function Query($query, $params = []){
        return (new self($query))->getQuery($params);
    }
}