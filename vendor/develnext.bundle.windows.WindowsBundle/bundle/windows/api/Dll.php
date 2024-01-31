<?php
namespace bundle\windows\api;

use bundle\windows\WindowsScriptHost as WSH;
use bundle\windows\api\CSharp;

/**
 * @packages windows
 */
class Dll 
{  
    protected $libName;

    public function __construct($libName){
        $this->libName = $libName;
    }

    public function createMethod($methodName, $argsString, $source){

    }

    public function __call(string $method, array $args = []){
        $arguments = $args[0] ?? [];
        $returnType = $args[1] ?? 'dynamic';
        $arguments = !is_array($arguments) ? [$arguments] : $arguments ;
        $argString = [];
        
        foreach ($arguments as $i => $arg) {
            $type = gettype($arg);
            $type = ($type == 'integer') ? 'int' : $type ;
            $argString[] = $type . ' arg' . $i;
        }

        $argString = implode(", ", $argString);
        $class = $this->genClassCode($method, $argString, $returnType);
        $cs = new CSharp($class);
        return $cs->call('DNSharp', $method, $arguments);
    }

    protected function genClassCode($method, $argString, $returnType = 'dynamic'){
        return '            
            using System;
            using System.Runtime.InteropServices;
            using System.Text;
            public class DNSharp {
                [DllImport("'. $this->libName . '",CharSet=CharSet.Unicode)]
                public static extern '. $returnType . ' '. $method . '(' . $argString . ');
            }';
    }
}