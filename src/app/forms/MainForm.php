<?php
namespace app\forms;

use app\forms\settings;
use php\time\Time;
use php\gui\UXCheckbox;
use php\gui\UXMaterialCheckbox;
use php\gui\UXMaterialTabPane;
use bundle\anipp\Curves\EaseElasticIn;
use php\gui\animatefx\AnimationFX;
use bundle\anipp\Curves\EaseCubicIn;
use bundle\anipp\AniPP;
use php\gui\framework\event\AbstractEventType;
use php\lib\arr;
use preg;
use php\lang\Process;
use php\lang\System;
use bundle\windows\WindowsException;
use bundle\windows\WindowsScriptHost;
use bundle\windows\Windows;
use windows;
use php\io\IOException;
use Error;
use php\io\Stream;
use std, gui, framework, app;
use php\gui\event\UXEvent; 
use php\gui\event\UXMouseEvent; 
use php\gui\event\UXKeyEvent; 
use php\gui\event\UXWindowEvent; 


class MainForm extends AbstractForm
{
    
    //UI/UX
    /**
     * @event button3.action 
     */
    function doButton3Action(UXEvent $e = null)
    {    
        //Device filter
        $deviceid = $this->combobox3->selected->text;
        $deviceid = explode(' ', $deviceid);
        $deviceid = str::trim($deviceid[0]);
        
        $packages = $this->listView->items->toArray();
        
        foreach ($packages as $package)
        {
            if ($package->selected == true)
            {
                $packagename = $package->text;
                $this->ADBAction("adb -s $deviceid shell pm uninstall -k --user 0 $packagename");
            }
        }    
        $this->doButtonAction();
    }
    
    //UI/UX
    
    //UI/UX

    /**
     * @event button.action 
     */
    function doButtonAction(UXEvent $e = null)
    {    
        //Device filter
        $deviceid = $this->combobox3->selected->text;
        $deviceid = explode(' ', $deviceid);
        $deviceid = str::trim($deviceid[0]);
        
        $command = "adb -s $deviceid shell pm list packages";
        
        if ($this->checkboxAlt->selected == true)
        {
            $command = $command;
        }
        elseif ($this->checkboxAlt->selected == false)
        {
            if ($this->checkbox3->selected == true)
            {
                $command .= ' -d';
            }
            if ($this->checkbox4->selected == true)
            {
                $command .= ' -e';
            }
            if ($this->checkbox5->selected == true)
            {
                $command .= ' -s';
            }
            if ($this->checkbox6->selected == true)
            {
                $command .= ' -3';
            }
        }
        $this->start($command);
        
        //Using "try{}" because on some devices with custom/bad ROM`s app can be crashed 
        try 
        {
            //Just UI, adding some branding to app
            //How you can see is not all brands, will be updated soon
            $devbrand1 = (new Process ( explode(' ', "adb -s $deviceid shell getprop ro.product.brand")))->start()->getInput()->readFully();
            $devbrand2 = (new Process ( explode(' ', "adb -s $deviceid shell getprop ro.product.model")))->start()->getInput()->readFully();
            
            $devbrand1 = str_replace(PHP_EOL, '', strtoupper($devbrand1));
            $devbrand2 = str_replace(PHP_EOL, '', strtoupper($devbrand2));
            
            $brand[0] = $devbrand1;
            $brand[1] = $devbrand2;
            
            $this->link->text = implode(" ", $brand);
            
            if (str::contains($devbrand1, 'DOOGEE') == true)
            {
                $brandlogo = new UXImage('res://.data/img/doogee.png');
            }
            else if (str::contains($devbrand1, 'HUAWEI') == true)
            {
                $brandlogo = new UXImage('res://.data/img/huawei.png');
            }
            else if (str::contains($devbrand1, 'LENOVO') == true)
            {
                $brandlogo = new UXImage('res://.data/img/lenovo.png');
            }
            else if (str::contains($devbrand1, 'LG') == true)
            {
                $brandlogo = new UXImage('res://.data/img/LG.png');
            }
            else if (str::contains($devbrand1, 'XIAOMI') == true)
            {
                $brandlogo = new UXImage('res://.data/img/mi.png');
            }
            else if (str::contains($devbrand1, 'MOTOROLA') == true)
            {
                $brandlogo = new UXImage('res://.data/img/motorola.png');
            }
            else if (str::contains($devbrand1, 'SAMSUNG') == true)
            {
                $brandlogo = new UXImage('res://.data/img/samsung.png');
            }
            else if (str::contains($devbrand1, 'TECNO') == true)
            {
                $brandlogo = new UXImage('res://.data/img/tecno.png');
            }
            else if (str::contains($devbrand1, 'ASUS') == true)
            {
                $brandlogo = new UXImage('res://.data/img/asus.png');
            }
            else if (str::contains($devbrand1, 'NOTHING') == true)
            {
                $brandlogo = new UXImage('res://.data/img/nothing.png');
            }
            else if (str::contains($devbrand1, 'ZTE') == true)
            {
                $brandlogo = new UXImage('res://.data/img/ZTE.png');
            }
            else 
            {
                $brandlogo = new UXImage('res://.data/img/android2.png');
            }
            
            $this->image->image = $brandlogo;
        }
        catch (Error $e) {$this->toast($e);}
        
        
        //Getting information about device, now using just for information bar
        //Using "try{}" because on some devices with custom/bad ROM`s app can be crashed 
        try 
        {
            $devserial = (new Process ( explode(' ', "adb -s $deviceid get-serialno")))->start()->getInput()->readFully();
            
            $this->label7->text = $devserial;
        }
        catch (Error $e) {$this->toast($e);}
        
        try 
        {
            $devcpu = (new Process ( explode(' ', "adb -s $deviceid shell getprop ro.product.cpu.abilist")))->start()->getInput()->readFully();
            $this->label8->text = $devcpu;
        }
        catch (Error $e) {$this->toast($e);}
        
        try 
        {
            $devfota = (new Process ( explode(' ', "adb -s $deviceid shell getprop ro.fota.version")))->start()->getInput()->readFully();
            $this->linkAlt->text = $devfota;
        }
        catch (Error $e) {$this->toast($e);}
        
        try 
        {
            $devgms = (new Process ( explode(' ', "adb -s $deviceid shell getprop ro.com.google.gmsversion")))->start()->getInput()->readFully();
            $this->label10->text = $devgms;
        }
        catch (Error $e) {$this->toast($e);}
        
        try 
        {
            $devand = (new Process ( explode(' ', "adb -s $deviceid shell getprop ro.build.version.release")))->start()->getInput()->readFully();
            $this->label12->text = $devand;
        }
        catch (Error $e) {$this->toast($e);}
        
        try 
        {
            $devboot = (new Process ( explode(' ', "adb -s $deviceid shell getprop ro.bootloader")))->start()->getInput()->readFully();
            $this->label14->text = $devboot;
        }
        catch (Error $e) {$this->toast($e);}
        
        try 
        {
            $devsec = (new Process ( explode(' ', "adb -s $deviceid shell getprop ro.build.version.security_patch")))->start()->getInput()->readFully();
            $this->label16->text = $devsec;
        }
        catch (Error $e) {$this->toast($e);}
    }

    
    //App disable
    /**
     * @event buttonAlt.action 
     */
    function doButtonAltAction(UXEvent $e = null)
    {    
        //Device filter
        
        $deviceid = $this->combobox3->selected->text;
        $deviceid = explode(' ', $deviceid);
        $deviceid = str::trim($deviceid[0]);
        
        
        $packages = $this->listView->items->toArray();
        
        foreach ($packages as $package)
        {
            if ($package->selected == true)
            {
                $packagename = $package->text;
                $this->ADBAction("adb -s $deviceid shell pm disable-user --user 0 $packagename");
            }
        }
        
        $this->doButtonAction();
    }
    
    
    //App enable
    /**
     * @event button8.action 
     */
    function doButton8Action(UXEvent $e = null)
    {
        //Device filter
        
        $deviceid = $this->combobox3->selected->text;
        $deviceid = explode(' ', $deviceid);
        $deviceid = str::trim($deviceid[0]);
        
        $packages = $this->listView->items->toArray();
        
        foreach ($packages as $package)
        {
            if ($package->selected == true)
            {
                $packagename = $package->text;
                $this->ADBAction("adb -s $deviceid shell pm enable --user 0 $packagename");
            }
        }
        
        $this->doButtonAction();
    }
    
    
    //UI/UX
    /**
     * @event button4.action 
     */
    function doButton4Action(UXEvent $e = null)
    {    
        //Device filter
        
        $deviceid = $this->combobox3->selected->text;
        $deviceid = explode(' ', $deviceid);
        $deviceid = str::trim($deviceid[0]);
        
        $packages = $this->listView->items->toArray();
        
        app()->showForm('dump');
        
        foreach ($packages as $package)
        {
            if ($package->selected == true)
            {
                $package = $package->text;
                $this->start("adb -s $deviceid shell pm path $package");
                break;
            }
        }
    }
    
    
    //UI/UX
    /**
     * @event link.action 
     */
    function doLinkAction(UXEvent $e = null)
    {    
        $link = $this->link->text;
        open("https://www.google.com/search?q= $link");
    }

    /**
     * @event linkAlt.action 
     */
    function doLinkAltAction(UXEvent $e = null)
    {    
        $linkAlt = $this->linkAlt->text;
        open(htmlspecialchars("https://www.google.com/search?q= $linkAlt"), ENT_QUOTES);
    }


    /**
     * @event show 
     */
    function doShow(UXWindowEvent $e = null)
    {    
        $this->PrepUI();
    }


    /**
     * @event listView.action 
     */
    function doListViewAction(UXEvent $e = null)
    {
        $this->buttonAlt->enabled = true;
        $this->button8->enabled = true;
        $this->button3->enabled = true;
        $this->button4->enabled = true;
        
        $line = $this->listView->focusedItem->text;
        
        $this->AICompare($line);
    }

    /**
     * @event close 
     */
    function doClose(UXWindowEvent $e = null)
    {    
        app()->shutdown();
    }

    /**
     * @event button10.action 
     */
    function doButton10Action(UXEvent $e = null)
    {
        //Device filter
        
        $deviceid = $this->combobox3->selected->text;
        $deviceid = explode(' ', $deviceid);
        $deviceid = str::trim($deviceid[0]);
        
        $this->ADBAction("adb -s $deviceid shell input keyevent KEYCODE_POWER");
    }

    /**
     * @event button11.action 
     */
    function doButton11Action(UXEvent $e = null)
    {
        //Device filter
        
        $deviceid = $this->combobox3->selected->text;
        $deviceid = explode(' ', $deviceid);
        $deviceid = str::trim($deviceid[0]);
        
        $this->ADBAction("adb -s $deviceid shell reboot -p");
    }

    /**
     * @event button9.action 
     */
    function doButton9Action(UXEvent $e = null)
    {
        //Device filter
        
        $deviceid = $this->combobox3->selected->text;
        $deviceid = explode(' ', $deviceid);
        $deviceid = str::trim($deviceid[0]);
        
        $this->ADBAction("adb -s $deviceid shell reboot bootloader");
    }

    /**
     * @event button7.action 
     */
    function doButton7Action(UXEvent $e = null)
    {
        //Device filter
        
        $deviceid = $this->combobox3->selected->text;
        $deviceid = explode(' ', $deviceid);
        $deviceid = str::trim($deviceid[0]);
        
        $this->ADBAction("adb -s $deviceid shell reboot");
    }



    /**
     * @event listView.mouseEnter 
     */
    function doListViewMouseEnter(UXMouseEvent $e = null)
    {    
        $total = $this->listView->items->count;
        $this->label41->text = "Total: $total";
        $this->helper->text = 'List of applications available on device by selected filter';
    }



    /**
     * @event button28.action 
     */
    function doButton28Action(UXEvent $e = null)
    {
        app()->showForm('savescript');
    }

    /**
     * @event button33.action 
     */
    function doButton33Action(UXEvent $e = null)
    {
        $this->listView4->items->clear();
        $dir = new File(fs::abs('scripts'));
        $list = $dir->findFiles();
        
        foreach ($list as $item)
        {    
            $item = fs::nameNoExt($item);
            $item = preg_replace('/\s+/', '', $item);
            
            $name = new UXLabelEx;
            $name->text = $item;
            $name->autoSize = true;
            $this->listView4->items->add($name);
        }
    }



    /**
     * @event button17.action 
     */
    function doButton17Action(UXEvent $e = null)
    {
        $file = $this->listView4->selectedItem->text;
        fs::delete(fs::abs("scripts/$file.aaus"));
        $this->toast(fs::abs("scripts/$file.aaus"));
        $this->doButton33Action();
    }


    /**
     * @event listView4.action 
     */
    function doListView4Action(UXEvent $e = null)
    {    
        $this->listView5->items->clear();
        $file = $this->listView4->selectedItem->text;
        $this->editAlt->text = $file;
        $input = file("scripts/$file.aaus");
        
        foreach ($input as $line)
        {
            $line = preg_replace('/\s+/', '', $line);
            $item = new UXCheckbox;
            $item->autoSize = true;
            $item->textColor = UXColor::of($color);                          
            $item->text = $line;
            $item->autoSize = TRUE;
            $item->wrapText = TRUE;
            $item->rightAnchor = 1;
            $item->leftAnchor = 1;
            $item->selected = true;
            $this->listView5->items->add($item);
        }
    }

    /**
     * @event button43.action 
     */
    function doButton43Action(UXEvent $e = null)
    {   
        $this->button42->text = 'Packages on device';
        $this->checkboxAlt->selected = true;
        $this->doCheckboxAltClick();
        $this->doButtonAction();
    }

    /**
     * @event button39.action 
     */
    function doButton39Action(UXEvent $e = null)
    {    
        $list = $this->listView5->items->toArray();
        $this->listView6->items->clear();
        $this->button42->text = 'Operation state...';
        
        //Device filter
        
        $deviceid = $this->combobox3->selected->text;
        $deviceid = explode(' ', $deviceid);
        $deviceid = str::trim($deviceid[0]);
        
        foreach ($list as $item)
        {
            if ($item->selected == true)
            {
                $item = $item->text;
                $action = "adb -s $deviceid shell pm disable-user --user 0 $item";
                $this->ADBAction($action);
                $this->toast($action);
            }
        }
    }

    /**
     * @event button40.action 
     */
    function doButton40Action(UXEvent $e = null)
    {    
        $list = $this->listView5->items->toArray();
        $this->listView6->items->clear();
        $this->button42->text = 'Operation state...';
        
        //Device filter
        
        $deviceid = $this->combobox3->selected->text;
        $deviceid = explode(' ', $deviceid);
        $deviceid = str::trim($deviceid[0]);
        
        foreach ($list as $item)
        {
            if ($item->selected == true)
            {
                $item = $item->text;
                $action = "adb -s $deviceid shell pm enable --user 0 $item";
                $this->ADBAction($action);
                $this->toast($action);
            }
        }
    }

    /**
     * @event button41.action 
     */
    function doButton41Action(UXEvent $e = null)
    {    
        $list = $this->listView5->items->toArray();
        $this->listView6->items->clear();
        $this->button42->text = 'Operation state...';
        
        //Device filter
        
        $deviceid = $this->combobox3->selected->text;
        $deviceid = explode(' ', $deviceid);
        $deviceid = str::trim($deviceid[0]);
        
        foreach ($list as $item)
        {
            if ($item->selected == true)
            {
                $item = $item->text;
                $action = "adb -s $deviceid shell pm uninstall --user 0 $item";
                $this->ADBAction($action);
                $this->toast($action);
            }
        }
    }

    /**
     * @event button25.action 
     */
    /*function doButton25Action(UXEvent $e = null)
    {
        try 
         {
             $devplat = (new Process ( explode(' ', 'adb shell getprop Build.BRAND')))->start()->getInput()->readFully();
             $this->label18->text = $devplat;
         }
         catch (Error $e) {$this->toast($e);}
         
         try 
         {
             $devcore = (new Process ( explode(' ', 'adb shell getprop dalvik.vm.isa.arm.variant')))->start()->getInput()->readFully();
             $this->label20->text = $devcore;
         }
         catch (Error $e) {$this->toast($e);}
         
         try 
         {
             $devgsmtype = (new Process ( explode(' ', 'adb shell getprop gsm.network.type')))->start()->getInput()->readFully();
             $this->label22->text = $devgsmtype;
         }
         catch (Error $e) {$this->toast($e);}
         
         try 
         {
             $devgsmop = (new Process ( explode(' ', 'adb shell getprop gsm.operator.alpha')))->start()->getInput()->readFully();
             $this->label24->text = $devgsmop;
         }
         catch (Error $e) {$this->toast($e);}
         
         try 
         {
             $devgsmCC = (new Process ( explode(' ', 'adb shell getprop gsm.operator.iso-country')))->start()->getInput()->readFully();
             $this->label26->text = $devgsmCC;
         }
         catch (Error $e) {$this->toast($e);}
         
         try 
         {
             $devgsmro = (new Process ( explode(' ', 'adb shell getprop gsm.operator.isroaming')))->start()->getInput()->readFully();
             $this->label28->text = $devgsmro;
         }
         catch (Error $e) {$this->toast($e);}
         
         try 
         {
             $devgsmbb = (new Process ( explode(' ', 'adb shell getprop gsm.operator.baseband')))->start()->getInput()->readFully();
             $this->label30->text = $devgsmbb;
         }
         catch (Error $e) {$this->toast($e);}
         
         try 
         {
             $devgsmserial = (new Process ( explode(' ', 'adb shell getprop gsm.serial')))->start()->getInput()->readFully();
             $this->label32->text = $devgsmserial;
         }
         catch (Error $e) {$this->toast($e);}
         
         try 
         {
             $devgsmverbb = (new Process ( explode(' ', 'adb shell getprop gsm.version.baseband')))->start()->getInput()->readFully();
             $this->label34->text = $devgsmverbb;
         }
         catch (Error $e) {$this->toast($e);}
         
         try 
         {
             $devsystime = (new Process ( explode(' ', 'adb shell getprop persist.sys.timezone')))->start()->getInput()->readFully();
             $this->label36->text = $devsystime;
         }
         catch (Error $e) {$this->toast($e);}
         
         try 
         {
             $devcpumodel = (new Process ( explode(' ', 'adb shell getprop ro.board.platform')))->start()->getInput()->readFully();
             $this->label38->text = $devcpumodel;
         }
         catch (Error $e) {$this->toast($e);}
         
         try 
         {
             $devbddate = (new Process ( explode(' ', 'adb shell getprop ro.bootimage.build.date')))->start()->getInput()->readFully();
             $this->label40->text = $devbddate;
         }
         catch (Error $e) {$this->toast($e);}
    }*/


    /**
     * @event button37.action 
     */
    function doButton37Action(UXEvent $e = null)
    {    
        $packages = $this->listView5->items->toArray();
        $scriptname = $this->editAlt->text;
        fs::delete(fs::abs("scripts/$scriptname.aaus"));
        
        foreach ($packages as $package)
        {
            if ($package->selected == true)
            {
                $packagename = $package->text;
                file_put_contents("scripts/$scriptname.aaus", "$packagename\r\n", FILE_APPEND);
            }
        }
    }

    /**
     * @event button36.action 
     */
    function doButton36Action(UXEvent $e = null)
    {    
        app()->showForm('add');
    }

    /**
     * @event listView6.click-2x 
     */
    function doListView6Click2x(UXMouseEvent $e = null)
    {    
        $item = new UXCheckbox;
        $item->autoSize = true;
        $item->textColor = UXColor::of($color);                          
        $item->text = $this->listView6->selectedItem;
        $item->autoSize = TRUE;
        $item->wrapText = TRUE;
        $item->rightAnchor = 1;
        $item->leftAnchor = 1;
        $item->selected = true;
        $this->listView5->items->add($item);
        $this->form('add')->hide();
    }

    /**
     * @event combobox3.action 
     */
    function doCombobox3Action(UXEvent $e = null)
    {    
        $this->doButtonAction();
    }
    
    /**
     * @event button15.action 
     */
    function doButton15Action(UXEvent $e = null)
    {    
        $this->combobox3->items->clear();
        $proc = (new Process( explode(' ', 'adb devices -l')))->start()->getInput()->readFully();
        $proc = explode("\r\n", $proc);
        
        foreach ($proc as $line)
        {
            if ($line != str::contains($line, 'List of devices attached'))
            {
                if ($line != str::contains($line, '* daemon not running; starting now at tcp:'))
                {
                    if ($line != str::contains($line, '* daemon started successfully'))
                    {
                        $fxout = new UXLabelEx;
                        $fxout->rightAnchor = 1;
                        $fxout->leftAnchor = 1;
                        $fxout->text = $line;
                        $fxout->textColor = UXColor::of('#ffff4d');
                        $img_icon = new UXImageView(new UXImage('res://.data/img/device.png'));                          
                        $icon = new UXHBox([$img_icon]);
                        $fxout->graphic = $icon;
                        $this->combobox3->items->add($fxout);
                    }
                }
            }
        }
        $this->combobox3->selectedIndex = 0;
        //$this->doButtonAction();
    }

    /**
     * @event tabPane.globalKeyPress-F1 
     */
    function doTabPaneGlobalKeyPressF1(UXKeyEvent $e = null)
    {    
        //just for tests
        
        $this->doButton25Action();
    }


    /**
     * @event button12.action 
     */
    function doButton12Action(UXEvent $e = null)
    {    
        $partition = $this->combobox->selected;
        $this->FastbootAction("fastboot erase $partition");
    }

    /**
     * @event button32.action 
     */
    function doButton32Action(UXEvent $e = null)
    {    
        $partition = $this->comboboxAlt->selected;
        $filename = $this->editimg->text;
        $this->FastbootAction("fastboot flash $partition $filename");
    }

    /**
     * @event button29.action 
     */
    function doButton29Action(UXEvent $e = null)
    {    
        $this->FastbootAction("fastboot oem unlock");
    }

    /**
     * @event button30.action 
     */
    function doButton30Action(UXEvent $e = null)
    {    
        $this->FastbootAction("fastboot flashing unlock");
    }

    /**
     * @event button16.action 
     */
    function doButton16Action(UXEvent $e = null)
    {    
        $this->FastbootAction("fastboot flashing unlock_critical");
    }

    /**
     * @event button24.action 
     */
    function doButton24Action(UXEvent $e = null)
    {    
        $this->FastbootAction("fastboot oem lock");
    }

    /**
     * @event button26.action 
     */
    function doButton26Action(UXEvent $e = null)
    {    
        $this->FastbootAction("fastboot flashing lock");
    }

    /**
     * @event button18.action 
     */
    function doButton18Action(UXEvent $e = null)
    {    
        $this->FastbootAction("fastboot flashing lock_critical");
    }

    /**
     * @event button31.action 
     */
    function doButton31Action(UXEvent $e = null)
    {    
        $this->FastbootAction("fastboot oem edl");
    }

    /**
     * @event button20.action 
     */
    function doButton20Action(UXEvent $e = null)
    {
        //Device filter
        
        $deviceid = $this->combobox3->selected->text;
        $deviceid = explode(' ', $deviceid);
        $deviceid = str::trim($deviceid[0]);
        
        execute("scrcpy -s $deviceid");
    }

    /**
     * @event button19.action 
     */
    function doButton19Action(UXEvent $e = null)
    {    
        $input = $this->edit3->text;
        UXClipboard::setText($input);
        
        $this->edit3->text = '';
        $this->listView7->items->add(' ');
        $this->listView7->items->add('---');
        $this->listView7->items->add("> $input");
        $this->listView7->items->add('---');
        
        if (str::contains($input, 'an') == true)
        {
            if ($input == 'an help')
            {
                $commands[0] = '[>Command syntax:<]';
                $commands[1] = ' ';
                $commands[2] = '.:: NOTICE ::.';
                $commands[3] = 'Do not try to execute Command Line in Command Line, that`s will crash the program !';
                $commands[4] = '(example: adb shell, cmd.exe, androcut.exe e.g.)';
                $commands[5] = ' ';
                $commands[6] = '.:: ALLOWED COMMAND SYNTAX ::.';
                $commands[7] = 'Executable files with parametrs whitch self-stops after operation';
                $commands[8] = '(example: adb shell pm uninstall -k --user 0)';
                $commands[9] = ' ';
                $commands[10] = '[>LIST OF SUPPRTED COMMANDS<]';
                $commands[11] = ' ';
                $commands[12] = 'an help';
                $commands[13] = 'Show this short guide';
                $commands = implode("\r\n", $commands);
                $this->listView7->items->add($commands);
                $this->listView7->scrollTo($this->listView7->items->count());
            }
        }
        else 
        {
            try 
            {
                $output = (new Process ( explode(' ', "$input")))->start()->getInput()->readFully();
            }
            catch (IOException $e) {$this->toast($e);}
            
            $this->listView7->items->add("$output");
            $this->listView7->scrollTo($this->listView7->items->count());
        }
        
        $this->AddToLog($input, 'CMD');
        $this->AddToLog($output, 'RES');
    }

    /**
     * @event edit3.keyDown-Up 
     */
    function doEdit3KeyDownUp(UXKeyEvent $e = null)
    {    
        $this->edit3->text = UXClipboard::getText();
    }

    /**
     * @event edit3.keyDown-Enter 
     */
    function doEdit3KeyDownEnter(UXKeyEvent $e = null)
    {    
        $this->doButton19Action();
    }

    /**
     * @event listView7.action 
     */
    function doListView7Action(UXEvent $e = null)
    {    
        UXClipboard::setText($this->listView7->selectedItem);
    }




    /**
     * @event panelAlt.mouseEnter 
     */
    function doPanelAltMouseEnter(UXMouseEvent $e = null)
    {    
        $this->helper->text = 'Short information about device';
    }

    /**
     * @event button15.mouseEnter 
     */
    function doButton15MouseEnter(UXMouseEvent $e = null)
    {    
        $this->helper->text = 'Update list of connected devices';
    }

    /**
     * @event combobox3.mouseEnter 
     */
    function doCombobox3MouseEnter(UXMouseEvent $e = null)
    {    
        $this->helper->text = 'List of connected devices';
    }

    /**
     * @event spoiler.mouseEnter 
     */
    function doSpoilerMouseEnter(UXMouseEvent $e = null)
    {    
        $this->helper->text = 'Let`s do it!';
    }

    /**
     * @event button7.mouseEnter 
     */
    function doButton7MouseEnter(UXMouseEvent $e = null)
    {    
        $this->helper->text = 'Reboot selected device';
    }

    /**
     * @event button9.mouseEnter 
     */
    function doButton9MouseEnter(UXMouseEvent $e = null)
    {    
        $this->helper->text = 'Reboot selected device to Bootloader/Fastboot/EDL';
    }

    /**
     * @event button11.mouseEnter 
     */
    function doButton11MouseEnter(UXMouseEvent $e = null)
    {    
        $this->helper->text = 'Shutdown selected device';
    }

    /**
     * @event button10.mouseEnter 
     */
    function doButton10MouseEnter(UXMouseEvent $e = null)
    {    
        $this->helper->text = 'Suitable if power button is broken';
    }

    /**
     * @event button20.mouseEnter 
     */
    function doButton20MouseEnter(UXMouseEvent $e = null)
    {    
        $this->helper->text = 'Start remote control session for selected device, sessions is unlimited';
    }

    /**
     * @event listViewAlt.mouseEnter 
     */
    function doListViewAltMouseEnter(UXMouseEvent $e = null)
    {    
        $this->helper->text = 'Callback for operations with packages';
    }

    /**
     * @event panel3.mouseEnter 
     */
    function doPanel3MouseEnter(UXMouseEvent $e = null)
    {    
        $this->helper->text = 'Short information about app';
    }


    /**
     * @event button.mouseEnter 
     */
    function doButtonMouseEnter(UXMouseEvent $e = null)
    {    
        $this->helper->text = 'Refresh app`s list';
    }

    /**
     * @event buttonAlt.mouseEnter 
     */
    function doButtonAltMouseEnter(UXMouseEvent $e = null)
    {    
        $this->helper->text = 'Disable selected';
    }

    /**
     * @event button8.mouseEnter 
     */
    function doButton8MouseEnter(UXMouseEvent $e = null)
    {    
        $this->helper->text = 'Enable selected';
    }

    /**
     * @event button3.mouseEnter 
     */
    function doButton3MouseEnter(UXMouseEvent $e = null)
    {    
        $this->helper->text = 'Remove selected';
    }

    /**
     * @event button4.mouseEnter 
     */
    function doButton4MouseEnter(UXMouseEvent $e = null)
    {    
        $this->helper->text = 'Get an *.apk of one (or first) selected app';
    }


    /**
     * @event button28.mouseEnter 
     */
    function doButton28MouseEnter(UXMouseEvent $e = null)
    {    
        $this->helper->text = 'Save selected as Androcut Automatical Uninstall Script';
    }

    /**
     * @event helper.mouseExit 
     */
    function doHelperMouseExit(UXMouseEvent $e = null)
    {    
        $this->helper->text = '0-0?';
    }

    /**
     * @event helper.mouseEnter 
     */
    function doHelperMouseEnter(UXMouseEvent $e = null)
    {    
        $this->helper->text = '❤-❤';
    }

    /**
     * @event button33.mouseEnter 
     */
    function doButton33MouseEnter(UXMouseEvent $e = null)
    {    
        $this->helper->text = 'Refresh local repository';
    }

    /**
     * @event button34.mouseEnter 
     */
    function doButton34MouseEnter(UXMouseEvent $e = null)
    {    
        $this->helper->text = 'Add to repository an external script';
    }

    /**
     * @event button17.mouseEnter 
     */
    function doButton17MouseEnter(UXMouseEvent $e = null)
    {    
        $this->helper->text = 'Remove selected from repository';
    }

    /**
     * @event button43.mouseEnter 
     */
    function doButton43MouseEnter(UXMouseEvent $e = null)
    {    
        $this->helper->text = 'Refresh packages list (also refreshing main (Apps) screen with parameter [All])';
    }

    /**
     * @event listView6.mouseEnter 
     */
    function doListView6MouseEnter(UXMouseEvent $e = null)
    {    
        $this->helper->text = 'Double click - add to script';
    }

    /**
     * @event listView4.mouseEnter 
     */
    function doListView4MouseEnter(UXMouseEvent $e = null)
    {    
        $this->helper->text = 'Select to open';
    }

    /**
     * @event button37.mouseEnter 
     */
    function doButton37MouseEnter(UXMouseEvent $e = null)
    {    
        $this->helper->text = 'Save script';
    }

    /**
     * @event editAlt.mouseEnter 
     */
    function doEditAltMouseEnter(UXMouseEvent $e = null)
    {    
        $this->helper->text = 'Script name (type your own if you creating new one)';
    }

    /**
     * @event button36.mouseEnter 
     */
    function doButton36MouseEnter(UXMouseEvent $e = null)
    {    
        $this->helper->text = 'Add package name manual';
    }

    /**
     * @event button39.mouseEnter 
     */
    function doButton39MouseEnter(UXMouseEvent $e = null)
    {    
        $this->helper->text = 'Execute script with parameter Disable';
    }

    /**
     * @event button40.mouseEnter 
     */
    function doButton40MouseEnter(UXMouseEvent $e = null)
    {    
        $this->helper->text = 'Execute script with parameter Enable';
    }

    /**
     * @event button41.mouseEnter 
     */
    function doButton41MouseEnter(UXMouseEvent $e = null)
    {    
        $this->helper->text = 'Execute script with parameter Uninstall';
    }

    /**
     * @event button21.action 
     */
    function doButton21Action(UXEvent $e = null)
    {    
        app()->showForm('scripteditor');
    }

    /**
     * @event listView5.mouseEnter 
     */
    function doListView5MouseEnter(UXMouseEvent $e = null)
    {    
        $this->helper->text = 'Script (list of apps)';
    }

    /**
     * @event listView7.mouseEnter 
     */
    function doListView7MouseEnter(UXMouseEvent $e = null)
    {    
        $this->helper->text = 'Console output (click to copy)';
    }

    /**
     * @event edit3.mouseEnter 
     */
    function doEdit3MouseEnter(UXMouseEvent $e = null)
    {    
        $this->helper->text = 'Type command here, press [↑] for last command/paste from clipboard, [Enter] to execute';
    }

    /**
     * @event button19.mouseEnter 
     */
    function doButton19MouseEnter(UXMouseEvent $e = null)
    {    
        $this->helper->text = 'Are you sure?';
    }

    /**
     * @event button21.mouseEnter 
     */
    function doButton21MouseEnter(UXMouseEvent $e = null)
    {    
        $this->helper->text = 'Open script editor';
    }

    /**
     * @event listView.mouseMove 
     */
    function doListViewMouseMove(UXMouseEvent $e = null)
    {    
        
    }

    /**
     * @event button38.action 
     */
    function doButton38Action(UXEvent $e = null)
    {    
        $packages = $this->listView->items->toArray();
        $needle = $this->edit->text;
        
        $key = 0;
        
        foreach ($packages as $item)
        {
            $list[$key++] = $item->text;
        }
        
        $listcount = count($list);
        
        $i = 0;
        
        while ($i < $listcount)
        {
            if (str::contains($list[$i], $needle) != false)
            {
                $index = $i;
                break;
            }
            $i++;
        }
        
        $this->listView->selectedIndex = $index;
        $this->listView->scrollTo($index);
    }

    /**
     * @event edit.keyDown-Enter 
     */
    function doEditKeyDownEnter(UXKeyEvent $e = null)
    {    
        $this->doButton38Action();
    }

    /**
     * @event edit.keyPress 
     */
    function doEditKeyPress(UXKeyEvent $e = null)
    {    
        $this->doButton38Action();
    }

    /**
     * @event button35.action 
     */
    function doButton35Action(UXEvent $e = null)
    {    
        $key = $this->listView->selectedItem->text;
        open(htmlspecialchars("https://www.google.com/search?q= $key"), ENT_QUOTES);
    }

    /**
     * @event button46.action 
     */
    function doButton46Action(UXEvent $e = null)
    {    
        app()->showForm('wificon');
    }

    /**
     * @event edit.mouseEnter 
     */
    function doEditMouseEnter(UXMouseEvent $e = null)
    {    
        $this->helper->text = 'Search by package name, press [Enter] or Search button for deep search';
    }

    /**
     * @event button38.mouseEnter 
     */
    function doButton38MouseEnter(UXMouseEvent $e = null)
    {    
        $this->helper->text = 'Deep search';
    }

    /**
     * @event button22.mouseEnter 
     */
    function doButton22MouseEnter(UXMouseEvent $e = null)
    {    
        $this->helper->text = 'Clickable area below...';
    }

    /**
     * @event button23.mouseEnter 
     */
    function doButton23MouseEnter(UXMouseEvent $e = null)
    {    
        $this->helper->text = 'Clickable area below...';
    }

    /**
     * @event button46.mouseEnter 
     */
    function doButton46MouseEnter(UXMouseEvent $e = null)
    {    
        $this->helper->text = 'Connect device via local network';
    }



    /**
     * @event button47.action 
     */
    function doButton47Action(UXEvent $e = null)
    {
        app()->showForm('install');
    }

    /**
     * @event button47.mouseEnter 
     */
    function doButton47MouseEnter(UXMouseEvent $e = null)
    {
        $this->helper->text = 'Install application(s) from local *.apk file';
    }

    /**
     * @event tabPane.change 
     */
    function doTabPaneChange(UXEvent $e = null)
    {    
        
    }



    /**
     * @event checkbox6.click 
     */
    function doCheckbox6Click(UXMouseEvent $e = null)
    {    
        if ($this->checkbox6->selected == true)
        {
            $this->checkbox5->selected = false;
        }
    }

    /**
     * @event checkbox5.click 
     */
    function doCheckbox5Click(UXMouseEvent $e = null)
    {    
        if ($this->checkbox5->selected == true)
        {
            $this->checkbox6->selected = false;
        }
    }

    /**
     * @event checkbox4.click 
     */
    function doCheckbox4Click(UXMouseEvent $e = null)
    {    
        if ($this->checkbox4->selected == true)
        {
            $this->checkbox3->selected = false;
        }
    }

    /**
     * @event checkbox3.click 
     */
    function doCheckbox3Click(UXMouseEvent $e = null)
    {    
        if ($this->checkbox3->selected == true)
        {
            $this->checkbox4->selected = false;
        }
    }

    /**
     * @event checkboxAlt.click 
     */
    function doCheckboxAltClick(UXMouseEvent $e = null)
    {    
        if ($this->checkboxAlt->selected == true)
        {
            $this->checkbox3->enabled = false;
            $this->checkbox4->enabled = false;
            $this->checkbox5->enabled = false;
            $this->checkbox6->enabled = false;
            $this->checkbox3->selected = false;
            $this->checkbox4->selected = false;
            $this->checkbox5->selected = false;
            $this->checkbox6->selected = false;
        }
        else 
        {
            $this->checkbox3->enabled = true;
            $this->checkbox4->enabled = true;
            $this->checkbox5->enabled = true;
            $this->checkbox6->enabled = true;
        }
    }



    /**
     * @event checkbox.click 
     */
    function doCheckboxClick(UXMouseEvent $e = null)
    {
        if ($this->checkbox->selected == true)
        {
            $items = $this->listView->items->toArray();
            $this->listView->items->clear();
            
            foreach ($items as $item)
            {
                $item->selected = true;
                $this->listView->items->add($item);
            }
            
        }
        else
        {
            $items = $this->listView->items->toArray();
            $this->listView->items->clear();
            
            foreach ($items as $item)
            {
                $item->selected = false;
                $this->listView->items->add($item);
            }
        }
        $this->listView->selectedIndex = 0;
    }

    /**
     * @event listView.click 
     */
    function doListViewClick(UXMouseEvent $e = null)
    {    
        if ($this->listView->selectedItem->selected == true)
        {
            $this->listView->selectedItem->selected = false;
        }
        else 
        {
            $this->listView->selectedItem->selected = true;
        }
    }

    /**
     * @event showing 
     */
    function doShowing(UXWindowEvent $e = null)
    {    
        fs::delete('log.md5');
    }

    /**
     * @event keyDown-F11 
     */
    function doKeyDownF11(UXKeyEvent $e = null)
    {    
        $this->doButton13Action();
    }



    /**
     * @event keyDown-F1 
     */
    function doKeyDownF1(UXKeyEvent $e = null)
    {    
        $this->doButton25Action();
    }

    /**
     * @event button5.action 
     */
    function doButton5Action(UXEvent $e = null)
    {
        app()->showForm('settings');
    }

    /**
     * @event button5.mouseEnter 
     */
    function doButton5MouseEnter(UXMouseEvent $e = null)
    {
        $this->helper->text = 'Show information about app';
    }

    /**
     * @event button25.action 
     */
    function doButton25Action(UXEvent $e = null)
    {
        open('"https://samarin-dev.github.io/pub/page2.html"');
    }

    /**
     * @event button25.mouseEnter 
     */
    function doButton25MouseEnter(UXMouseEvent $e = null)
    {
        $this->helper->text = 'Get support';
    }

    /**
     * @event button44.action 
     */
    function doButton44Action(UXEvent $e = null)
    {
        app()->showForm('about');
    }

    /**
     * @event button44.mouseEnter 
     */
    function doButton44MouseEnter(UXMouseEvent $e = null)
    {
        $this->helper->text = 'Show information about app';
    }

    /**
     * @event button13.action 
     */
    function doButton13Action(UXEvent $e = null)
    {
        if ($this->fullScreen == true)
        {
            $this->fullScreen = false;
        }
        else 
        {
            $this->fullScreen = true;
        }
    }

    /**
     * @event button13.mouseEnter 
     */
    function doButton13MouseEnter(UXMouseEvent $e = null)
    {
        $this->helper->text = 'Workstation mode';
    }


    /**
     * @event keyDown-F5 
     */
    function doKeyDownF5(UXKeyEvent $e = null)
    {    
        $this->doButtonAction();
    }

    /**
     * @event keyDown-Ctrl+F5 
     */
    function doKeyDownCtrlF5(UXKeyEvent $e = null)
    {    
        $this->doButton15Action();
    }

    /**
     * @event spoilerAlt.mouseEnter 
     */
    function doSpoilerAltMouseEnter(UXMouseEvent $e = null)
    {
        $this->helper->text = 'Let`s do it!';
    }










    
    //UI/UX
    function showNotice()
    {
        $this->notice->show();
        $this->panel->hide();
    }
}
