<?php
namespace app\forms;

use std, gui, framework, app;


class about extends AbstractForm
{

    /**
     * @event buttonAlt.action 
     */
    function doButtonAltAction(UXEvent $e = null)
    {    
        open('http://t.me/androcut');
    }

}
