<?php
namespace app\modules;

use localization;
use std, gui, framework, app;


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

}
