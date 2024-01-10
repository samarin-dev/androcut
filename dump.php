<?php
namespace app\forms;

use Error;
use std, gui, framework, app;
use php\gui\event\UXWindowEvent; 


class dump extends AbstractForm
{

    /**
     * @event button4.action 
     */
    function doButton4Action(UXEvent $e = null)
    {
        //Device filter
        
        $deviceid = app()->form('MainForm')->combobox3->selected->text;
        $deviceid = explode(' ', $deviceid);
        $deviceid = str::trim($deviceid[0]);
        
        try 
        {
            $apkdir = fs::abs($this->edit->text);
            $packages = app()->form('MainForm')->listView->items->toArray();
        
            foreach ($packages as $package)
            {
                if ($package->selected == true)
                {
                    $packagename = $package->text;
                    app()->form('MainForm')->ADBAction("adb -s $deviceid pull $packagename $apkdir");
                }
            }
            $this->form('MainForm')->doButtonAction();
            $this->hide();
        }
        catch (Error $e)
        {
            $this->form('MainForm')->doButtonAction();
            app()->form('MainForm')->toast("Error: $e");
        }
        //$this->hide();
    }

    /**
     * @event close 
     */
    function doClose(UXWindowEvent $e = null)
    {    
        $this->form('MainForm')->doButtonAction();
    }

    /**
     * @event edit.keyDown-Enter 
     */
    function doEditKeyDownEnter(UXKeyEvent $e = null)
    {    
        $this->doButton4Action();
    }

    /**
     * @event showing 
     */
    function doShowing(UXWindowEvent $e = null)
    {    
        
    }



}
