<?php
namespace app\forms;

use php\gui\framework\AbstractForm;
use php\gui\event\UXEvent; 


class tps extends AbstractForm
{

    /**
     * @event buttonAlt.action 
     */
    function doButtonAltAction(UXEvent $e = null)
    {    
        open(htmlspecialchars("https://www.java.com/en/"), ENT_QUOTES);
    }

    /**
     * @event button.action 
     */
    function doButtonAction(UXEvent $e = null)
    {    
        open(htmlspecialchars("https://www.oracle.com/legal/copyright.html"), ENT_QUOTES);
    }

    /**
     * @event button3.action 
     */
    function doButton3Action(UXEvent $e = null)
    {
        open(htmlspecialchars("https://developer.android.com/tools/releases/platform-tools"), ENT_QUOTES);
    }

    /**
     * @event button4.action 
     */
    function doButton4Action(UXEvent $e = null)
    {
        open(htmlspecialchars("https://developer.android.com/license"), ENT_QUOTES);
    }

    /**
     * @event button5.action 
     */
    function doButton5Action(UXEvent $e = null)
    {
        open(htmlspecialchars("https://github.com/Genymobile/scrcpy"), ENT_QUOTES);
    }

    /**
     * @event button6.action 
     */
    function doButton6Action(UXEvent $e = null)
    {
        open(htmlspecialchars("https://github.com/Genymobile/scrcpy/blob/master/LICENSE"), ENT_QUOTES);
    }

    /**
     * @event button7.action 
     */
    function doButton7Action(UXEvent $e = null)
    {
        open(htmlspecialchars("https://fonts.google.com/"), ENT_QUOTES);
    }

    /**
     * @event button8.action 
     */
    function doButton8Action(UXEvent $e = null)
    {
        open(htmlspecialchars("https://policies.google.com/terms"), ENT_QUOTES);
    }

}
