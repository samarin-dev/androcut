<?php
namespace app\forms;

use php\gui\framework\AbstractForm;
use php\gui\event\UXEvent; 


class update extends AbstractForm
{

    /**
     * @event buttonAlt.action 
     */
    function doButtonAltAction(UXEvent $e = null)
    {    
        open(htmlspecialchars('https://github.com/samarin-dev/androcut/releases'), ENT_QUOTES);
    }

}
