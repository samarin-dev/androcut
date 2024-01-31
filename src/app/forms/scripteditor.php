<?php
namespace app\forms;

use php\gui\framework\AbstractForm;
use php\gui\event\UXEvent; 
use php\gui\event\UXWindowEvent; 
use php\gui\event\UXMouseEvent; 
use php\gui\event\UXKeyEvent; 


class scripteditor extends AbstractForm
{

    /**
     * @event show 
     */
    function doShow(UXWindowEvent $e = null)
    {    
        $this->fileChooser3->actionNode = 'button3';
    }

    /**
     * @event buttonopen.mouseEnter 
     */
    function doButtonopenMouseEnter(UXMouseEvent $e = null)
    {    
        $this->button->text = 'Open existing file/script';
    }

    /**
     * @event scriptpath.mouseEnter 
     */
    function doScriptpathMouseEnter(UXMouseEvent $e = null)
    {    
        $this->button->text = 'Current path';
    }

    /**
     * @event buttonpath.mouseEnter 
     */
    function doButtonpathMouseEnter(UXMouseEvent $e = null)
    {    
        $this->button->text = 'Select another path/Save Us';
    }

    /**
     * @event buttonAlt.mouseEnter 
     */
    function doButtonAltMouseEnter(UXMouseEvent $e = null)
    {    
        $this->button->text = 'Save current file/script';
    }

    /**
     * @event textArea.mouseEnter 
     */
    function doTextAreaMouseEnter(UXMouseEvent $e = null)
    {    
        $this->button->text = 'Editor';
    }

    /**
     * @event button.mouseEnter 
     */
    function doButtonMouseEnter(UXMouseEvent $e = null)
    {    
        $this->button->text = 'Just an a helpbar';
    }

    /**
     * @event buttonAlt.action 
     */
    function doButtonAltAction(UXEvent $e = null)
    {    
        $content = $this->textArea->text;
        $filename = $this->scriptpath->text;
        file_put_contents($filename, $content);
        $this->toast("Saved to $filename");
    }

    /**
     * @event buttonopen.action 
     */
    function doButtonopenAction(UXEvent $e = null)
    {    
        
    }


    /**
     * @event numberField.click 
     */
    function doNumberFieldClick(UXMouseEvent $e = null)
    {    
        $this->slider->value = $this->numberField->value;
        $this->textArea->font->size = $this->slider->value;
    }

    /**
     * @event numberField.globalKeyDown-Enter 
     */
    function doNumberFieldGlobalKeyDownEnter(UXKeyEvent $e = null)
    {    
        $this->slider->value = $this->numberField->value;
        $this->textArea->font->size = $this->slider->value;
    }


    /**
     * @event numberField.mouseEnter 
     */
    function doNumberFieldMouseEnter(UXMouseEvent $e = null)
    {    
        $this->button->text = 'Font size';
    }

    /**
     * @event keyDown-Ctrl+S 
     */
    function doKeyDownCtrlS(UXKeyEvent $e = null)
    {    
        $this->doButtonAltAction();
    }

    /**
     * @event slider.mouseDrag 
     */
    function doSliderMouseDrag(UXMouseEvent $e = null)
    {    
        $this->numberField->value = $this->slider->value;
        $this->textArea->font->size = $this->numberField->value;
    }

    /**
     * @event slider.mouseEnter 
     */
    function doSliderMouseEnter(UXMouseEvent $e = null)
    {    
            $this->button->text = 'Font size';
    }





}
