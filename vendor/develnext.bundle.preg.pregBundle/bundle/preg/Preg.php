<?php
namespace bundle\preg;

use php\framework\Logger;
use php\util\Regex;
use php\lib\str;

class Preg
{
    protected $reg, $pattern, $subject, $flags;
    public static function of($pattern = null, $subject = null){
        return new self($pattern, $subject);
    }
    
    public function __construct($pattern = null, $subject = null){
        if(!is_null($pattern)) $this->setPattern($pattern);
        if(!is_null($subject)) $this->setSubject($subject);
    }
    
    public function setSubject($subject){
        $this->subject = (string) $subject;
        $this->log('subject = ', $subject);
    }
    
    public function getPattern(){
        return $this->pattern;
    }

    public function setPattern($pattern){
        $delim = substr($pattern, 0, 1);
        $endReg = str::lastPos($pattern, $delim);
        $modifs = str_split(substr($pattern, $endReg+1));
        
        $this->pattern = substr($pattern, 1, $endReg-1);
        $this->parseModifiers($modifs);
        
        if(substr($this->pattern, 0, 1) == '^')$this->pattern = substr($this->pattern, 1);
        if(substr($this->pattern, -1) == '$')$this->pattern = substr($this->pattern, 0, -1);
       
        $this->log('pattern = '. $this->pattern);
        $this->log('modifs = ',$modifs);
        $this->log('flags = '. $this->flags);
        
        return $this;
    }
    
    public function setRegClass($reg){
        $this->reg = $reg;
    }
    
    public function compile(){
       $this->reg = Regex::of($this->pattern, $this->flags)->with($this->subject);
    }
    
    public function find(){
       return $this->reg->find();
    }
    
    public function replace($replacement){
       return $this->reg->replace($replacement);
    }    

    public function replaceCallback($callback){
       return $this->reg->replaceWithCallback(function($reg) use ($callback){
           $p = new self;
           $p->setRegClass($reg);
           return call_user_func_array($callback, [$p->matches()]);
       });
    }
    
    protected function parseModifiers($modifiers){
        $this->flags = 0;
        $mods = [
            'i' => Regex::CASE_INSENSITIVE,
            'm' => Regex::MULTILINE,
            's' => Regex::DOTALL,
            'u' => Regex::UNICODE_CASE
        ];
        
        foreach($modifiers as $m){
            if(isset($mods[$m])){
                $this->flags |= $mods[$m];
            }
        }
    }
    
    public function count(){
        return $this->reg->getGroupCount() + 1;
    }

    public function matches(){
        $return = [];
        for($i = 0; $i < $this->count(); ++$i){
            $return[] = $this->reg->group($i);
        }
        
        return $return;
    }

    const LOG = false;
    private function log(...$args){
        if(self::LOG !== true) return;
        $message = '';
        //$args = func_get_args();
        foreach($args as $arg){
            if(is_string($arg)){
                $message.=$arg;
            } else {
                $message.=var_export($arg, true);
            }
        }

        $lines = explode("\n", trim($message));
        foreach ($lines as $line){
            Logger::Debug('[Preg] ' . $line);
        }
    }
}