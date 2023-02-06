<?php
namespace app\forms;

use Error;
use std, gui, framework, app;


class dump extends AbstractForm
{

    /**
     * @event button4.action 
     */
    function doButton4Action(UXEvent $e = null)
    {
        $this->progressIndicator->show();
        try 
        {
            $packagename = $this->form('MainForm')->listView->selectedItem->text;
            $apkdir = fs::abs($this->edit->text);
            $this->form('MainForm')->start("adb pull $packagename $apkdir");
            $this->progressIndicator->hide();
            $this->form('MainForm')->doButtonAction();
            $this->form('MainForm')->button4->enabled = true;
        
        }
        catch (Error $e)
        {
            $this->progressIndicator->hide();
            $this->form('MainForm')->doButtonAction();
            $this->form('MainForm')->button4->enabled = true;
            app()->form('MainForm')->toast("Error: $e");
        }
        $this->hide();
    }

    /**
     * @event close 
     */
    function doClose(UXWindowEvent $e = null)
    {    
        $this->form('MainForm')->doButtonAction();
    }


}
