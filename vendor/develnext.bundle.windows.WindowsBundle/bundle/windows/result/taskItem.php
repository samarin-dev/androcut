<?php
namespace bundle\windows\result;

use bundle\windows\result\abstractItem;
use bundle\windows\WindowsException;
use bundle\windows\WindowsScriptHost as WSH;

/**
 * Экземпляр данного класса содержит информацию об одном процессе
 */
class taskItem extends abstractItem
{
    /**
     * --RU--
     * Имя процесса
     * @readonly
     * @var string
     */
    public $name;

    /**
     * --RU--
     * Process ID
     * @readonly
     * @var int
     */
    public $pid;

    /**
     * --RU--
     * Имя сессии
     * @readonly
     * @var string
     */
    public $sessionName;

    /**
     * --RU--
     * № сеанса
     * @readonly
     * @var int
     */
    public $sessionNumber;

    /**
     * --RU--
     * Память (в байтах)
     * @readonly
     * @var int
     */
    public $memory;

    /**
     * --RU--
     * Состояние
     * @readonly
     * @var string
     */
    public $status;

    /**
     * --RU--
     * Пользователь
     * @readonly
     * @var string
     */
    public $user;

    /**
     * CPU Time (sec)
     * --RU--
     * Время ЦП (сек)
     * @readonly
     * @var int
     */
    public $cpuTime;

    /**
     * Window Title
     * --RU--
     * Заголовок окна
     * @readonly
     * @var string
     */
    public $title;

    public function __construct($name, $pid, $sessionName, $sessionNumber, $memory, $status, $user, $cpuTime, $title){
        $this->name = $name;
        $this->pid = $pid;
        $this->sessionName = $sessionName;
        $this->sessionNumber = $sessionNumber;
        $this->memory = intval(str_replace([" ", ' ', 'K', 'M'], ['', '', '000', '000000'], $memory));
        $this->status = $status;
        $this->user = ($user == 'N/A') ? null : $user;

        $time = explode(':', $cpuTime);
        //var_dump([$cpuTime => $time]);
        $this->cpuTime = ($time[0] * 60 * 60) + ($time[1] * 60) + ($time[2]);
        //$this->cpuTime = [$cpuTime => $time];
        $this->title = ($title == 'N/A') ? null : $title;
    }

    /**
     * Завершить процесс
     * @throws WindowsException
     */
    public function kill(){
        return WSH::cmd('taskkill /F /PID ":process"', ['process' => $this->pid]);
    }
}