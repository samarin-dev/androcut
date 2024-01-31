<?php
namespace bundle\windows\result;

use bundle\windows\WindowsException;
use bundle\windows\Windows;
use php\io\File;
use php\lib\fs;
use php\lib\str;
use php\util\Regex;
use bundle\windows\WindowsScriptHost as WSH;
use php\gui\UXApplication;

class lanAdapter 
{
    private $name;
    private $device;
    private $params;
    private $ipv4;
    private $ipv6;
    private $mac;
    
    public function __construct(string $name, array $params){
        $this->name = $name;
        $this->params = $params;

        $this->device = $params['Description'] ?? null;
        $this->ipv4 = isset($params['IPv4 Address']) ? str_replace(['%5','(Preferred)'], '', $params['IPv4 Address']) : null;
        $this->ipv6 = explode('%', str_replace(['(Preferred)'], '', ($params['IPv6 Address'] ?? $params['Link-local IPv6 Address'] ?? null)))[0];

        $this->mac = str_replace('-', ':', $params['Physical Address'] ?? null);
    }
    
    /**
     * Получить имя адаптера
     */
    public function getName() : string {
        return $this->name;
    }    

    /**
     * Получить параметры адаптера
     */
    public function getParams() : array {
        return $this->params;
    }    
        
    /**
     * Получить описание устройства
     */
    public function getDevice() : string {
        return $this->device;
    }    
        
    /**
     * Получить mac адрес
     */
    public function getMac() : string {
        return $this->mac;
    }      

    /**
     * Получить IPv4 адрес
     */
    public function getIPv4() : string {
        return $this->ipv4;
    }  

    /**
     * Получить IPv6 адрес
     */
    public function getIPv6() : string {
        return $this->ipv6;
    }     

    /**
     * Доступна ли сеть на данном адаптере
     */
    public function isNetworkEnabled() : bool {
        return $this->params['NetEnabled'] ?? false;
    }  

    /**
     * Подключен ли сетевой кабель
     */
    public function isConnected() : bool {
        return str::contains(WSH::cmd('netsh interface show interface name=":adapter"', ['adapter' => $this->name]), 'Connected');
    } 

    /**
     * Включен ли адаптер
     */
    public function isEnabled() : bool {
        return str::contains(WSH::cmd('netsh interface show interface name=":adapter"', ['adapter' => $this->name]), 'Enabled');
    }     

/******************/
    /**
     * Отключить адаптер (нужны права администратора)
     */
    public function disable() : bool {
        return strlen(WSH::cmd('netsh interface set interface name=":adapter" admin=disabled', ['adapter' => $this->name])) == 0;
    }    
        
    /**
     * Включить интерфейс (нужны права администратора)
     */
    public function enable() : bool {
        return strlen(WSH::cmd('netsh interface set interface name=":adapter" admin=enabled', ['adapter' => $this->name])) == 0;
    }   
}