<?php
namespace app\forms;

use std, gui, framework, app;


class add extends AbstractForm
{

    /**
     * @event button.action 
     */
    function doButtonAction(UXEvent $e = null)
    {    
        $item = new UXCheckbox;
        $item->autoSize = true;
        $item->textColor = UXColor::of($color);                          
        $item->text = $this->edit->text;
        $item->autoSize = TRUE;
        $item->wrapText = TRUE;
        $item->rightAnchor = 1;
        $item->leftAnchor = 1;
        $item->selected = true;
        $this->form('MainForm')->listView5->items->add($item);
        $this->form('add')->hide();
    }

    /**
     * @event edit.keyDown-Enter 
     */
    function doEditKeyDownEnter(UXKeyEvent $e = null)
    {    
        $this->doButtonAction();
    }

}
