<?php
namespace app\forms;

use std, gui, framework, app;


class exec extends AbstractForm
{

    /**
     * @event button.action 
     */
    function doButtonAction(UXEvent $e = null)
    {    
        $com = $this->edit->text;
        $this->edit->clear();
        $this->form('MainForm')->start("adb $com", 'INF');
    }

    /**
     * @event edit.keyDown-Enter 
     */
    function doEditKeyDownEnter(UXKeyEvent $e = null)
    {    
        $this->doButtonAction();
    }

}
