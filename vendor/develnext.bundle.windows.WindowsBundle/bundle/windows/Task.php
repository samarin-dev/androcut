<?php
namespace bundle\windows;

use bundle\windows\result\taskItem;
use bundle\windows\result\taskResult;
use php\lib\arr;
use php\lib\str;
use php\util\Regex;
use bundle\windows\WindowsScriptHost as WSH;

/**
 * @packages windows
 */
class Task 
{  
    /**
     * --RU--
     * Получить список процессов
     * @return \result\taskResult
     */
    public static function getList(){
        return self::exec(false);
    }
    
    /**
     * --RU--
     * Поиск процесса по PID
     * @param int $pid
     * @return \result\taskItem
     */
    public static function findByPID($pid){
        $proc = self::exec('PID eq '.$pid);
        return ($proc->length() > 0) ? $proc->first() : false ;
    }

    /**
     * --RU--
     * Поиск процесса по имени образа
     * @param string $name
     * @return \result\taskResult
     */
    public static function find($name){
        $proc = self::exec('IMAGENAME eq '.$name);
        return ($proc->length() > 0) ? $proc : false ;
    }

    /**
     * --RU--
     * Поиск процесса по заголовку окна
     * @param string $title
     * @return \result\taskResult
     */
    public static function findByTitle($title){
        $proc = self::exec('WINDOWTITLE eq '.$title);
        return ($proc->length() > 0) ? $proc : false ;
    }

    /**
     * --RU--
     * Существует ли процесс с таким именем образа
     * @param string $name
     * @return bool
     */
    public static function exists($name){
        return self::find($name) !== false;
    }

    /**
     * --RU--
     * Существует ли процесс с таким PID
     * @param int $pid
     * @return bool
     */
    public static function pidExists($pid){
        return self::findByPID($pid) !== false;
    }

    /**
     * --RU--
     * Существует ли процесс с таким заголовком окна
     * @param string $title
     * @return bool
     */
    public static function titleExists($title){
        return self::findByTitle($title) !== false;
    }

    private static function exec($filter = false){
        // cp изменит вывод на английский язык
        $list = WSH::cmd('chcp 65001 | tasklist /V /FO CSV /NH' . ($filter === false ? '' : ' /FI ":filter"'), ['filter' => $filter], 'utf-8');
        return self::parseAnswer($list);
    }

    private static function parseAnswer($list){
        $tasks = explode("\n", $list);
        $reg = '"([^"]+)"';
        $result = new taskResult;
        foreach ($tasks as $k=>$task) {
            $regex = Regex::of($reg, Regex::CASE_INSENSITIVE + Regex::UNICODE_CASE)->with($task);
            if ($regex->find()) {
                $result->addItem($regex->all());
            }

        }

        return $result;
    }
}