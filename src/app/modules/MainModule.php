<?php
namespace app\modules;

use localization;
use std, gui, framework, app;
use php\gui\framework\ScriptEvent; 


class MainModule extends AbstractModule
{

    /**
     * @event fileChooser.action 
     */
    function doFileChooserAction(ScriptEvent $e = null)
    {    
        $file = $this->fileChooser->file;
        $filename = $file->getName();
        fs::makeFile("scripts/$filename");
        fs::copy($file, "scripts/$filename");
    }

    /**
     * @event action 
     */
    function doAction(ScriptEvent $e = null)
    {    
        
    }

    /**
     * @event fileChooser4.action 
     */
    function doFileChooser4Action(ScriptEvent $e = null)
    {    
        $content = fs::get($this->fileChooser4->file);
        app()->form('scripteditor')->textArea->text = $content;
    }

    /**
     * @event fileChooser3.action 
     */
    function doFileChooser3Action(ScriptEvent $e = null)
    {    
        $content = app()->form('scripteditor')->textArea->text;
        fs::makeFile($this->fileChooser3->file);
        file_put_contents($this->fileChooser3->file, $content);
    }

}
