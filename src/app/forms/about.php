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

    /**
     * @event button4.action 
     */
    function doButton4Action(UXEvent $e = null)
    {
        open('"https://github.com/samarin-dev/androcut"');
    }

    /**
     * @event button5.action 
     */
    function doButton5Action(UXEvent $e = null)
    {
        open('"https://samarin-dev.github.io/pub/"');
    }

    /**
     * @event button6.action 
     */
    function doButton6Action(UXEvent $e = null)
    {
        open('"https://www.buymeacoffee.com/samarin.dev/introduction-2260066"');
    }

    /**
     * @event button7.action 
     */
    function doButton7Action(UXEvent $e = null)
    {
        open('"https://www.paypal.com/donate/?hosted_button_id=MB2VZZATFBMZ2"');
    }

    /**
     * @event button8.action 
     */
    function doButton8Action(UXEvent $e = null)
    {
        open('"https://send.monobank.ua/jar/AEFwnbzMFo"');
    }

    /**
     * @event button9.action 
     */
    function doButton9Action(UXEvent $e = null)
    {    
        app()->showForm('tps');
    }

    /**
     * @event button10.action 
     */
    function doButton10Action(UXEvent $e = null)
    {    
        open('"https://github.com/samarin-dev/androcut/blob/main/LICENSE"');
    }

    /**
     * @event button11.action 
     */
    function doButton11Action(UXEvent $e = null)
    {    
        $this->toast('Checking updates on server...');
        $this->form('MainForm')->getUpdate();
    }

}
