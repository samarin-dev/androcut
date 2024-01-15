<?php
namespace app\forms;

use php\gui\framework\AbstractForm;
use php\gui\event\UXEvent; 
use php\gui\event\UXMouseEvent; 


class wificon extends AbstractForm
{

    /**
     * @event button.action 
     */
    function doButtonAction(UXEvent $e = null)
    {    
        $host = $this->edit->text;
        $port = $this->editAlt->text;
        $paircode = $this->edit3->text;
        
        if ($this->checkbox->selected == true)
        {
            $this->form('MainForm')->ADBAction("adb pair $host:$port $paircode");
            $this->hide();
        }
        else 
        {
            $this->form('MainForm')->ADBAction("adb connect $host:$port");
            $this->hide();
        }
        
        $this->form('MainForm')->doButton15Action();
    }

    /**
     * @event checkbox.click 
     */
    function doCheckboxClick(UXMouseEvent $e = null)
    {    
        if ($this->checkbox->selected == true)
        {
            $this->edit3->enabled = true;
            $this->button->text = 'Pair';
        }
        else 
        {
            $this->edit3->enabled = false;
            $this->button->text = 'Connect';
        }
    }

    /**
     * @event spoiler.click 
     */
    function doSpoilerClick(UXMouseEvent $e = null)
    {    
        if ($this->spoiler->expanded == true)
        {
            $this->height = 584;
        }
        else 
        {
            $this->height = 200;
        }
    }

}
