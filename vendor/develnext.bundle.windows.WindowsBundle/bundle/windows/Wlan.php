<?php
namespace bundle\windows;


use bundle\windows\WindowsScriptHost as WSH;
use bundle\windows\Windows;
use php\lib\str;
use php\util\Regex;
use bundle\windows\result\wlanInterface;

/**
 * Wireless lan 
 * @packages windows
 */
class Wlan 
{
    /**
     * Получить список интерфейсов 
     * @return array
     */
    public static function getInterfaces() : array {
        $interfaces = [];
        $i = 0;
        
        $cmd = WSH::cmd('netsh wlan show interfaces');
        
        $regexp = Regex::of('\s*([^:]+)\s+:([^\n]+)\n', Regex::MULTILINE)->with($cmd);
        while ($regexp->find()){
            $k = trim($regexp->group(1));
            $v = trim($regexp->group(2));
            if(isset($interfaces[$i][$k]))$i++;
            
            $interfaces[$i][$k] = $v;
        }
        
        foreach($interfaces as $k=>$interface){
            $interfaces[$k] = new wlanInterface($interface);
        }
        
        return $interfaces;
    }
    
    /**
     * Есть ли оборудование для работы с беспроводными сетями
     * @return boolean
     */
    public static function isSupported() : bool {
        return !str::contains(WSH::cmd('netsh wlan show interfaces'), 'is not running');
    }    

    /**
     * Получить используемый беспроводной интерфейс (идёт первый в списке интерфейсов)
     * @return wlanInterface
     */
    public static function getMainInterface() : wlanInterface {
        return self::getInterfaces()[0];
    }
    
}