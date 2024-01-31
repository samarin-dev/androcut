<?php
namespace bundle\windows\api;

use bundle\windows\WindowsScriptHost as WSH;

/**
 * Класс для выполнения C# кода
 * @packages windows
 */
class CSharp 
{  
    /**
     * Исходный код C#
     * @var string
     */
    protected $source;

    public function __construct($source){
        $this->source = $source;
    }

    /**
     * Вызов метода
     * @param  string $class  
     * @param  string $method 
     * @param  array  $args   
     * @return string
     */
    public function call(string $class, string $method, array $args = []) : string {
        $argString = [];
        foreach ($args as $arg) {
            $argString[] = var_export($arg, true);
        }
        $argString = implode(", ", $argString);

        // Если использовать CSharpVersion3, там нет поддержки dynamic, CSharp по умолчанию использует последнюю доступную версию
        $ps = "Add-Type -Language CSharp -TypeDefinition @\"" . $this->source . "\n\"@\n" . 
              "[$class]::$method($argString)";

        return WSH::PowerShell($ps);
    }
}