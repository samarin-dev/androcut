<?php
namespace app\forms;

use std, gui, framework, app;
use php\gui\event\UXEvent; 


class about extends AbstractForm
{

    /**
     * @event buttonAlt.action 
     */
    function doButtonAltAction(UXEvent $e = null)
    {    
        open('"http://t.me/androcut"');
    }

    /**
     * @event button3.action 
     */
    function doButton3Action(UXEvent $e = null)
    {
        open('"https://4pda.to/forum/index.php?showtopic=1081778"');
    }

}
