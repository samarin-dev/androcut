<?php
namespace bundle\windows\result;

use bundle\windows\result\abstractResult;
use bundle\windows\result\taskItem;

/**
 * Экземпляр класса содержит список процессов, который был сформирован в одном из методов класса Task
 */
class taskResult extends abstractResult{
    public function addItem($params){
        $this->data[] = new taskItem(
           $params[0][1],
           $params[1][1],
           $params[2][1],
           $params[3][1],
           $params[4][1],
           $params[5][1],
           $params[6][1],
           $params[7][1],
           $params[8][1]
        );
    }

    /**
     * --RU--
     * Завершить процессы
     * @throws WindowsException
     */
    public function kill(){
        foreach($this->data as $k => $v){
            try{
                $v->kill();
            }catch(WindowsScriptHost $e){

            }
            unset($this->data[$k]);
        }
    }
}