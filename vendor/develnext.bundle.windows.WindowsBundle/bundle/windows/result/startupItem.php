<?php
namespace bundle\windows\result;

use bundle\windows\result\abstractItem;
use bundle\windows\Startup;
use bundle\windows\Windows;
use bundle\windows\WindowsException;
use bundle\windows\Registry;
use php\lib\str;
use php\lib\fs;
use php\util\Regex;

class startupItem extends abstractItem
{
    /**
     * --RU--
     * Заголовок 
     * @readonly
     * @var string
     */
    public $title;

    /**
     * --RU--
     * Команда для запуска 
     * @readonly
     * @var string
     */
    public $command;

    /**
     * --RU--
     * Путь к файлу
     * @readonly
     * @var string
     */
    public $file;

    /**
     * --RU--
     * Путь к ярлыку
     * @readonly
     * @var string
     */
    public $shortcut;

    /**
     * --RU--
     * Для всех пользователей
     * @readonly
     * @var bool
     */
    public $forAllUsers;

    /**
     * --RU--
     * Расположение записи (Реестр, папка startup и т.д.)
     * @readonly
     * @var string
     */
    public $location;

    public function __construct($title, $command, $location){
        $this->title = $title;
        $this->location = $location;
        $this->command = $command;
        
        $file = $this->getFileFromCommand($command);
        $this->file = realpath(Windows::expandEnv($file));

        if($location == 'Startup'){
            $this->shortcut = Startup::getUserStartupDirectory() . '\\' . $this->command;
            $this->file = Windows::getShortcutTarget($this->shortcut);
        } elseif($location == 'Common Startup'){
            $this->shortcut = Startup::getCommonStartupDirectory() . '\\' . $this->title . '.lnk';
        } elseif(str::startsWith($location, 'HK')){
            $this->shortcut = $location;
            $this->location = 'Registry';
        }

        $this->forAllUsers = $this->isForAllUsers();
    }

    /**
     * Автозагрузка для всех пользователей
     * @return bool
     */
    public function isForAllUsers(){
        return $this->location == 'Common Startup' ||
               ($this->location == 'Registry' && str::startsWith($this->shortcut, 'HKEY_LOCAL_MACHINE'));

    }    

    private function getFileFromCommand($command){
        $file = null;
        if(str::contains($command, '"') and str::sub($command, 0, 1) == '"'){
            $file = str::sub($command, 1, str::pos($command, '"', 1));
        } elseif($reg = Regex::of("\\\\([^\\s\\\\]+\\.[^\\s\\\\]+)(\\s[\\S]+)+", Regex::CASE_INSENSITIVE)->with($command) and $reg->find()){
            $file = $reg->group(1);
        } else {
            $file = $command;
        }

        return $file;
    }

    /** 
     * --RU--
     * Удалить объект из автозагрузки
     * @return bool
     */
    public function delete(){
        if($this->location == 'Registry'){
            try{
                Registry::of($this->shortcut)->deleteKey($this->title);
                return true;
            } catch (WindowsException $e){
                return false;
            }
        } else {
            return fs::delete($this->shortcut);
        }
    }

    /**
     * --RU--
     * Заголовок
     * @return string
     */
    public function getTitle(){
        return $this->title;
    }

    /**
     * --RU--
     * Команда для запуска
     * @return string
     */
    public function getCommand(){
        return $this->command;
    }

    /**
     * --RU--
     * Путь к исполняемому файлу
     * @return string
     */
    public function getFile(){
        return $this->file;
    }
    
    /**
     * --RU--
     * Путь к ярлыку для запуска
     * @return string
     */
    public function getShortcut(){
        return $this->shortcut;
    }

    /**
     * --RU--
     * Расположение записи для запуска
     * @return string
     */
    public function getLocation(){
        return $this->location;
    }
}