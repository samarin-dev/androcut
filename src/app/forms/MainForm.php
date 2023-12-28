<?php
namespace app\forms;

use windows;
use php\io\IOException;
use Error;
use php\io\Stream;
use std, gui, framework, app;


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
        
        if (fs::size('ntc.cfg') > 0)
        {
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
        else 
        {
            $this->showNotice();
        }
    }
    
    //UI/UX
    /**
     * @event button5.action 
     */
    function doButton5Action(UXEvent $e = null)
    {    
        $this->panel->show();
        $this->notice->hide();
        
        $packagename = $this->listView->selectedItem->text;
        $this->start("adb shell pm uninstall -k --user 0 $packagename");
        
        if ($this->checkbox->selected == true)
        {
            fs::makeFile('ntc.cfg');
            file_put_contents('ntc.cfg', '1');
        }
    }
    
    //UI/UX
    /**
     * @event button6.action 
     */
    function doButton6Action(UXEvent $e = null)
    {    
        $this->panel->show();
        $this->notice->hide();
    }

    /**
     * @event button.action 
     */
    function doButtonAction(UXEvent $e = null)
    {    
        //App`s filter
        $index = $this->radioGroup->selectedIndex;
        
        //Device filter
        
        $deviceid = $this->combobox3->selected->text;
        $deviceid = explode(' ', $deviceid);
        $deviceid = str::trim($deviceid[0]);
        
        if ($index == 0)
        {
            $this->start("adb -s $deviceid shell pm list packages");
        }
        elseif ($index == 1)
        {
            $this->start("adb -s $deviceid shell pm list packages -d");
        }
        elseif ($index == 2)
        {
            $this->start("adb -s $deviceid shell pm list packages -e");
        }
        elseif ($index == 3)
        {
            $this->start("adb -s $deviceid shell pm list packages -s");
        }
        elseif ($index == 4)
        {
            $this->start("adb -s $deviceid shell pm list packages -3");
        }
        else 
        {
            $this->toast('JavaFX UI Framework Error, please restart an app and contact developer!!!');
        }
        
        //Using "try{}" because on some devices with custom/bad ROM`s app can be crashed 
        try 
        {
            //Just UI, adding some branding to app
            //How you can see is not all brands, will be updated soon
            $devbrand1 = (new Process ( explode(' ', "adb -s $deviceid shell getprop ro.product.brand")))->start()->getInput()->readFully();
            $devbrand2 = (new Process ( explode(' ', "adb -s $deviceid shell getprop ro.product.model")))->start()->getInput()->readFully();
            
            $devbrand1 = str_replace(PHP_EOL, '', strtoupper($devbrand1));
            $devbrand2 = str_replace(PHP_EOL, '', strtoupper($devbrand2));
            
            $this->link->text = "$devbrand1 $devbrand2";
            
            if ($devbrand1 == 'DOOGEE')
            {
                $brandlogo = new UXImage('res://.data/img/doogee.png');
            }
            else if ($devbrand1 == 'HUAWEI')
            {
                $brandlogo = new UXImage('res://.data/img/huawei.png');
            }
            else if ($devbrand1 == 'LENOVO')
            {
                $brandlogo = new UXImage('res://.data/img/lenovo.png');
            }
            else if ($devbrand1 == 'LG')
            {
                $brandlogo = new UXImage('res://.data/img/LG.png');
            }
            else if ($devbrand1 == 'XIAOMI')
            {
                $brandlogo = new UXImage('res://.data/img/mi.png');
            }
            else if ($devbrand1 == 'MOTOROLA')
            {
                $brandlogo = new UXImage('res://.data/img/motorola.png');
            }
            else if ($devbrand1 == 'SAMSUNG')
            {
                $brandlogo = new UXImage('res://.data/img/samsung.png');
            }
            else if ($devbrand1 == 'TECNO')
            {
                $brandlogo = new UXImage('res://.data/img/tecno.png');
            }
            else if ($devbrand1 == 'ASUS')
            {
                $brandlogo = new UXImage('res://.data/img/asus.png');
            }
            else if ($devbrand1 == 'NOTHING')
            {
                $brandlogo = new UXImage('res://.data/img/nothing.png');
            }
            else if ($devbrand1 == 'ZTE')
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
        open("https://www.google.com/search?q= $linkAlt");
    }


    /**
     * @event show 
     */
    function doShow(UXWindowEvent $e = null)
    {    
        $this->StartupCheck();
        if (fs::size('rnc.cfg') > 0)
        {
            $this->doButton15Action();
            $this->doButton33Action();
            
            $path = fs::abs('./');
            $this->listView7->items->add("Current application path - $path");
        }
        else 
        {
            app()->showForm('runonce');
            $this->hide();
        }
        
        $img_icon = new UXImageView(new UXImage('res://.data/img/dump.png'));
        $icon = new UXHBox([$img_icon]);
        $this->tabPane->selectFirstTab();
        $this->tabPane->selectedTab->graphic = $icon;
        
        $img_icon = new UXImageView(new UXImage('res://.data/img/android.png'));
        $icon = new UXHBox([$img_icon]);
        $this->tabPane->selectNextTab();
        $this->tabPane->selectedTab->graphic = $icon;
        
        $img_icon = new UXImageView(new UXImage('res://.data/img/edit.png'));
        $icon = new UXHBox([$img_icon]);
        $this->tabPane->selectNextTab();
        $this->tabPane->selectedTab->graphic = $icon;
        
        $img_icon = new UXImageView(new UXImage('res://.data/img/log.png'));
        $icon = new UXHBox([$img_icon]);
        $this->tabPane->selectNextTab();
        $this->tabPane->selectedTab->graphic = $icon;
        
        $this->tabPane->selectFirstTab();
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
        
        $line = $this->listView->selectedItem->text;
        
        if (str::contains($line, 'mediatek') == true) //here starts Frameworks    //-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-
        {
            $img_icon = new UXImage('res://.data/img/system.png');
            $this->label42->text = 'Part of: MTK Framework';
        }
        elseif (str::contains($line, 'mtk') == true)
        {
            $img_icon = new UXImage('res://.data/img/system.png');
            $this->label42->text = 'Part of: MTK Framework';
        }
        elseif (str::contains($line, 'sprd') == true)
        {
            $img_icon = new UXImage('res://.data/img/system.png');
            $this->label42->text = 'Part of: Spreadtrum Framework';
        }
        elseif (str::contains($line, 'spreadtrum') == true)
        {
            $img_icon = new UXImage('res://.data/img/system.png');
            $this->label42->text = 'Part of: Spreadtrum Framework';
        }
        elseif (str::contains($line, 'qualcomm') == true)
        {
            $img_icon = new UXImage('res://.data/img/system.png');
            $this->label42->text = 'Part of: Qualcomm Framework';
        }
        elseif (str::contains($line, 'qcom') == true)
        {
            $img_icon = new UXImage('res://.data/img/system.png');
            $this->label42->text = 'Part of: Qualcomm Framework';
        }
        elseif (str::contains($line, 'com.android') == true) //here starts GMS and Android    //-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-
        {
            $img_icon = new UXImage('res://.data/img/android-ser.png');
            $this->label42->text = 'Part of: Android OS';
        }
        elseif (str::contains($line, 'com.google') == true)
        {
            $img_icon = new UXImage('res://.data/img/google.png');
            $this->label42->text = 'Part of: Google MSF';
        }
        elseif (str::contains($line, 'transsion') == true) //here starts OEM/ODM    //-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-
        {
            $img_icon = new UXImage('res://.data/img/hios.png');
            $this->label42->text = 'Part of: HiOS OEM';
        }
        elseif (str::contains($line, 'zte') == true)
        {
            $img_icon = new UXImage('res://.data/img/zte-ser.png');
            $this->label42->text = 'Part of: ZTE OEM';
        }
        elseif (str::contains($line, 'miui') == true)
        {
            $img_icon = new UXImage('res://.data/img/miui.png');
            $this->label42->text = 'Part of: MIUI OEM';
        }
        elseif (str::contains($line, 'xiaomi') == true)
        {
            $img_icon = new UXImage('res://.data/img/miui.png');
            $this->label42->text = 'Part of: MIUI OEM';
        }
        elseif (str::contains($line, 'mi') == true)
        {
            $img_icon = new UXImage('res://.data/img/miui.png');
            $this->label42->text = 'Part of: MIUI OEM';
        }
        elseif (str::contains($line, 'oneplus') == true)
        {
            $img_icon = new UXImage('res://.data/img/oxygen-ser.png');
            $this->label42->text = 'Part of: OxygenOS OEM';
        }
        elseif (str::contains($line, 'meizu') == true)
        {
            $img_icon = new UXImage('res://.data/img/flyme-ser.png');
            $this->label42->text = 'Part of: FlymeOS OEM';
        }
        elseif (str::contains($line, 'samsung') == true)
        {
            $img_icon = new UXImage('res://.data/img/oneui-ser.png');
            $this->label42->text = 'Part of: OneUI OEM';
        }
        elseif (str::contains($line, 'sec') == true)
        {
            $img_icon = new UXImage('res://.data/img/oneui-ser.png');
            $this->label42->text = 'Part of: OneUI OEM';
        }
        else 
        {
            $img_icon = new UXImage('res://.data/img/android-ser.png');
            $this->label42->text = 'Part of: Unknown';
        }
        $this->imageAlt->image = $img_icon;
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
     * @event listView.mouseEnter 
     */
    function doListViewMouseEnter(UXMouseEvent $e = null)
    {    
        $total = $this->listView->items->count;
        $this->label41->text = "Total: $total";
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
        $this->toast('Main (Apps) Screen also reloaded with parameter [All]'); 
        $this->button42->text = 'Packages on device';
        $this->radioGroup->selectedIndex = 0;
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

    /**
     * @event tabPane.globalKeyPress-F1 
     */
    function doTabPaneGlobalKeyPressF1(UXKeyEvent $e = null)
    {    
        //just for tests
        
        $device = $this->combobox3->selected->text;
        $device = explode(' ', $device);
        $device = $device[0];
        $this->toast($device);
    }

    /**
     * @event button44.action 
     */
    function doButton44Action(UXEvent $e = null)
    {    
        app()->showForm('about');
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
        $partition = $this->combobox->selected;
        $filename = $this->edit->text;
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
        
        execute("scrcpy/scrcpy -s $deviceid");
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
     * @event button25.action 
     */
    function doButton25Action(UXEvent $e = null)
    {    
        open('manual.chm');
    }
    
    //UI/UX
    function showNotice()
    {
        $this->notice->show();
        $this->panel->hide();
    }
    
    //Starting process and getting output
    protected $process, $thread;
    
    public function start($command)
    {   
        $this->listView->items->clear();
        $this->listView6->items->clear();
        
        $this->process = new Process(explode(' ', $command));
        $this->process = $this->process->start();
        
        $this->thread = new Thread(function(){
            $this->process->getInput()->eachLine(function($line){
                uiLater(function() use ($line) {
                    $this->addConsole($line, '#FFFFFF');
                });
            });

            $this->process->getError()->eachLine(function($line){
                uiLater(function () use ($line) {
                    $this->addConsole($line, '#FFAAAA');
                }); 
            });
            
            $exitValue = $this->process->getExitValue();
            uiLater(function () use ($exitValue) {
              
            });
        });
        
        $this->thread->start();
    }
    
    
    //Addidng elements to listView
    protected function addConsole($line, $color = '#FFFFFF'){

        if(str::length(str::trim($line)) == 0)return; 
        
        $line = str_replace('package:', '', $line);
        
        if (str::contains($line, 'mediatek') == true) //here starts SoC    //-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-
        {
            $img_icon = new UXImageView(new UXImage('res://.data/img/system.png'));
        }
        elseif (str::contains($line, 'mtk') == true)
        {
            $img_icon = new UXImageView(new UXImage('res://.data/img/system.png'));
        }
        elseif (str::contains($line, 'sprd') == true)
        {
            $img_icon = new UXImageView(new UXImage('res://.data/img/system.png'));
        }
        elseif (str::contains($line, 'spreadtrum') == true)
        {
            $img_icon = new UXImageView(new UXImage('res://.data/img/system.png'));
        }
        elseif (str::contains($line, 'qualcomm') == true)
        {
            $img_icon = new UXImageView(new UXImage('res://.data/img/system.png'));
        }
        elseif (str::contains($line, 'qcom') == true)
        {
            $img_icon = new UXImageView(new UXImage('res://.data/img/system.png'));
        }
        elseif (str::contains($line, 'com.android') == true) //here starts GMS and Android    //-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-
        {
            $img_icon = new UXImageView(new UXImage('res://.data/img/android-ser.png'));
        }
        elseif (str::contains($line, 'com.google') == true)
        {
            $img_icon = new UXImageView(new UXImage('res://.data/img/google.png'));
        }
        elseif (str::contains($line, 'transsion') == true) //here starts OEM/ODM    //-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-
        {
            $img_icon = new UXImageView(new UXImage('res://.data/img/hios.png'));
        }
        elseif (str::contains($line, 'zte') == true)
        {
            $img_icon = new UXImageView(new UXImage('res://.data/img/zte-ser.png'));
        }
        elseif (str::contains($line, 'miui') == true)
        {
            $img_icon = new UXImageView(new UXImage('res://.data/img/miui.png'));
        }
        elseif (str::contains($line, 'xiaomi') == true)
        {
            $img_icon = new UXImageView(new UXImage('res://.data/img/miui.png'));
        }
        elseif (str::contains($line, 'mi') == true)
        {
            $img_icon = new UXImageView(new UXImage('res://.data/img/miui.png'));
        }
        elseif (str::contains($line, 'oneplus') == true)
        {
            $img_icon = new UXImageView(new UXImage('res://.data/img/oxygen-ser.png'));
        }
        elseif (str::contains($line, 'meizu') == true)
        {
            $img_icon = new UXImageView(new UXImage('res://.data/img/flyme-ser.png'));
        }
        elseif (str::contains($line, 'samsung') == true)
        {
            $img_icon = new UXImageView(new UXImage('res://.data/img/oneui-ser.png'));
        }
        elseif (str::contains($line, 'sec') == true)
        {
            $img_icon = new UXImageView(new UXImage('res://.data/img/oneui-ser.png'));
        }
        elseif (str::contains($line, 'ua.') == true) //here starts third-party software    //-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-
        {
            $img_icon = new UXImageView(new UXImage('res://.data/img/dia.png'));
        }
        elseif (str::contains($line, 'facebook') == true)
        {
            $img_icon = new UXImageView(new UXImage('res://.data/img/facebook.png'));
        }
        elseif (str::contains($line, 'instagram') == true)
        {
            $img_icon = new UXImageView(new UXImage('res://.data/img/instagram.png'));
        }
        elseif (str::contains($line, 'viber') == true)
        {
            $img_icon = new UXImageView(new UXImage('res://.data/img/viber-ser.png'));
        }
        elseif (str::contains($line, 'telegram') == true)
        {
            $img_icon = new UXImageView(new UXImage('res://.data/img/telegram-ser.png'));
        }
        elseif (str::contains($line, 'spotify') == true)
        {
            $img_icon = new UXImageView(new UXImage('res://.data/img/spotify-ser.png'));
        }
        elseif (str::contains($line, 'discord') == true)
        {
            $img_icon = new UXImageView(new UXImage('res://.data/img/discord-ser.png'));
        }
        elseif (str::contains($line, 'whatsapp') == true)
        {
            $img_icon = new UXImageView(new UXImage('res://.data/img/whatsapp-ser.png'));
        }
        elseif (str::contains($line, 'openai') == true)
        {
            $img_icon = new UXImageView(new UXImage('res://.data/img/openai-ser.png'));
        }
        elseif (str::contains($line, 'microsoft') == true)
        {
            $img_icon = new UXImageView(new UXImage('res://.data/img/microsoft-ser.png'));
        }
        elseif (str::contains($line, 'binance') == true)
        {
            $img_icon = new UXImageView(new UXImage('res://.data/img/binance-ser.png'));
        }
        elseif (str::contains($line, 'valve') == true)
        {
            $img_icon = new UXImageView(new UXImage('res://.data/img/steam-ser.png'));
        }
        elseif (str::contains($line, 'zhiliaoapp') == true)
        {
            $img_icon = new UXImageView(new UXImage('res://.data/img/tiktok-ser.png'));
        }
        elseif (str::contains($line, 'netflix') == true)
        {
            $img_icon = new UXImageView(new UXImage('res://.data/img/tiktok-ser.png'));
        }
        elseif (str::contains($line, 'amazon') == true)
        {
            $img_icon = new UXImageView(new UXImage('res://.data/img/amazon-ser.png'));
        }
        elseif (str::contains($line, 'reddit') == true)
        {
            $img_icon = new UXImageView(new UXImage('res://.data/img/reddit-ser.png'));
        }
        elseif (str::contains($line, 'twitter') == true)
        {
            $img_icon = new UXImageView(new UXImage('res://.data/img/x-ser.png'));
        }
        elseif (str::contains($line, 'gallery') == true)
        {
            $img_icon = new UXImageView(new UXImage('res://.data/img/gallery-ser.png'));
        }
        elseif (str::contains($line, 'photo') == true)
        {
            $img_icon = new UXImageView(new UXImage('res://.data/img/gallery-ser.png'));
        }
        elseif (str::contains($line, 'music') == true)
        {
            $img_icon = new UXImageView(new UXImage('res://.data/img/music-ser.png'));
        }
        elseif (str::contains($line, 'audio') == true)
        {
            $img_icon = new UXImageView(new UXImage('res://.data/img/music-ser.png'));
        }
        elseif (str::contains($line, 'video') == true)
        {
            $img_icon = new UXImageView(new UXImage('res://.data/img/video-ser.png'));
        }
        elseif (str::contains($line, 'player') == true)
        {
            $img_icon = new UXImageView(new UXImage('res://.data/img/video-ser.png'));
        }
        elseif (str::contains($line, 'launcher') == true)
        {
            $img_icon = new UXImageView(new UXImage('res://.data/img/launcher-ser.png'));
        }
        elseif (str::contains($line, 'theme') == true)
        {
            $img_icon = new UXImageView(new UXImage('res://.data/img/launcher-ser.png'));
        }
        elseif (str::contains($line, 'no.') == true)
        {
            $img_icon = new UXImageView(new UXImage('res://.data/img/norwey-ser.png'));
        }
        elseif (str::contains($line, 'pl.') == true)
        {
            $img_icon = new UXImageView(new UXImage('res://.data/img/pl-ser.png'));
        }
        elseif (str::contains($line, 'us.') == true)
        {
            $img_icon = new UXImageView(new UXImage('res://.data/img/us-ser.png'));
        }
        elseif (str::contains($line, 'uk.') == true)
        {
            $img_icon = new UXImageView(new UXImage('res://.data/img/uk-ser.png'));
        }
        elseif (str::contains($line, 'ro.') == true)
        {
            $img_icon = new UXImageView(new UXImage('res://.data/img/ro-ser.png'));
        }
        elseif (str::contains($line, 'cz.') == true)
        {
            $img_icon = new UXImageView(new UXImage('res://.data/img/cz-ser.png'));
        }
        else 
        {
            $img_icon = new UXImageView(new UXImage('res://.data/img/material.png'));
        }
        
        $item = new UXCheckbox;
        $item->autoSize = true;
        $item->textColor = UXColor::of($color);                          
        $icon = new UXHBox([$img_icon]);
        $item->text = $line;
        $item->graphic = $icon;
        $item->autoSize = TRUE;
        $item->wrapText = TRUE;
        $item->rightAnchor = 1;
        $item->leftAnchor = 1;
        $this->listView->items->add($item);
        $this->listView->scrollTo($this->listView->items->count);
        $item_alt = $item->text;
        $this->listView6->items->add($item_alt);
        $this->listView6->scrollTo($this->listView6->items->count);
    }
    
    function ADBAction ($action)
    {   
        try 
        {
            $output = (new Process ( explode(' ', "$action")))->start()->getInput()->readFully();
        }
        catch (IOException $e) {$this->toast($e);}
        
        $fxout = new UXLabelEx;
        $fxout->rightAnchor = 1;
        $fxout->leftAnchor = 1;
        $fxout->text = $output;
        $fxout->textColor = UXColor::of('#ffff4d');
        $img_icon = new UXImageView(new UXImage('res://.data/img/device.png'));                          
        $icon = new UXHBox([$img_icon]);
        $fxout->graphic = $icon;
        
        $this->listViewAlt->items->add($fxout);
        $this->listView6->items->add($output);
        $this->listViewAlt->scrollTo($this->listViewAlt->items->count);
        $this->listView6->scrollTo($this->listView6->items->count);
    }
    
    function FastbootAction($action)
    {
        try 
        {
            $output = (new Process ( explode(' ', "$action")))->start()->getInput()->readFully();
        }
        catch (IOException $e) {$this->toast($e);}
        
        $fxout = new UXLabelEx;
        $fxout->rightAnchor = 1;
        $fxout->leftAnchor = 1;
        $fxout->text = $output;
        $fxout->textColor = UXColor::of('#ffff4d');
        $img_icon = new UXImageView(new UXImage('res://.data/img/android.png'));                          
        $icon = new UXHBox([$img_icon]);
        $fxout->graphic = $icon;
        
        $this->listView3->items->add($fxout);
        $this->listView3->scrollTo($this->listView3->items->count());
    }
    
    function StartupCheck($ignore = false)
    {
        $os = strtolower(Windows::getProductName());
        
        if (str::contains($os, 'windows') == true)
        {
            if (fs::exists('adb.exe') == false) //checkup for Android Platform Tools
            {
                $this->ErrorToast('red', 'adb.exe', $ignore);
            }
            elseif (fs::exists('AdbWinApi.dll') == false)
            {
                $this->ErrorToast('red', 'AdbWinApi.dll', $ignore);
            }
            elseif (fs::exists('AdbWinUsbApi.dll') == false)
            {
                $this->ErrorToast('red', 'AdbWinUsbApi.dll', $ignore);
            }
            elseif (fs::exists('fastboot.exe') == false)
            {
                $this->ErrorToast('red', 'fastboot.exe', $ignore);
            }
            elseif (fs::exists('dmtracedump.exe') == false)
            {
                $this->ErrorToast('red', 'fastboot.exe', $ignore);
            }
            elseif (fs::exists('etc1tool.exe') == false)
            {
                $this->ErrorToast('red', 'etc1tool.exe', $ignore);
            }
            elseif (fs::exists('hprof-conv.exe') == false)
            {
                $this->ErrorToast('red', 'hprof-conv.exe', $ignore);
            }
            elseif (fs::exists('libwinpthread-1.dll') == false)
            {
                $this->ErrorToast('red', 'libwinpthread-1.dll', $ignore);
            }
            elseif (fs::exists('make_f2fs.exe') == false)
            {
                $this->ErrorToast('red', 'make_f2fs.exe', $ignore);
            }
            elseif (fs::exists('make_f2fs_casefold.exe') == false)
            {
                $this->ErrorToast('red', 'make_f2fs_casefold.exe', $ignore);
            }
            elseif (fs::exists('mke2fs.exe') == false)
            {
                $this->ErrorToast('red', 'mke2fs.exe', $ignore);
            }
            elseif (fs::exists('sqlite3.exe') == false)
            {
                $this->ErrorToast('red', 'sqlite3.exe', $ignore);
            }
            elseif (fs::exists('scrcpy/scrcpy.exe') == false) //third-party software
            {
                $this->ErrorToast('ok', 'scrcpy/scrcpy.exe', $ignore);
            }
            elseif (fs::exists('scrcpy/scrcpy-server') == false)
            {
                $this->ErrorToast('ok', 'scrcpy/scrcpy-server', $ignore);
            }
            elseif (fs::exists('scrcpy/adb.exe') == false)
            {
                $this->ErrorToast('ok', 'scrcpy/adb.exe', $ignore);
            }
            elseif (fs::exists('scrcpy/AdbWinApi.dll') == false)
            {
                $this->ErrorToast('ok', 'scrcpy/AdbWinApi.dll', $ignore);
            }
            elseif (fs::exists('scrcpy/AdbWinUsbApi.dll') == false)
            {
                $this->ErrorToast('ok', 'scrcpy/AdbWinUsbApi.dll', $ignore);
            }
            elseif (fs::exists('scrcpy/avcodec-60.dll') == false)
            {
                $this->ErrorToast('ok', 'scrcpy/avcodec-60.dll', $ignore);
            }
            elseif (fs::exists('scrcpy/avformat-60.dll') == false)
            {
                $this->ErrorToast('ok', 'scrcpy/avformat-60.dll', $ignore);
            }
            elseif (fs::exists('scrcpy/avutil-58.dll') == false)
            {
                $this->ErrorToast('ok', 'scrcpy/avutil-58.dll', $ignore);
            }
            elseif (fs::exists('scrcpy/libusb-1.0.dll') == false)
            {
                $this->ErrorToast('ok', 'scrcpy/libusb-1.0.dll', $ignore);
            }
            elseif (fs::exists('scrcpy/SDL2.dll') == false)
            {
                $this->ErrorToast('ok', 'scrcpy/SDL2.dll', $ignore);
            }
            elseif (fs::exists('scrcpy/swresample-4.dll') == false)
            {
                $this->ErrorToast('ok', 'scrcpy/swresample-4.dll', $ignore);
            }
        }
    }
    
    function ErrorToast($code, $path, $ignore = false)
    {
        if ($ignore == true)
        {
            app()->showForm('MainForm');
            app()->hideForm('error');
        }
        else 
        {
            if ($code == 'red')
            {
                $this->hide();
                app()->showForm('error');
                $this->form('error')->textArea->text = "Some critical parts of Android SDK Platform Tools couldn`t be found: $path. We strongly recommend you to reinstall an app and contact developer for bug-report and support.";
            }
            elseif ($code == 'ok')
            {
                $this->hide();
                app()->showForm('error');
                $this->form('error')->textArea->text = "Some third-party parts of Androcut Software Kit couldn`t be found: $path. Functionallity might be not full. We strongly recommend you to reinstall an app and contact developer for bug-report and support.";
            }
        }
        
    }
    
    function localization($locale = 'en-US')
    {
        if ($locale == 'uk-UA')
        {
            $this->tabPane->selectFirstTab();
            $this->tabPane->selectedTab->text = 'Застосунки (Ручне)';
            $this->tabPane->selectNextTab();
            $this->tabPane->selectedTab->text = 'Fastboot (Ручне)';
            $this->tabPane->selectNextTab();
            $this->tabPane->selectedTab->text = 'Скрипти';
            $this->tabPane->selectNextTab();
            $this->tabPane->selectedTab->text = 'Консоль (Ручне)';
            $this->radioGroup->items->clear();
            $this->radioGroup->items->add('Усі');
            $this->radioGroup->items->add('Вимкнуті');
            $this->radioGroup->items->add('Увімкнуті');
            $this->radioGroup->items->add('Системні');
            $this->radioGroup->items->add('Сторонні');
        }
    }
}