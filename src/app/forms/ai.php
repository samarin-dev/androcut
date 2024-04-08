<?php
namespace app\forms;

use behaviour\custom\GaussianBlurEffectBehaviour;
use behaviour\custom\InnerShadowEffectBehaviour;
use behaviour\custom\DropShadowEffectBehaviour;
use php\gui\framework\AbstractForm;
use php\gui\event\UXEvent; 
use php\gui\event\UXMouseEvent; 
use php\gui\event\UXWindowEvent; 


class ai extends AbstractForm
{

    /**
     * @event mouseMove 
     */
    function doMouseMove(UXMouseEvent $e = null)
    {    
        $this->SFXcalc();
    }

    /**
     * @event show 
     */
    function doShow(UXWindowEvent $e = null)
    {    
        
    }

    /**
     * @event combobox.action 
     */
    function doComboboxAction(UXEvent $e = null)
    {    
        
    }

    /**
     * @event button.mouseEnter 
     */
    function doButtonMouseEnter(UXMouseEvent $e = null)
    {    
        
    }

    /**
     * @event button3.mouseEnter 
     */
    function doButton3MouseEnter(UXMouseEvent $e = null)
    {    
        $this->SFXcalc(0, 0);
    }
    
    function SFXcalc ($x = 1, $y = 1, $mColorElement = '#000000', $sColorElement = '#4d4d4d')
    {
        if ($x != 1)
        {
            $xin = $x;
            $yin = $y;
        }
        else 
        {
            $xin = $this->circle->x - $this->image3->x;
            $yin = $this->circle->y - $this->image3->y;
        }
        
        $dropShadowEffect = new DropShadowEffectBehaviour();
        $dropShadowEffect->radius = 40;
        $dropShadowEffect->color = $mColorElement;
        $dropShadowEffect->offsetX = $xin/(-20);
        $dropShadowEffect->offsetY = $yin/(-20);
        
        $dropShadowEffect2 = new DropShadowEffectBehaviour();
        $dropShadowEffect2->radius = 40;
        $dropShadowEffect2->color = $mColorElement;
        $dropShadowEffect2->offsetX = $xin/(-20);
        $dropShadowEffect2->offsetY = $yin/(-20);
        
        $dropShadowEffect3 = new DropShadowEffectBehaviour();
        $dropShadowEffect3->radius = 40;
        $dropShadowEffect3->color = $mColorElement;
        $dropShadowEffect3->offsetX = $xin/(-20);
        $dropShadowEffect3->offsetY = $yin/(-20);
        
        $dropShadowEffect4 = new DropShadowEffectBehaviour();
        $dropShadowEffect4->radius = 10;
        $dropShadowEffect4->color = $mColorElement;
        $dropShadowEffect4->offsetX = $xin/(-20);
        $dropShadowEffect4->offsetY = $yin/(-20);
        
        $InnerShadowEffect = new InnerShadowEffectBehaviour();
        $InnerShadowEffect->radius = 20;
        $InnerShadowEffect->color = $sColorElement;
        $InnerShadowEffect->offsetX = $xin/(-20);
        $InnerShadowEffect->offsetY = $yin/(-20);
        
        $blurEffect = new GaussianBlurEffectBehaviour();
        $blurEffect->radius = 63;
        
        $this->image3->effects->clear();
        $dropShadowEffect->apply($this->image3);
        $this->button3->effects->clear();
        $dropShadowEffect2->apply($this->button3);
        $this->combobox->effects->clear();
        $dropShadowEffect3->apply($this->combobox);
        $this->label->effects->clear();
        $dropShadowEffect4->apply($this->label);
        
        $this->panel->effects->clear();
        $InnerShadowEffect->apply($this->panel);
        $blurEffect->apply($this->panel);
    }



}
