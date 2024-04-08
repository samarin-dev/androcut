<?php
namespace app\forms;

use php\lib\str;
use php\gui\framework\AbstractForm;
use php\gui\event\UXEvent; 


class install extends AbstractForm
{

    /**
     * @event button.action 
     */
    function doButtonAction(UXEvent $e = null)
    {
        $this->showPreloader('Application install in process...');
        $path = $this->apkoutpath->text;
        $path = htmlspecialchars_decode("&quot;$path&quot;");
        
        //Device filter
        $deviceid = $this->form('MainForm')->combobox3->selected->text;
        $deviceid = explode(' ', $deviceid);
        $deviceid = str::trim($deviceid[0]);
        
        if ($this->checkbox->selected == false)
        {
            $action = "adb -s $deviceid install --no-streaming $path";
            $this->ADBAction($action);
        }
        else 
        {
            $devices = app()->form('MainForm')->combobox3->items->toArray();
            
            foreach ($devices as $device)
            {
                $device = $device->text;
                $device = explode(' ', $device);
                $device = str::trim($device[0]);
                $action = "adb -s $device install --no-streaming $path";
                $this->ADBAction($action);
            }
        }
        
        $this->hidePreloader();
    }

    /**
     * @event buttonAlt.action 
     */
    function doButtonAltAction(UXEvent $e = null)
    {
        $this->progressIndicator->show();
    }




}
