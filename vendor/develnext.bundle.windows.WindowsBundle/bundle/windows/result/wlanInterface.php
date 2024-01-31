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

class wlanInterface 
{
    private $name;
    private $description;
    private $mac;
    
    public function __construct(array $params){
    
        $this->name = $params['Name'] ?? null;
        $this->description = $params['Description'] ?? null;
        $this->mac = $params['Physical address'] ?? UXApplication::getMacAddress();
    }
    
    /**
     * Получить имя интерфейса
     */
    public function getName() : string {
        return $this->name;
    }    
        
    /**
     * Получить описание интерфейса
     */
    public function getDescription() : string {
        return $this->description;
    }    
        
    /**
     * Получить mac-адрес
     */
    public function getMac() : string {
        return $this->mac;
    }     

    /**
     * Получить текущий профиль (обычно совпадает с именем подключённой сети)
     */
    public function getProfile() : string {
        $params = $this->getParams();
        return $params['Profile'] ?? null ;
    }    

    /**
     * Получить пароль текущего профиля
     */
    public function getPassword() : string {
        $temp = Windows::getTemp();

        $profile = WSH::cmd('netsh wlan show profile name=":name" interface=":interface" key=clear', [
            'name' => $this->getProfile(),
            'interface' => $this->name
        ]);
       
        $regexp = Regex::of('\n([^:\n]+)\s+:([^\n]+)', Regex::MULTILINE)->with($profile);
        while ($regexp->find()){
            $k = str::lower(trim($regexp->group(1)));
            $v = trim($regexp->group(2));   
            
            // win7 fix: между словами key и content какой-то знак, не являющийся пробелом, поэтому определяем ключ таким образом
            if(str::contains($k, 'key') && str::contains($k, 'content')) return $v;
        }
        return false;
    }   
    
    /**
     * Перезагрузить интерфейс (нужны права администратора)
     */
    public function reload(){
        $this->disable();
        $this->enable();
    }
        
    /**
     * Отключить интерфейс (нужны права администратора)
     */
    public function disable(){
        WSH::cmd('netsh interface set interface name=":interface" admin=disabled', ['interface' => $this->name]);
    }    
        
    /**
     * Включить интерфейс (нужны права администратора)
     */
    public function enable(){
        WSH::cmd('netsh interface set interface name=":interface" admin=enabled', ['interface' => $this->name]);
    }   
    
    /**
     * Отключиться от сети
     */
    public function disconnect(){
        WSH::cmd('netsh wlan disconnect interface=":interface"', ['interface' => $this->name]);
    }
    
    /**
     * Переподключиться к текущей сети
     */
    public function reconnect(){
        $current = $this->getProfile();
        $this->disconnect();

        if(!is_null($current)){
            $this->connect($current, false);
        }
    }

    /**
     * Подключиться к сети
     * @param  string  $ssid SSID сети
     * @param  mixed $password Пароль длиной минимум 8 символов, или NULL, если пароль не нужен, или false, чтоб использовать сохранённые в системе настройки
     * @throws WindowsException
     */
    public function connect($ssid, $password = false) : bool {
        if($password !== false){
            // Сначала удалим профиль, если он существует
            WSH::cmd('netsh wlan delete profile name=":ssid" interface=":interface"', ['interface' => $this->name, 'ssid' => $ssid]);
            
            // Создаём файл профиля с авторизационными данными
            $file = $this->createConfig($ssid, $password);
            $profile = WSH::cmd('netsh wlan add profile filename=":file"', ['file' => $file]);
            unlink($file);

            if(str::contains($profile, 'error')){
                throw new WindowsException($profile);
            }
        }

        // Подключаемся к сети  используя файл профиля
        $connect = WSH::cmd('netsh wlan connect name=":ssid" interface=":interface"', ['interface' => $this->name, 'ssid' => $ssid]);
        if(str::contains($connect, 'Reason:')){
            throw new WindowsException(explode('Reason: ', $connect)[1]);
        }
                
        if(str::contains($connect, 'successfully')){
            $status = null;
            while(!str::contains($status, 'connected')){
                $status = $this->getState();
                usleep(100);
            }
            
            return $status == 'connected';
        } else {
            return false;
        }
    }
    
    /**
     * Получить состояние подключения сети
     * @return string disconnected, authenticating, connected, connecting
     */
    public function getState() : string {
        $params = $this->getParams();
        return $params['State'] ?? 'disconnected';
    }

    /**
     * Получить список параметров текущего интерфейса
     */
    public function getParams() : array {
        $params = [];
        $data = WSH::cmd('netsh wlan show interface name=":interface"', ['interface' => $this->name]);
        $regexp = Regex::of('\s*([^:]+)\s+:([^\n]+)\n', Regex::MULTILINE)->with($data);
        while ($regexp->find()){
            $k = trim($regexp->group(1));
            $v = trim($regexp->group(2));            
            $params[$k] = $v;
        }

        return $params;
    }
    
    /**
     * Получить список обнаруженных Wi-Fi сетей
     * @return array
     */
    public function getNetworks() : array {
        $networks = [];
        $i = -1;
        $cmd = WSH::cmd('netsh wlan show networks interface=":interface" mode=bssid', ['interface' => $this->name]);
        $cmd = explode('currently visible.', $cmd)[1];
 
        $regex = Regex::of('([^:]+):([^\n]+)\n', Regex::MULTILINE)->with($cmd);
        while ($regex->find()){
            $k = trim($regex->group(1));
            $v = trim($regex->group(2));
            
            if(str::startsWith($k, 'SSID')){
                $k = 'SSID'; 
                $i++;
            }
            elseif(str::startsWith($k, 'BSSID')) $k = 'BSSID';
            elseif($k == 'Signal') $v = intval(str::replace($v, '%', ''));
            
            $networks[$i][$k] = $v;
        }
        
        return $networks;
    }
    
    /**
     * Генерация файла профиля (для авторизации в сети WiFi)
     * @param  string  $ssid     
     * @param  string $password=null
     * @return string Путь к файлу
     * @todo Авторизация с WEP
     */
    protected function createConfig(string $ssid, string $password = null) : string {
        $xml = '<?xml version="1.0"?>
                    <WLANProfile xmlns="http://www.microsoft.com/networking/WLAN/profile/v1">
                        <name>'. $ssid .'</name>
                        <SSIDConfig>
                            <SSID>
                                <name>'. $ssid .'</name>
                            </SSID>
                        </SSIDConfig>
                        <connectionType>ESS</connectionType>
                        <connectionMode>manual</connectionMode>
                        <MSM>
                            <security>'. (is_null($password) ? 
                                '<authEncryption>
                                    <authentication>open</authentication>
                    <encryption>none</encryption>
                    <useOneX>false</useOneX>
                                </authEncryption>' :
                               '<authEncryption>
                                    <authentication>WPA2PSK</authentication>
                                    <encryption>AES</encryption>
                                    <useOneX>false</useOneX>
                                </authEncryption>
                                <sharedKey>
                                    <keyType>passPhrase</keyType>
                                    <protected>false</protected>
                                    <keyMaterial>' . $password . '</keyMaterial>
                                </sharedKey>
                            ').
                            '</security>
                        </MSM>
                        <MacRandomization xmlns="http://www.microsoft.com/networking/WLAN/profile/v3">
                            <enableRandomization>false</enableRandomization>
                        </MacRandomization>
                    </WLANProfile>';
        $tmpFile = Windows::expandEnv('%TEMP%/wlan_connect_' . str::uuid() . '.xml');
        file_put_contents($tmpFile, $xml);
        return $tmpFile;
    }
}