<?php
namespace bundle\windows;

use bundle\windows\result\startupItem;
use php\lib\arr;
use php\lib\str;
use php\util\Regex;
use bundle\windows\WindowsScriptHost as WSH;

/**
 * Класс содержит функции для работы с автозапуском
 * @packages windows
 */
class Startup 
{    
    /**
     * --RU--
     * Получить список программ, находящихся в автозагрузке
     * @return startupItem[]
     */
    public static function getList() : array{
        $wmic = self::loadWMIC();
        $registry = self::loadRegistry();
        $items = array_merge($wmic, $registry);
        $return = [];

        foreach($items as $k=>$item){
            $key = strtolower(implode('-', $item)); // Чтоб убрать повторяющиеся элементы
            if(isset($return[$key])) continue;
            
            $return[$key] = new startupItem($item['title'], $item['command'], $item['location']);
        }

        return array_values($return);
    }

    /**
     * Загрузка элементов из WMI
     */
    private static function loadWMIC() : array{
        $list = WSH::WMIC('startup get');
        $startup = [];

        foreach($list as $v){
            $startup[] = ['title' => $v['Caption'], 'command' => $v['Command'], 'location' => self::expandRegPath($v['Location'])];
        }

        return $startup;
    }

    /**
     * Загрузка элементов из реестра
     */
    public static function loadRegistry() : array{
        $regPaths = [
            'HKEY_LOCAL_MACHINE\Software\Microsoft\Windows\CurrentVersion\Run',
            'HKEY_LOCAL_MACHINE\Software\Microsoft\Windows\CurrentVersion\RunOnce',
            'HKEY_CURRENT_USER\Software\Microsoft\Windows\CurrentVersion\Run',
            'HKEY_CURRENT_USER\Software\Microsoft\Windows\CurrentVersion\RunOnce',

            // If added by Group Policy
            'HKEY_CURRENT_USER\Software\Microsoft\Windows\CurrentVersion\Policies\Explorer\Run',
            'HKEY_LOCAL_MACHINE\Software\Microsoft\Windows\CurrentVersion\Policies\Explorer\Run',

            //x64
            'HKEY_LOCAL_MACHINE\Software\Wow6432Node\Microsoft\Windows\CurrentVersion\Run',
            'HKEY_LOCAL_MACHINE\Software\Wow6432Node\Microsoft\Windows\CurrentVersion\RunOnce',
        ];

        $startup = [];

        foreach($regPaths as $path){
            try{
                $reg = Registry::of($path)->readFully(true);
                foreach($reg as $r){
                    foreach($r as $v){
                        $startup[] = ['title' => $v->key, 'command' => $v->value, 'location' => $r->path];
                    }
                }

            } catch(WindowsException $e){
            }
        }

        return $startup;
    }

    /**
     * @todo Загрузка отключенных в реестре
     */
    public static function loadDisabled() : array {
        $regPaths = [
            'HKEY_LOCAL_MACHINE\Software\Microsoft\Windows\CurrentVersion\Explorer\StartupApproved\Run',
            'HKEY_LOCAL_MACHINE\Software\Microsoft\Windows\CurrentVersion\Explorer\StartupApproved\Run32',
            'HKEY_LOCAL_MACHINE\Software\Microsoft\Windows\CurrentVersion\Explorer\StartupApproved\StartupFolder',
            'HKEY_CURRENT_USER\Software\Microsoft\Windows\CurrentVersion\Explorer\StartupApproved\Run',
            'HKEY_CURRENT_USER\Software\Microsoft\Windows\CurrentVersion\Explorer\StartupApproved\Run32',
            'HKEY_CURRENT_USER\Software\Microsoft\Windows\CurrentVersion\Explorer\StartupApproved\StartupFolder',
        ];


        $startup = [];
       
        foreach($regPaths as $path){
            try{
                $reg = Registry::of($path)->readFully();
                foreach($reg as $r){
                    foreach($r as $v){
                        $startup[$r->path][] = [$v->key => $v->value];
                    }
                }

            } catch(WindowsException $e){
            }
        }

        return $startup;
    }

    private static function expandRegPath($path) : string {
        $reg = [
            'HKCR\\' => 'HKEY_CLASSES_ROOT\\',
            'HKCU\\' => 'HKEY_CURRENT_USER\\',
            'HKLM\\' => 'HKEY_LOCAL_MACHINE\\',
            'HKU\\' => 'HKEY_USERS\\',
            'HKCC\\' => 'HKEY_CURRENT_CONFIG\\',
        ];

        return str_replace(array_keys($reg), array_values($reg), $path);
    }


    /**
     * --RU--
     * Добавляет программу в автозагрузку
     * @param string $file Команда для запуска
     * @param string $description=null Описание
     * @return startupItem
     */
    public static function add($file, $description = null) : startupItem {
        $dir = self::getUserStartupDirectory();
        $basename = basename($file);
        Windows::createShortcut($dir . '\\' . $basename . '.lnk', $file, $description);
        return self::find($file);
    }

    /**
     * --RU--
     * Найти запись в автозапуске по исполняемому файлу
     * @param string $file Путь к исполняемому файлу
     * @return startupItem
     */
    public static function find($file) : startupItem {
        $list = self::getList();
        foreach($list as $item){
            if($item->file == $file){
                return $item;
            }
        }

        return false;
    }

    /**
     * --RU--
     * Находится ли данный файл в автозапуске
     * @param string $file Путь к исполняемому файлу
     * @return bool
     */
    public static function isExists($file) : bool {
        return self::find($file) !== false;
    }

    /**
     * --RU--
     * Возвращает путь к пользовательской папке автозагрузки
     * @return string
     */
    public static function getUserStartupDirectory() : string {
        return realpath(Windows::expandEnv('%APPDATA%\Microsoft\Windows\Start Menu\Programs\Startup'));
    }
    
    /**
     * --RU--
     * Возвращает путь к папке автозагрузки для программ
     * @return string
     */
    public static function getCommonStartupDirectory() : string {
        return realpath(Windows::expandEnv('%PROGRAMDATA%\Microsoft\Windows\Start Menu\Programs\Startup'));
    }
    
}