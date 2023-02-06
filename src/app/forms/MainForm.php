<?php
namespace app\forms;

use php\io\IOException;
use Error;
use std, gui, framework, app;
use php\gui\event\UXEvent; 


class MainForm extends AbstractForm
{
    
    //UI/UX
    /**
     * @event button3.action 
     */
    function doButton3Action(UXEvent $e = null)
    {    
        if (fs::size('ntc.cfg') > 0)
        {
            $packagename = $this->listView->selectedItem->text;
            $this->start("adb shell pm uninstall -k --user 0 $packagename", 'INF');
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
        $this->start("adb shell pm uninstall -k --user 0 $packagename", 'INF');
        
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
        //UI/UX
        $this->buttonAlt->enabled = false;
        $this->button8->enabled = false;
        $this->button3->enabled = false;
        $this->button4->enabled = false;
        
        //App`s filter
        $index = $this->radioGroup->selectedIndex;
        
        if ($index == 0)
        {
            $this->start('adb shell pm list packages');
        }
        elseif ($index == 1)
        {
            $this->start('adb shell pm list packages -d');
        }
        elseif ($index == 2)
        {
            $this->start('adb shell pm list packages -e');
        }
        elseif ($index == 3)
        {
            $this->start('adb shell pm list packages -s');
        }
        elseif ($index == 4)
        {
            $this->start('adb shell pm list packages -3');
        }
        else 
        {
            $this->toast('JavaFX UI Framework Error, please restart an app and contact developer!!!');
        }
        
        //Using "try{}" because on some devices with custom/bad ROM`s app can be crashed 
        try 
        {
            //Just UI, adding some branding to app
            //Here you can see is not all brands, will be updated soon
            $devbrand1 = (new Process ( explode(' ', 'adb shell getprop ro.product.brand')))->start()->getInput()->readFully();
            $devbrand2 = (new Process ( explode(' ', 'adb shell getprop ro.product.model')))->start()->getInput()->readFully();
            
            $devbrand1 = str_replace(PHP_EOL, '', $devbrand1);
            $devbrand2 = str_replace(PHP_EOL, '', $devbrand2);
            
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
            $devserial = (new Process(['./adb', 'get-serialno']))->start()->getInput()->readFully();
            
            $this->label7->text = $devserial;
        }
        catch (Error $e) {$this->toast($e);}
        
        try 
        {
            $devcpu = (new Process ( explode(' ', 'adb shell getprop ro.product.cpu.abilist')))->start()->getInput()->readFully();
            $this->label8->text = $devcpu;
        }
        catch (Error $e) {$this->toast($e);}
        
        try 
        {
            $devfota = (new Process ( explode(' ', 'adb shell getprop ro.fota.version')))->start()->getInput()->readFully();
            $this->linkAlt->text = $devfota;
        }
        catch (Error $e) {$this->toast($e);}
        
        try 
        {
            $devgms = (new Process ( explode(' ', 'adb shell getprop ro.com.google.gmsversion')))->start()->getInput()->readFully();
            $this->label10->text = $devgms;
        }
        catch (Error $e) {$this->toast($e);}
        
        try 
        {
            $devand = (new Process ( explode(' ', 'adb shell getprop ro.build.version.release')))->start()->getInput()->readFully();
            $this->label12->text = $devand;
        }
        catch (Error $e) {$this->toast($e);}
        
        try 
        {
            $devboot = (new Process ( explode(' ', 'adb shell getprop ro.bootloader')))->start()->getInput()->readFully();
            $this->label14->text = $devboot;
        }
        catch (Error $e) {$this->toast($e);}
        
        try 
        {
            $devsec = (new Process ( explode(' ', 'adb shell getprop ro.build.version.security_patch')))->start()->getInput()->readFully();
            $this->label16->text = $devsec;
        }
        catch (Error $e) {$this->toast($e);}
        
        //adb get-serialno - serial
        //adb shell get prop ro.product.model - model
        //adb shell get prop ro.product.brand - brand
        //adb shell get prop ro.product.cpu.abilist - cpu
        //adb shell get prop ro.com.google.gmsversion - gms
        //adb shell get prop ro.build.version.release - android
        //adb shell get prop ro.bootloader - bootloader
        //adb shell get prop ro.build.version.security_patch - security
    }

    
    //App disable
    /**
     * @event buttonAlt.action 
     */
    function doButtonAltAction(UXEvent $e = null)
    {    
        $packagename = $this->listView->selectedItem->text;
        $this->start("adb shell pm disable-user --user 0 $packagename", 'INF');
    }
    
    
    //App enable
    /**
     * @event button8.action 
     */
    function doButton8Action(UXEvent $e = null)
    {
        $packagename = $this->listView->selectedItem->text;
        $this->start("adb shell pm enable --user 0 $packagename", 'INF');
    }
    
    
    //UI/UX
    /**
     * @event button4.action 
     */
    function doButton4Action(UXEvent $e = null)
    {    
        $packagename = $this->listView->selectedItem->text;
        app()->showForm('dump');
        $this->start("adb shell pm path $packagename", 'INF');
        $this->button4->enabled = false;
    }
    
    
    //UI/UX

    /**
     * @event link.action 
     */
    function doLinkAction(UXEvent $e = null)
    {    
        $link = $this->link->text;
        open("https://www.google.com/search?q=$link");
    }

    /**
     * @event linkAlt.action 
     */
    function doLinkAltAction(UXEvent $e = null)
    {    
        $linkAlt = $this->linkAlt->text;
        open("https://www.google.com/search?q=$linkAlt");
    }


    /**
     * @event show 
     */
    function doShow(UXWindowEvent $e = null)
    {    
        if (fs::size('rnc.cfg') > 0)
        {
            $this->doButtonAction();
            $this->doButton25Action();
        }
        else 
        {
            app()->showForm('runonce');
            $this->hide();
        }
    }





    /**
     * @event button12.action 
     */
    function doButton12Action(UXEvent $e = null)
    {
       app()->showForm('exec');
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
        $this->start("adb shell input keyevent KEYCODE_POWER", 'INF');
    }

    /**
     * @event button13.action 
     */
    function doButton13Action(UXEvent $e = null)
    {
        $this->start("adb shell input keyevent KEYCODE_VOLUME_UP", 'INF');
    }

    /**
     * @event button14.action 
     */
    function doButton14Action(UXEvent $e = null)
    {
        $this->start("adb shell input keyevent KEYCODE_VOLUME_DOWN", 'INF');
    }

    /**
     * @event button11.action 
     */
    function doButton11Action(UXEvent $e = null)
    {
        $this->start("adb shell reboot -p", 'INF');
    }

    /**
     * @event button9.action 
     */
    function doButton9Action(UXEvent $e = null)
    {
        $this->start("adb shell reboot bootloader", 'INF');
    }

    /**
     * @event button7.action 
     */
    function doButton7Action(UXEvent $e = null)
    {
        $this->start("adb shell reboot", 'INF');
    }

    /**
     * @event button15.action 
     */
    function doButton15Action(UXEvent $e = null)
    {
        $this->start("adb shell input keyevent KEYCODE_CAMERA", 'INF');
    }

    /**
     * @event button16.action 
     */
    function doButton16Action(UXEvent $e = null)
    {
        $this->start("adb shell input keyevent KEYCODE_HOME", 'INF');
    }

    /**
     * @event button17.action 
     */
    function doButton17Action(UXEvent $e = null)
    {
        $this->start("adb shell input keyevent KEYCODE_BACK", 'INF');
    }

    /**
     * @event button18.action 
     */
    function doButton18Action(UXEvent $e = null)
    {
        $this->start("adb shell input keyevent KEYCODE_ASSIST", 'INF');
    }

    /**
     * @event button19.action 
     */
    function doButton19Action(UXEvent $e = null)
    {
        $this->start("adb shell input keyevent KEYCODE_MEDIA_PREVIOUS", 'INF');
    }

    /**
     * @event button20.action 
     */
    function doButton20Action(UXEvent $e = null)
    {
        $this->start("adb shell input keyevent KEYCODE_MEDIA_PLAY_PAUSE", 'INF');
    }

    /**
     * @event button21.action 
     */
    function doButton21Action(UXEvent $e = null)
    {
        $this->start("adb shell input keyevent KEYCODE_MEDIA_NEXT", 'INF');
    }

    /**
     * @event button22.action 
     */
    function doButton22Action(UXEvent $e = null)
    {
        $this->start("adb shell input keyevent KEYCODE_MENU", 'INF');
    }

    /**
     * @event button23.action 
     */
    function doButton23Action(UXEvent $e = null)
    {
        $this->start("adb shell input keyevent KEYCODE_CALL", 'INF');
    }

    /**
     * @event button24.action 
     */
    function doButton24Action(UXEvent $e = null)
    {
        $this->start("adb shell input keyevent KEYCODE_ENDCALL", 'INF');
    }

    /**
     * @event button25.action 
     */
    function doButton25Action(UXEvent $e = null)
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
            $devgsmbb = (new Process ( explode(' ', 'adb shell getprop gsm.project.baseband')))->start()->getInput()->readFully();
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
        
        try 
        {
            $devbootmode = (new Process ( explode(' ', 'adb shell getprop ro.boot.mode')))->start()->getInput()->readFully();
            $this->label42->text = $devbootmode;
        }
        catch (Error $e) {$this->toast($e);}
        
        try 
        {
            $devbdtype = (new Process ( explode(' ', 'adb shell getprop ro.build.user')))->start()->getInput()->readFully();
            $this->label44->text = $devbdtype;
        }
        catch (Error $e) {$this->toast($e);}
        
        try 
        {
            $devcrypto = (new Process ( explode(' ', 'adb shell getprop ro.crypto.state')))->start()->getInput()->readFully();
            $this->label46->text = $devcrypto;
        }
        catch (Error $e) {$this->toast($e);}
        
        try 
        {
            $devboard = (new Process ( explode(' ', 'adb shell getprop ro.product.board')))->start()->getInput()->readFully();
            $this->label48->text = $devboard;
        }
        catch (Error $e) {$this->toast($e);}
        
        try 
        {
            $devlocale = (new Process ( explode(' ', 'adb shell getprop ro.product.locale')))->start()->getInput()->readFully();
            $this->label50->text = $devlocale;
        }
        catch (Error $e) {$this->toast($e);}
        
        try 
        {
            $devrevision = (new Process ( explode(' ', 'adb shell getprop ro.revision')))->start()->getInput()->readFully();
            $this->label52->text = $devrevision;
        }
        catch (Error $e) {$this->toast($e);}
    }

    /**
     * @event keyDown-1 
     */
    function doKeyDown1(UXKeyEvent $e = null)
    {    
        $this->radioGroup->selectedIndex = 0;
    }

    /**
     * @event keyDown-Tab 
     */
    function doKeyDownTab(UXKeyEvent $e = null)
    {    
        if ($this->label55->visible == true)
        {
            $this->label55->hide();
            $this->label56->hide();
            $this->label57->hide();
            $this->label58->hide();
            $this->label59->hide();
            $this->label60->hide();
            $this->label61->hide();
            $this->label62->hide();
            $this->label63->hide();
            $this->label64->hide();
            $this->label65->hide();
            $this->label66->hide();
            $this->label67->hide();
            $this->label68->hide();
            $this->label69->hide();
            $this->label70->hide();
        }
        else 
        {
            $this->label55->show();
            $this->label56->show();
            $this->label57->show();
            $this->label58->show();
            $this->label59->show();
            $this->label60->show();
            $this->label61->show();
            $this->label62->show();
            $this->label63->show();
            $this->label64->show();
            $this->label65->show();
            $this->label66->show();
            $this->label67->show();
            $this->label68->show();
            $this->label69->show();
            $this->label70->show();
        }
    }

    /**
     * @event button26.action 
     */
    function doButton26Action(UXEvent $e = null)
    {
       app()->showForm('fullprops');
    }

    /**
     * @event keyDown-2 
     */
    function doKeyDown2(UXKeyEvent $e = null)
    {    
        $this->radioGroup->selectedIndex = 1;
    }

    /**
     * @event keyDown-3 
     */
    function doKeyDown3(UXKeyEvent $e = null)
    {    
        $this->radioGroup->selectedIndex = 2;
    }

    /**
     * @event keyDown-4 
     */
    function doKeyDown4(UXKeyEvent $e = null)
    {    
        $this->radioGroup->selectedIndex = 3;
    }

    /**
     * @event keyDown-5 
     */
    function doKeyDown5(UXKeyEvent $e = null)
    {    
        $this->radioGroup->selectedIndex = 4;
    }

    /**
     * @event keyDown-F5 
     */
    function doKeyDownF5(UXKeyEvent $e = null)
    {    
        $this->doButtonAction();
    }

    /**
     * @event keyDown-F6 
     */
    function doKeyDownF6(UXKeyEvent $e = null)
    {    
        $this->doButtonAltAction();
    }

    /**
     * @event keyDown-F7 
     */
    function doKeyDownF7(UXKeyEvent $e = null)
    {    
        $this->doButton8Action();
    }

    /**
     * @event keyDown-F8 
     */
    function doKeyDownF8(UXKeyEvent $e = null)
    {    
        $this->doButton3Action();
    }

    /**
     * @event keyDown-J 
     */
    function doKeyDownJ(UXKeyEvent $e = null)
    {    
        $this->doButton4Action();
    }

    /**
     * @event keyDown-F9 
     */
    function doKeyDownF9(UXKeyEvent $e = null)
    {    
        $this->doButton12Action();
    }

    /**
     * @event keyDown-R 
     */
    function doKeyDownR(UXKeyEvent $e = null)
    {    
        $this->doButton25Action();
    }

    /**
     * @event keyDown-F 
     */
    function doKeyDownF(UXKeyEvent $e = null)
    {    
        $this->doButton26Action();
    }

    /**
     * @event keyDown-I 
     */
    function doKeyDownI(UXKeyEvent $e = nIull)
    {    
        $this->tabPane->selectedIndex = 2;
    }

    /**
     * @event keyDown-C 
     */
    function doKeyDownC(UXKeyEvent $e = null)
    {    
        $this->tabPane->selectedIndex = 1;
    }

    /**
     * @event keyDown-A 
     */
    function doKeyDownA(UXKeyEvent $e = null)
    {    
        $this->tabPane->selectedIndex = 0;
    }

    /**
     * @event button27.action 
     */
    function doButton27Action(UXEvent $e = null)
    {
        open('https://github.com/samarin-dev/androcut');
    }

    /**
     * @event button28.action 
     */
    function doButton28Action(UXEvent $e = null)
    {
        open('https://github.com/samarin-dev/androcut/releases');
    }

    //UI/UX
    function showNotice()
    {
        $this->notice->show();
        $this->panel->hide();
    }
    
    //Starting process and getting output
    protected $process, $thread;
    
    public function start($command, $icotype = 'APK')
    {   
        $this->listView->items->clear();
        
        $this->process = new Process(explode(' ', $command));
        $this->process = $this->process->start();
        
        $this->thread = new Thread(function(){
            $this->process->getInput()->eachLine(function($line){
                uiLater(function() use ($line) {
                    $this->addConsole($line, '#FFFFFF', $icotype);
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
        
        $item = new UXLabelEx($line);
        $item->autoSize = true;
        $item->textColor = UXColor::of($color);
        
        //Icons for listView, not using now (bugs)
        if ($icontype == 'APK')
        {
            $img_icon = new UXImageView(new UXImage('res://.data/img/android.png'));                          
            $icon = new UXHBox([$img_icon]);
        }
        elseif ($icontype == 'ERR')
        {
            $img_icon = new UXImageView(new UXImage('res://.data/img/stop.png'));                          
            $icon = new UXHBox([$img_icon]);
        }
        elseif ($icontype == 'INF')
        {
            $img_icon = new UXImageView(new UXImage('res://.data/img/apply.png'));                          
            $icon = new UXHBox([$img_icon]);
        }
        
        $item->graphic = $icon;
        $item->autoSize = TRUE;
        $item->wrapText = TRUE;
        $this->listView->items->add($item);
        
        $this->listView->scrollTo($this->listView->items->count);
    }
}
