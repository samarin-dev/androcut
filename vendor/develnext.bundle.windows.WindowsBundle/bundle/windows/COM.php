<?php
namespace bundle\windows;

use bundle\windows\result\comItem;
use bundle\windows\WindowsScriptHost as WSH;
use php\util\Regex;

/**
 * Класс для работы с COM-портами
 * @packages windows
 */
class COM 
{  
    /**
     * --RU--
     * Получить список портов
     * @return comItem[]
     */
    public static function getList(){
        $com = [];
        $reg = Registry::of('HKEY_LOCAL_MACHINE\Hardware\DeviceMap\SerialComm')->readFully();
        foreach($reg as $r){
            foreach($r as $v){
                $com[$v->value] = new comItem($v->value, array_merge(self::getParams($v->value), ['path' => $v->key]));
            }
        }

        return $com; 
    }

    /**
     * --RU--
     * Получить список параметров порта
     * @param string $port Имя порта (от COM1 до COM255)
     * @return array
     */
    public static function getParams($port){
        $r = WSH::WMIC('path Win32_PnPEntity WHERE "Caption like \'%' . $port . '%\'" get');
        return isset($r[0]) ? $r[0] : [];
    }

    /**
     * --RU--
     * Ищет устройство по имени
     * @param string $search Строка поиска
     * @param array $searchFields=['Caption','Description','Name','Service'] Поля, по ктоторым осуществляется поиск
     * @return comItem[]
     */
    public static function searchDevice($search, $searchFields = ['Caption', 'Description', 'Name', 'Service']){
        $searchCOM = ['Caption', 'Name']; // Поля, в которых фигурирует номер COM порта

        // Формирование SQL запроса
        $searchQuery = [];
        foreach($searchFields as $field){
            $searchQuery[] = $field . ' like \'%' . $search . '%\'';
        }
        $search = implode(' OR ', $searchQuery);
        $r = WSH::WMIC('path Win32_PnPEntity WHERE "'.$search.'" get');

        $ports = [];
        foreach ($r as $v) {
            $string = ''; // Делаем конкатенацию полей, где может фигурировать номер COM порта
            foreach ($searchCOM as $field){
                $string .= $v[$field];
            }
            $regex = Regex::of('(COM[0-9]+)', Regex::CASE_INSENSITIVE + Regex::UNICODE_CASE)->with($string);
            if ($regex->find()){ // Если удалось определить номер com порта
                $ports[$regex->group(1)] = new comItem($regex->group(1), $v);
            }
        }

        return $ports;
    }
}