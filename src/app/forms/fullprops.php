<?php
namespace app\forms;

use php\gui\layout\UXHBox;
use php\gui\UXImage;
use php\gui\UXImageView;
use php\gui\paint\UXColor;
use php\gui\UXLabelEx;
use php\lib\str;
use php\lang\Thread;
use php\lang\Process;
use php\gui\framework\AbstractForm;
use php\gui\event\UXWindowEvent; 


class fullprops extends AbstractForm
{

    /**
     * @event show 
     */
    function doShow(UXWindowEvent $e = null)
    {    
        $this->form('fullprops')->start('adb shell getprop');
    }
    //Starting process and getting output
    protected $process, $thread;
    
    public function start($command, $icotype = 'APK')
    {   
        $this->listView->items->clear();
        
        $this->process = new Process(explode(' ', $command));
        $this->process = $this->process->start();
        
        $this->thread = new Thread(function(){
            $this->process->getInput()->eachLine(function($line){
                uiLater(function() use ($line) {
                    $this->addlines($line, '#FFFFFF', $icotype);
                });
            });

            $this->process->getError()->eachLine(function($line){
                uiLater(function () use ($line) {
                    $this->addlines($line, '#FFAAAA');
                }); 
            });
            
            $exitValue = $this->process->getExitValue();
            uiLater(function () use ($exitValue) {
              
            });
        });
        
        $this->thread->start();
    }
    
    
    //Addidng elements to listView
    protected function addlines($line, $color = '#FFFFFF'){

        if(str::length(str::trim($line)) == 0)return; 
        
        $line = str_replace('package:', '', $line);
        
        $item = new UXLabelEx($line);
        $item->autoSize = true;
        $item->textColor = UXColor::of($color);
        $item->autoSize = TRUE;
        $item->wrapText = TRUE;
        $this->listView->items->add($item);
        
        $this->listView->scrollTo($this->listView->items->count);
    }

}
