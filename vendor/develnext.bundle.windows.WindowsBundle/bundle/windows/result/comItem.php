<?php
namespace bundle\windows\result;

use php\io\MiscStream;
use bundle\windows\WindowsScriptHost as WSH;

class comItem extends abstractItem
{
    /**
     * --RU--
     * Порт
     * @readonly
     * @var string
     */
    public $port;

    /**
     * --RU--
     * Параметры порта
     * @var array
     * @readonly
     */
    public $params;

    public function __construct($port, $params){
        $this->port = $port;
        $this->params = $params;   
    }

     /**
     * Port
     * --RU--
     * Порт
     * @return string
     */
    public function getPort(){
        return $this->port;
    }

    /**
     * Port params
     * --RU--
     * Параметры
     * @return array
     */
    public function getParams(){
        return $this->params;
    }

    /**
     * --RU--
     * Подключиться к порту
     * @param string $mode='r'
     * @return php\io\MiscStream
     * @throws IOException
     */
    public function connect($mode = 'r'){
        return MiscStream::of($this->port);
    }

    /**
     * --RU--
     * Установить скорость порта (бод)
     * @param int $baud
     * @throws WindowsException
     */
    public function setBaud($baud){
        return WSH::cmd('mode ' . $this->port . ' baud=' . $baud);
    }

    public function __toString(){
        return (string) $this->port;
    }

}