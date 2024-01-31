<?php
namespace bundle\windows;

use bundle\windows\Prepare;
use bundle\windows\WindowsException;
use bundle\windows\result\wshResult;
use php\framework\Logger;
use php\gui\framework\AbstractModule;
use php\lang\Process;
use php\lib\str;
use php\util\Regex;

/**
 * Методы класса позволяют вызывать функции API Windows, выполнять системные скрипты
 * @packages windows
 */
class WindowsScriptHost
{
    protected static function exec($cmd, $wait = false, $charset = 'cp1251'){
        $cmd = (!is_array($cmd)) ? [$cmd] : $cmd;
 
        $process = new Process($cmd);
        $process = $process->start();     

        $wshResult = new wshResult($process);
        $wshResult->setCharset($charset);
    
        $output = $wshResult->getOutput();

        if(strlen($output) == 0 and strlen($error = $wshResult->getError()) > 0){
            throw new WindowsException('WindowsScriptHost Error: ' . $error);
        }
        
        return $output;
    }
  
    /**
     * --RU--
     * Выполнить команду
     * @param string $command
     * @param array $params=array() параметры для замены (в запросе можно передать именованные параметры, как в PDO)
     * @param string $charset кодировка ответа (в командной строке по умолчанию cp866). utf-8 возвращает всё на английском языке
     * @param string $decodeCharset кодировка, из которой будет декодироваться ответ. Некоторые команды (например ipconfig), возвращают в cp866, даже если перед ней явно указан вывод командой chcp
     * @return string
     * @throws WindowsException
     */  
    public static function cmd($command, $params = [], $charset = 'utf-8', $decodeCharset = 'auto'){
        if($charset == 'utf-8') $chcp = 65001;
        else $chcp = str_replace(['cp', 'windows', '-'], '', $charset);
        
        $command = Windows::getSystem32('chcp.com') . ' ' . $chcp . '>nul & ' . $command;
        $command = Prepare::Query($command, $params);    
        return self::Exec([Windows::getSystem32('cmd.exe'), '/c', $command], true, ($decodeCharset == 'auto' ? $charset : $decodeCharset));
    }
    
    /**
     * --RU--
     * Сделать запрос к WMI
     * @param string $query
     * @return array
     * @throws WindowsException
     */
    public static function WMIC($query){
        // more убирает лишние байты возле символа переноса строки, что значительно упрощает парсинг 
        // cp866 не должно конфликтовать с more
        $data = self::cmd(Windows::getSystem32('wbem\\wmic.exe') . ' :query /Format:List | ' . Windows::getSystem32('more.com'), ['query' => $query], 'cp866');

        $reg = '([^\n=]+)=([^\n\r]+)';
        $regex = Regex::of($reg, Regex::CASE_INSENSITIVE + Regex::MULTILINE)->with($data);

        $return = [];
        $key = 0;
        while ($regex->find()) {
            $k = $regex->group(1);
            $v = $regex->group(2);
            if (isset($return[$key][$k])) $key++;
            $return[$key][$k] = $v;
        }
        // Не возвращает [0], т.к. может быть несколько устройств, напирмер, sounddev get
        return $return;
    }
    
    /**
     * --RU--
     * Выполнить скрипт PowerShell
     * @param string $query
     * @param array $params параметры для замены
     * @param bool $wait ожидать окончания
     * @return string
     * @throws WindowsException
     */
    public static function PowerShell($query, $params = [], $wait = true){
        $query = "[Console]::OutputEncoding = [System.Text.Encoding]::UTF8\n" . $query;
        $source = Prepare::Query($query, $params);
        $command = 'Invoke-Expression ([System.Text.Encoding]::UTF8.GetString([System.Convert]::FromBase64String(\'' . base64_encode($source) . '\')))'; 
        $psPath = Windows::getSysNative('WindowsPowerShell\\v1.0\\powershell.exe');
        return self::Exec([$psPath, '-inputformat', 'none', '-command', $command], $wait, 'utf-8');  
    }
    
    /**
     * --RU--
     * Выполнить скрипт vbScript (должен располагаться в одну строку)
     * @param string $query
     * @param string $params
     * @return string
     * @throws WindowsException
     * @deprecated
     */
    public static function vbScript($query, $params = []){
        $command = Prepare::Query($query, $params); 

        $cmd = new Prepare(Windows::getSystem32('mshta.exe') . ' vbscript:Execute(":query(window.close)")');
        $cmd->addStringQuotes = false;
        $cmd->quotesPolicy = 2;
        return self::cmd($cmd->getQuery(['query' => $command]));
    }
}