<?php
namespace app\forms;

use std, gui, framework, app;


class error extends AbstractForm
{

    /**
     * @event button4.action 
     */
    function doButton4Action(UXEvent $e = null)
    {    
        app()->shutdown();
    }


}
