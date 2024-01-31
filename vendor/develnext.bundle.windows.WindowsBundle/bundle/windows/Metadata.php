<?php
namespace bundle\windows;

use bundle\windows\WindowsScriptHost as WSH;
use php\io\File;
use php\lib\str;
use php\util\Regex;
use php\util\Scanner;

/**
 * @packages windows
 */
class Metadata 
{  
    /**
     * @var File
     */
    protected $file;

    /**
     * @param File|string $file
     */
    public function __construct($file){
        $this->file = ($file instanceof File) ? $file : File::of($file) ;
    }

    public function readData(int $items = 300){
        $title_query = '
            $objShell = New-Object -ComObject Shell.Application
            $objFolder = $objShell.namespace(":path")
            0..:items | Foreach-Object { \'{0} = {1}\' -f $_, $objFolder.GetDetailsOf($null, $_) }
        ';        
        
        $data_query = '
            $objShell = New-Object -ComObject Shell.Application
            $objFolder = $objShell.namespace(":path")
            $objFile = $objFolder.parsename(":file")
            0..:items | Foreach-Object { \'{0} = {1}\' -f $_, $objFolder.GetDetailsOf($objFile, $_) }
        ';

        $title_items = WindowsScriptHost::PowerShell($title_query, ['file' => $this->file->getName(), 'path' => dirname($this->file->getAbsolutePath()), 'items' => $items]);
        $data_items = WindowsScriptHost::PowerShell($data_query, ['file' => $this->file->getName(), 'path' => dirname($this->file->getAbsolutePath()), 'items' => $items]);
        
        $title = $this->parse($title_items);
        $data = $this->parse($data_items);
        
        $array = array_combine($title, $data);
        foreach ($array as $k=>$v){
            if(strlen($k) == 0 || strlen($v) == 0){
                unset($array[$k]);
            }
        }
        
        return $array;
    }
    
    
    protected function parse($lines){
        $scanner = new Scanner($lines, 'UTF-8');
        $data = [];
        
        while ($scanner->hasNextLine()) {
            $line = $scanner->nextLine();
            list($key, $value) = str::split($line, '=', 2);
            $key = intval(str::trim($key));
            $value = str::trim($value);
            
            $data[$key] = $value;
            
        }

        return $data;
    }
}