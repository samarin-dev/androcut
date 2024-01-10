<?php
namespace app\forms;

use std, gui, framework, app;


class savescript extends AbstractForm
{

    /**
     * @event button.action 
     */
    function doButtonAction(UXEvent $e = null)
    {    
        $packages = $this->form('MainForm')->listView->items->toArray();
        $scriptname = $this->edit->text;
        
        foreach ($packages as $package)
        {
            if ($package->selected == true)
            {
                $packagename = $package->text;
                file_put_contents("scripts/$scriptname.aaus", "$packagename\r\n", FILE_APPEND);
            }
        }
        $this->form('savescript')->hide();
    }

    /**
     * @event edit.keyDown-Enter 
     */
    function doEditKeyDownEnter(UXKeyEvent $e = null)
    {    
        $this->doButtonAction();
    }

}
