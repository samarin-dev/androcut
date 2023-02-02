<?php
namespace app\forms;

use std, gui, framework, app;


class runonce extends AbstractForm
{

    /**
     * @event button.action 
     */
    function doButtonAction(UXEvent $e = null)
    {    
        $this->tabPane->selectNextTab();
        $this->progressBar->progress = 30;
    }

    /**
     * @event button3.action 
     */
    function doButton3Action(UXEvent $e = null)
    {    
        $this->tabPane->selectNextTab();
        $this->progressBar->progress = 70;
    }

    /**
     * @event button4.action 
     */
    function doButton4Action(UXEvent $e = null)
    {    
        $this->tabPane->selectNextTab();
        $this->progressBar->progress = 100;
    }

    /**
     * @event buttonAlt.action 
     */
    function doButtonAltAction(UXEvent $e = null)
    {    
        fs::makeFile('rnc.cfg');
        file_put_contents('rnc.cfg', '1');
        app()->showForm('MainForm');
        $this->hide();
    }

    /**
     * @event tabPane.change 
     */
    function doTabPaneChange(UXEvent $e = null)
    {    
        if ($this->tabPane->selectedIndex == 0)
        {
            $this->progressBar->progress = 0;
        }
        elseif ($this->tabPane->selectedIndex == 1)
        {
            $this->progressBar->progress = 30;
        }
        elseif ($this->tabPane->selectedIndex == 2)
        {
            $this->progressBar->progress = 70;
        }
        elseif ($this->tabPane->selectedIndex == 3)
        {
            $this->progressBar->progress = 100;
        }
    }

    /**
     * @event button5.action 
     */
    function doButton5Action(UXEvent $e = null)
    {    
        fs::makeFile('rnc.cfg');
        file_put_contents('rnc.cfg', '1');
        app()->showForm('MainForm');
        $this->hide();
    }

}
