<?php
namespace bundle\windows;


use bundle\windows\WindowsScriptHost as WSH;
use bundle\windows\Windows;
use php\lib\str;
use php\util\Regex;
use bundle\windows\result\lanAdapter;
use php\util\Scanner;

/**
 * Local Area Network 
 * @packages windows
 */
class Lan 
{
    /**
     * Получить список адаптеров 
     * @return array
     */
    public static function getAdapters() : array {
        $adapters = [];
        $adaptersObj = [];
        $adapterName = false;

        // ipconfig возвращает в 866, независимо от указанной кодировки
        $cmd = WSH::cmd('ipconfig /all', [], 'utf-8', 'cp866');  
        $nic = WSH::WMIC('nic get');

        $scanner = (new Scanner($cmd))->useDelimiter(Regex::of('\n', Regex::MULTILINE));
        while($scanner->hasNextLine()){
            $scanner->next();
            $line = $scanner->current();
            
            $regAdapter = Regex::of('Ethernet adapter ([^\n\r]+):', Regex::MULTILINE)->with($line);
            if($regAdapter->find()){
                $adapterName = $regAdapter->group(1);
            } elseif($adapterName != false){
                $regParam = Regex::of('\s+([^\.]+)[\.\s]+:([^\n]+)', Regex::MULTILINE)->with($line);
                if($regParam->find()){
                    $key = trim($regParam->group(1));
                    if(isset($adapters[$adapterName][$key])) $adapterName = false;
                    else $adapters[$adapterName][$key] = trim($regParam->group(2));
                }
            }
        }

        foreach($nic as $k => $v){
            if(!isset($v['NetConnectionID'])) continue;
            if(!isset($adapters[$v['NetConnectionID']])){
                $adapters[$v['NetConnectionID']] = [
                    'Description' => $v['Description'],
                    'Physical Address' => $v['MACAddress'],
                ];
            }

            $adapters[$v['NetConnectionID']]['Speed'] = $v['Speed'] ?? 0;
            $adapters[$v['NetConnectionID']]['Manufacturer'] = $v['Manufacturer'] ?? '';
            $adapters[$v['NetConnectionID']]['Manufacturer'] = $v['Manufacturer'] ?? '';
            $adapters[$v['NetConnectionID']]['NetEnabled'] = isset($v['NetEnabled']) && $v['NetEnabled'] == 'TRUE';
        }
        
        foreach ($adapters as $name => $params) {
            $adaptersObj[] = new lanAdapter($name, $params);
        }
        return $adaptersObj;
    }
    
    /**
     * Получить используемый по умолчанию адаптер
     * @return lanAdapter
     */
    public static function getActiveAdapter() : lanAdapter {
        $adapters = self::getAdapters();
        foreach ($adapters as $adapter) {
            if($adapter->isActive()){
                return $adapter;
            }
        }

        return null;
    }   

    /**
     * Есть ли оборудование для работы с проводными сетями
     * @return boolean
     */
    public static function isSupported() : bool {
        return sizeof(self::getAdapters()) > 0;
    }
    
}