<?php
namespace app\forms;

use php\gui\framework\AbstractForm;
use php\gui\event\UXEvent; 


class install extends AbstractForm
{

    /**
     * @event button.action 
     */
    function doButtonAction(UXEvent $e = null)
    {
        $this->progressIndicator->show();
        $path = $this->apkoutpath->text;
        $path = strtolower(htmlspecialchars($path), ENT_QUOTES);
        
        if ($this->checkbox->selected == false)
        {
            $action = "adb shell pm install $path -r -t -d";
            app()->form('MainForm')->ADBAction($action);
        }
        else 
        {
            $devices = app()->form('MainForm')->combobox3->items->toArray();
            
            foreach ($devices as $device)
            {
                $device = explode(' ', $device);
                $device= str::trim($deviceid[0]);
                $action = "adb -s $device shell pm install $path -r -t -d";
                app()->form('MainForm')->ADBAction($action);
            }
        }
        
        $this->progressIndicator->hide();
    }

    /**
     * @event buttonAlt.action 
     */
    function doButtonAltAction(UXEvent $e = null)
    {
        $this->progressIndicator->show();
    }




}
