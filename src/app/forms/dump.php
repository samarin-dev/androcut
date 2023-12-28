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
        
        //Device filter
        
        $deviceid = $this->form('MainForm')->combobox3->selected->text;
        $deviceid = explode(' ', $deviceid);
        $deviceid = str::trim($deviceid[0]);
        
        try 
        {
            $apkdir = fs::abs($this->edit->text);
            $packages = $this->form('MainForm')->listView->items->toArray();
        
            foreach ($packages as $package)
            {
                if ($package->selected == true)
                {
                    $packagename = $package->text;
                    $this->form('MainForm')->EXEAction("adb -s $deviceid pull $packagename $apkdir");
                }
            }
            
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

    /**
     * @event edit.keyDown-Enter 
     */
    function doEditKeyDownEnter(UXKeyEvent $e = null)
    {    
        $this->doButton4Action();
    }



}
