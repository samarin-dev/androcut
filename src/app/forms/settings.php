<?php
namespace app\forms;

use php\gui\UXClipboard;
use php\gui\layout\UXHBox;
use php\gui\UXImage;
use php\gui\UXImageView;
use php\gui\paint\UXColor;
use php\gui\UXLabelEx;
use php\lib\str;
use php\gui\framework\AbstractForm;
use php\gui\event\UXEvent; 
use php\lib\fs;
use php\gui\event\UXMouseEvent; 

class settings extends AbstractForm
{
    /**
     * @event button3.action 
     */
    function doButton3Action(UXEvent $e = null)
    {    
        $logfile = explode("\n", fs::get('log.md5'));
        
        $this->listView->items->clear();
        
        foreach ($logfile as $logstr)
        {
            /*$rval[0] = "\r";
            $rval[1] = "\n";
            $logstr = str::replace("\n", null, $logstr);*/
            
            $fxout = new UXLabelEx;
            $fxout->rightAnchor = 1;
            $fxout->leftAnchor = 1;
            $fxout->text = $logstr;
            
            if (str::contains($logstr, ':::'))
            {
                $fxout->css('-fx-text-fill','#e64d4d');
                $img_icon = new UXImageView(new UXImage('res://.data/img/break.png'));                          
                $icon = new UXHBox([$img_icon]);
                $fxout->graphic = $icon;
            }
            elseif (str::contains($logstr, '>'))
            {
                $fxout->css('-fx-text-fill','#b3b31a');
                $img_icon = new UXImageView(new UXImage('res://.data/img/terminal.png'));                          
                $icon = new UXHBox([$img_icon]);
                $fxout->graphic = $icon;
            }
            elseif (str::contains($logstr, '|'))
            {
                $fxout->css('-fx-text-fill','#80b380');
                $img_icon = new UXImageView(new UXImage('res://.data/img/android-ser.png'));                          
                $icon = new UXHBox([$img_icon]);
                $fxout->graphic = $icon;
            }
            elseif (str::contains($logstr, '*'))
            {
                $fxout->css('-fx-text-fill','#cccccc');
                $img_icon = new UXImageView(new UXImage('res://.data/img/click.png'));                          
                $icon = new UXHBox([$img_icon]);
                $fxout->graphic = $icon;
            }
            elseif (str::contains($logstr, '.::'))
            {
                $fxout->css('-fx-text-fill','#6680e6');
                $img_icon = new UXImageView(new UXImage('res://.data/img/info.png'));                          
                $icon = new UXHBox([$img_icon]);
                $fxout->graphic = $icon;
            }
            
            $this->listView->items->add($fxout);
            $this->listView->scrollTo($this->listView->items->count());
        }
    }

    /**
     * @event buttonAlt.action 
     */
    function doButtonAltAction(UXEvent $e = null)
    {    
        if ($this->checkbox->selected == true)
        {
            $bg = 1;
        }
        else 
        {
            $bg = 0;
        }
        
        if ($this->checkboxAlt->selected == true)
        {
            $fullscrn = 1;
        }
        else 
        {
            $fullscrn = 0;
        }
        
        if ($this->checkbox3->selected == true)
        {
            $ai = 1;
        }
        else 
        {
            $ai = 0;
        }
        
        if ($this->checkbox4->selected == true)
        {
            $simpleui = 1;
        }
        else 
        {
            $simpleui = 0;
        }
        
        if ($this->checkbox5->selected == true)
        {
            $userdb = 1;
        }
        else 
        {
            $userdb = 0;
        }
        
        $langid = $this->combobox4->selectedIndex;
        $userdbpath = $this->edit->text;
        
        $this->cfg->set('LocaleID', $langid, 'GLOBAL');
        $this->cfg->set('StartFullScreen', $fullscrn, 'GLOBAL');
        $this->cfg->set('NoBg', $bg, 'GLOBAL');
        $this->cfg->set('Lite', $simpleui, 'GLOBAL');
        $this->cfg->set('AIMode', $ai, 'GLOBAL');
        $this->cfg->set('UsrDB', $userdb, 'GLOBAL');
        $this->cfg->set('UsrDBPath', $userdbpath, 'GLOBAL');
        
        $this->cfg->save();
        
        $this->PrepUI();
        
        app()->hideForm('settings');
    }

    /**
     * @event button.action 
     */
    function doButtonAction(UXEvent $e = null)
    {    
        app()->hideForm('settings');
    }

    /**
     * @event panel6.click 
     */
    function doPanel6Click(UXMouseEvent $e = null)
    {    
        
    }

    /**
     * @event panel7.click 
     */
    function doPanel7Click(UXMouseEvent $e = null)
    {    
        
    }

    /**
     * @event panel8.click 
     */
    function doPanel8Click(UXMouseEvent $e = null)
    {    
        
    }

    /**
     * @event checkbox5.click 
     */
    function doCheckbox5Click(UXMouseEvent $e = null)
    {    
        if ($this->checkbox5->selected == true)
        {
            $this->panel8->enabled = true;
        }
        else 
        {
            $this->panel8->enabled = false;
        }
    }

    /**
     * @event button5.action 
     */
    function doButton5Action(UXEvent $e = null)
    {    
        open("log.md5");
    }

    /**
     * @event button4.action 
     */
    function doButton4Action(UXEvent $e = null)
    {    
        $items = $this->listView->items->toArray();
        $i = 0;
        
        foreach ($items as $item)
        {
            $log[$i] = $item->text;
            $i++;
        }
        
        $log = implode("\r\n ", $log);
        
        UXClipboard::setText($log);
    }



}
