<?php
namespace bundle\windows\result;

use php\lib\str;
use php\lang\Process;

class wshResult
{
    /**
     * @var Process
     */
    private $process;
    private $charset = null;
    
    public function __construct(Process $process){
        $this->process = $process;
    }
    
    public function setCharset($charset){
        $this->charset = $charset;
        return $this;
    }
    
    public function getOutput(){
        return $this->decodeStream($this->process->getInput());
    }    

    public function getError(){
        return $this->decodeStream($this->process->getError());
    }
    
    private function decodeStream($stream){
        $data = $stream->readFully();
        if($this->charset != null){
            $data = str::decode($data, $this->charset);
        }
        
        return str::trim($data);
    }
}