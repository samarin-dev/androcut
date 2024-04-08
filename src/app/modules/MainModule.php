<?php
namespace app\modules;

use php\gui\effect\UXInnerShadowEffect;
use behaviour\custom\InnerShadowEffectBehaviour;
use app\modules\MainModule;
use app\forms\add;
use action\Element;
use app\forms\MainForm;
use php\io\IOException;
use localization;
use std, gui, framework, app;
use php\gui\framework\ScriptEvent; 

class MainModule extends AbstractModule
{
    protected $process, $thread;

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
        $this->getUpdate();
    }

    /**
     * @event fileChooser4.action 
     */
    function doFileChooser4Action(ScriptEvent $e = null)
    {    
        $content = fs::get($this->fileChooser4->file);
        $this->form('scripteditor')->textArea->text = $content;
    }

    /**
     * @event fileChooser3.action 
     */
    function doFileChooser3Action(ScriptEvent $e = null)
    {    
        $content = $this->form('scripteditor')->textArea->text;
        fs::makeFile($this->fileChooser3->file);
        file_put_contents($this->fileChooser3->file, $content);
    }
    
    /**
    * Starting process and getting output
    * @string $command - Command to Execute
    */
    public function start($command)
    {   
        $this->form('MainForm')->listView->items->clear();
        $this->form('MainForm')->listView6->items->clear();
        
        $this->process = new Process(explode(' ', $command));
        $this->process = $this->process->start();
        
        $this->thread = new Thread(function(){
            $this->process->getInput()->eachLine(function($line){
                uiLater(function() use ($line) {
                    $this->addConsole($line, '#FFFFFF');
                });
            });

            $this->process->getError()->eachLine(function($line){
                uiLater(function () use ($line) {
                    $this->addConsole($line, '#FFAAAA');
                }); 
            });
            
            $exitValue = $this->process->getExitValue();
            uiLater(function () use ($exitValue) {
              
            });
        });
        
        $this->thread->start();
        
        $this->AddToLog("$command",'CMD');
    }
    
    
    /**
    * Adding Elements to listView
    * @string Line to add
    * @string Line color in HTML format (like #ffffff)
    */
    protected function addConsole($line, $color = '#FFFFFF'){

        if(str::length(str::trim($line)) == 0)return; 
        
        $line = str_replace('package:', '', $line);
        
        $item = new UXCheckbox;
        $item->autoSize = true;
        
        if (str::contains($line, 'mediatek') == true) //here starts SoC    //-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-
        {
            $img_icon = new UXImageView(new UXImage('res://.data/img/system.png'));
            $item->css('-fx-text-fill','#e64d4d');
        }
        elseif (str::contains($line, 'mtk') == true)
        {
            $img_icon = new UXImageView(new UXImage('res://.data/img/system.png'));
            $item->css('-fx-text-fill','#e64d4d');
        }
        elseif (str::contains($line, 'sprd') == true)
        {
            $img_icon = new UXImageView(new UXImage('res://.data/img/system.png'));
            $item->css('-fx-text-fill','#e64d4d');
        }
        elseif (str::contains($line, 'spreadtrum') == true)
        {
            $img_icon = new UXImageView(new UXImage('res://.data/img/system.png'));
            $item->css('-fx-text-fill','#e64d4d');
        }
        elseif (str::contains($line, 'qualcomm') == true)
        {
            $img_icon = new UXImageView(new UXImage('res://.data/img/system.png'));
            $item->css('-fx-text-fill','#e64d4d');
        }
        elseif (str::contains($line, 'qcom') == true)
        {
            $img_icon = new UXImageView(new UXImage('res://.data/img/system.png'));
            $item->css('-fx-text-fill','#e64d4d');
        }
        elseif (str::contains($line, 'com.android') == true) //here starts GMS and Android    //-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-
        {
            $img_icon = new UXImageView(new UXImage('res://.data/img/android-ser.png'));
            $item->css('-fx-text-fill','#b3b31a');
        }
        elseif (str::contains($line, 'com.google') == true)
        {
            $img_icon = new UXImageView(new UXImage('res://.data/img/google.png'));
            $item->css('-fx-text-fill','#b3b31a');
        }
        elseif (str::contains($line, 'transsion') == true) //here starts OEM/ODM    //-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-
        {
            $img_icon = new UXImageView(new UXImage('res://.data/img/hios.png'));
            $item->css('-fx-text-fill','#6680e6');
        }
        elseif (str::contains($line, 'zte') == true)
        {
            $img_icon = new UXImageView(new UXImage('res://.data/img/zte-ser.png'));
            $item->css('-fx-text-fill','#6680e6');
        }
        elseif (str::contains($line, 'miui') == true)
        {
            $img_icon = new UXImageView(new UXImage('res://.data/img/miui.png'));
            $item->css('-fx-text-fill','#6680e6');
        }
        elseif (str::contains($line, 'xiaomi') == true)
        {
            $img_icon = new UXImageView(new UXImage('res://.data/img/miui.png'));
            $item->css('-fx-text-fill','#6680e6');
        }
        elseif (str::contains($line, 'mi') == true)
        {
            $img_icon = new UXImageView(new UXImage('res://.data/img/miui.png'));
            $item->css('-fx-text-fill','#6680e6');
        }
        elseif (str::contains($line, 'oneplus') == true)
        {
            $img_icon = new UXImageView(new UXImage('res://.data/img/oxygen-ser.png'));
            $item->css('-fx-text-fill','#6680e6');
        }
        elseif (str::contains($line, 'meizu') == true)
        {
            $img_icon = new UXImageView(new UXImage('res://.data/img/flyme-ser.png'));
            $item->css('-fx-text-fill','#6680e6');
        }
        elseif (str::contains($line, 'samsung') == true)
        {
            $img_icon = new UXImageView(new UXImage('res://.data/img/oneui-ser.png'));
            $item->css('-fx-text-fill','#6680e6');
        }
        elseif (str::contains($line, 'sec') == true)
        {
            $img_icon = new UXImageView(new UXImage('res://.data/img/oneui-ser.png'));
            $item->css('-fx-text-fill','#6680e6');
        }
        elseif (str::contains($line, 'lenovo') == true)
        {
            $img_icon = new UXImageView(new UXImage('res://.data/img/oneui-ser.png'));
            $item->css('-fx-text-fill','#6680e6');
        }
        elseif (str::contains($line, 'ua.') == true) //here starts third-party software    //-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-
        {
            $img_icon = new UXImageView(new UXImage('res://.data/img/dia.png'));
            $item->css('-fx-text-fill','#80b380');
        }
        elseif (str::contains($line, 'facebook') == true)
        {
            $img_icon = new UXImageView(new UXImage('res://.data/img/facebook.png'));
            $item->css('-fx-text-fill','#80b380');
        }
        elseif (str::contains($line, 'instagram') == true)
        {
            $img_icon = new UXImageView(new UXImage('res://.data/img/instagram.png'));
            $item->css('-fx-text-fill','#80b380');
        }
        elseif (str::contains($line, 'viber') == true)
        {
            $img_icon = new UXImageView(new UXImage('res://.data/img/viber-ser.png'));
            $item->css('-fx-text-fill','#80b380');
        }
        elseif (str::contains($line, 'telegram') == true)
        {
            $img_icon = new UXImageView(new UXImage('res://.data/img/telegram-ser.png'));
            $item->css('-fx-text-fill','#80b380');
        }
        elseif (str::contains($line, 'spotify') == true)
        {
            $img_icon = new UXImageView(new UXImage('res://.data/img/spotify-ser.png'));
            $item->css('-fx-text-fill','#80b380');
        }
        elseif (str::contains($line, 'discord') == true)
        {
            $img_icon = new UXImageView(new UXImage('res://.data/img/discord-ser.png'));
            $item->css('-fx-text-fill','#80b380');
        }
        elseif (str::contains($line, 'whatsapp') == true)
        {
            $img_icon = new UXImageView(new UXImage('res://.data/img/whatsapp-ser.png'));
            $item->css('-fx-text-fill','#80b380');
        }
        elseif (str::contains($line, 'openai') == true)
        {
            $img_icon = new UXImageView(new UXImage('res://.data/img/openai-ser.png'));
            $item->css('-fx-text-fill','#80b380');
        }
        elseif (str::contains($line, 'microsoft') == true)
        {
            $img_icon = new UXImageView(new UXImage('res://.data/img/microsoft-ser.png'));
            $item->css('-fx-text-fill','#80b380');
        }
        elseif (str::contains($line, 'binance') == true)
        {
            $img_icon = new UXImageView(new UXImage('res://.data/img/binance-ser.png'));
            $item->css('-fx-text-fill','#80b380');
        }
        elseif (str::contains($line, 'valve') == true)
        {
            $img_icon = new UXImageView(new UXImage('res://.data/img/steam-ser.png'));
            $item->css('-fx-text-fill','#80b380');
        }
        elseif (str::contains($line, 'zhiliaoapp') == true)
        {
            $img_icon = new UXImageView(new UXImage('res://.data/img/tiktok-ser.png'));
            $item->css('-fx-text-fill','#80b380');
        }
        elseif (str::contains($line, 'netflix') == true)
        {
            $img_icon = new UXImageView(new UXImage('res://.data/img/tiktok-ser.png'));
            $item->css('-fx-text-fill','#80b380');
        }
        elseif (str::contains($line, 'amazon') == true)
        {
            $img_icon = new UXImageView(new UXImage('res://.data/img/amazon-ser.png'));
            $item->css('-fx-text-fill','#80b380');
        }
        elseif (str::contains($line, 'reddit') == true)
        {
            $img_icon = new UXImageView(new UXImage('res://.data/img/reddit-ser.png'));
            $item->css('-fx-text-fill','#80b380');
        }
        elseif (str::contains($line, 'twitter') == true)
        {
            $img_icon = new UXImageView(new UXImage('res://.data/img/x-ser.png'));
            $item->css('-fx-text-fill','#80b380');
        }
        elseif (str::contains($line, 'gallery') == true)
        {
            $img_icon = new UXImageView(new UXImage('res://.data/img/gallery-ser.png'));
            $item->css('-fx-text-fill','#80b380');
        }
        elseif (str::contains($line, 'photo') == true)
        {
            $img_icon = new UXImageView(new UXImage('res://.data/img/gallery-ser.png'));
            $item->css('-fx-text-fill','#80b380');
        }
        elseif (str::contains($line, 'music') == true)
        {
            $img_icon = new UXImageView(new UXImage('res://.data/img/music-ser.png'));
            $item->css('-fx-text-fill','#80b380');
        }
        elseif (str::contains($line, 'audio') == true)
        {
            $img_icon = new UXImageView(new UXImage('res://.data/img/music-ser.png'));
            $item->css('-fx-text-fill','#80b380');
        }
        elseif (str::contains($line, 'video') == true)
        {
            $img_icon = new UXImageView(new UXImage('res://.data/img/video-ser.png'));
            $item->css('-fx-text-fill','#80b380');
        }
        elseif (str::contains($line, 'player') == true)
        {
            $img_icon = new UXImageView(new UXImage('res://.data/img/video-ser.png'));
            $item->css('-fx-text-fill','#80b380');
        }
        elseif (str::contains($line, 'launcher') == true)
        {
            $img_icon = new UXImageView(new UXImage('res://.data/img/launcher-ser.png'));
            $item->css('-fx-text-fill','#80b380');
        }
        elseif (str::contains($line, 'theme') == true)
        {
            $img_icon = new UXImageView(new UXImage('res://.data/img/launcher-ser.png'));
            $item->css('-fx-text-fill','#80b380');
        }
        elseif (str::contains($line, 'no.') == true)
        {
            $img_icon = new UXImageView(new UXImage('res://.data/img/norwey-ser.png'));
            $item->css('-fx-text-fill','#80b380');
        }
        elseif (str::contains($line, 'pl.') == true)
        {
            $img_icon = new UXImageView(new UXImage('res://.data/img/pl-ser.png'));
            $item->css('-fx-text-fill','#80b380');
        }
        elseif (str::contains($line, 'us.') == true)
        {
            $img_icon = new UXImageView(new UXImage('res://.data/img/us-ser.png'));
            $item->css('-fx-text-fill','#80b380');
        }
        elseif (str::contains($line, 'uk.') == true)
        {
            $img_icon = new UXImageView(new UXImage('res://.data/img/uk-ser.png'));
            $item->css('-fx-text-fill','#80b380');
        }
        elseif (str::contains($line, 'ro.') == true)
        {
            $img_icon = new UXImageView(new UXImage('res://.data/img/ro-ser.png'));
            $item->css('-fx-text-fill','#80b380');
        }
        elseif (str::contains($line, 'cz.') == true)
        {
            $img_icon = new UXImageView(new UXImage('res://.data/img/cz-ser.png'));
            $item->css('-fx-text-fill','#80b380');
        }
        else 
        {
            $img_icon = new UXImageView(new UXImage('res://.data/img/material.png'));
            $item->css('-fx-text-fill','#80b380');
        }
                                 
        $icon = new UXHBox([$img_icon]);
        $item->text = $line;
        $item->graphic = $icon;
        $item->autoSize = TRUE;
        $item->wrapText = TRUE;
        $item->rightAnchor = 1;
        $item->leftAnchor = 1;
        $item->height = 26;
        
        $this->form('MainForm')->listView->items->add($item);
        $this->form('MainForm')->listView->scrollTo($this->form('MainForm')->listView->items->count);
        
        $item_alt = $item->text;
        $this->form('MainForm')->listView6->items->add($item_alt);
        $this->form('MainForm')->listView6->scrollTo($this->form('MainForm')->listView6->items->count);
    }
    
    /**
    * Actions With ADB
    * @string Command (action)
    */
    function ADBAction ($action)
    {   
        try 
        {
            $output = (new Process ( explode(' ', "$action")))->start()->getInput()->readFully();
        }
        catch (IOException $e) {$this->toast($e);}
        
        $fxout = new UXLabelEx;
        $fxout->rightAnchor = 1;
        $fxout->leftAnchor = 1;
        $fxout->text = $output;
        $fxout->textColor = UXColor::of('#ffff4d');
        $img_icon = new UXImageView(new UXImage('res://.data/img/device.png'));                          
        $icon = new UXHBox([$img_icon]);
        $fxout->graphic = $icon;
        
        $this->form('MainForm')->listViewAlt->items->add($fxout);
        $this->form('MainForm')->listView6->items->add($output);
        $this->form('MainForm')->listViewAlt->scrollTo($this->form('MainForm')->listViewAlt->items->count);
        $this->form('MainForm')->listView6->scrollTo($this->form('MainForm')->listView6->items->count);
        
        $this->AddToLog("$action",'CMD');
        $this->AddToLog("$output",'RES');
    }
    
    /**
    * Actions With Fastboot
    * @string Command (action)
    */
    function FastbootAction($action)
    {
        try 
        {
            $output = (new Process ( explode(' ', "$action")))->start()->getInput()->readFully();
        }
        catch (IOException $e) {$this->toast($e);}
        
        $fxout = new UXLabelEx;
        $fxout->rightAnchor = 1;
        $fxout->leftAnchor = 1;
        $fxout->text = $output;
        $fxout->textColor = UXColor::of('#ffff4d');
        $img_icon = new UXImageView(new UXImage('res://.data/img/android.png'));                          
        $icon = new UXHBox([$img_icon]);
        $fxout->graphic = $icon;
        
        $this->form('MainForm')->listView3->items->add($fxout);
        $this->form('MainForm')->listView3->scrollTo($this->form('MainForm')->listView3->items->count());
        
        $this->AddToLog("$action",'CMD');
        $this->AddToLog(implode(null,"$output"),'RES');
    }
    
    /**
    * Setting Up Localization
    * @string $locale Localization Code (en-US, uk-UA, ru-RU, etc.)
    */
    function localization($locale = 'en-US')
    {
        $this->AddToLog("Localization changed to $locale",'ACT');
        
        if ($locale == 'uk-UA')
        {
            /*
                    For MainForm
            */      
            $this->form('MainForm')->tabPane->selectFirstTab();
            $this->form('MainForm')->tabPane->selectedTab->text = 'Застосунки (Ручне)';
            $this->form('MainForm')->tabPane->selectNextTab();
            $this->form('MainForm')->tabPane->selectedTab->text = 'Fastboot (Ручне)';
            $this->form('MainForm')->tabPane->selectNextTab();
            $this->form('MainForm')->tabPane->selectedTab->text = 'Скрипти';
            $this->form('MainForm')->tabPane->selectNextTab();
            $this->form('MainForm')->tabPane->selectedTab->text = 'Консоль (Ручне)';
            $this->form('MainForm')->tabPane->selectFirstTab();
            $this->form('MainForm')->checkboxAlt->text = 'Всі';
            $this->form('MainForm')->checkbox3->text = 'Вимкнуті';
            $this->form('MainForm')->checkbox4->text = 'Увімкнуті';
            $this->form('MainForm')->checkbox5->text = 'Системні';
            $this->form('MainForm')->checkbox6->text = 'Сторонні';
            $this->form('MainForm')->spoiler->text = 'Керування пристроєм';
            $this->form('MainForm')->spoiler3->text = 'Стерти';
            $this->form('MainForm')->spoiler5->text = 'Записати';
            $this->form('MainForm')->combobox->promptText = 'Розділ...';
            $this->form('MainForm')->comboboxAlt->promptText = 'Розділ...';
            $this->form('MainForm')->combobox3->promptText = 'Пристрій...';
            $this->form('MainForm')->combobox3->text = 'Пристрій...';
            $this->form('MainForm')->editimg->promptText = 'Образ запису...';
            $this->form('MainForm')->editAlt->promptText = 'Ім`я скрипту';
            $this->form('MainForm')->edit3->promptText = 'Команда';
            $this->form('MainForm')->button7->text = 'Перезавантажити';
            $this->form('MainForm')->button9->text = 'До завантажувача';
            $this->form('MainForm')->button11->text = 'Вимкнути';
            $this->form('MainForm')->button10->text = 'Увім/Вимк Екран';
            $this->form('MainForm')->button20->text = 'Керування...';
            $this->form('MainForm')->button12->text = 'Стерти';
            $this->form('MainForm')->button29->text = 'ОЕМ Розблк.';
            $this->form('MainForm')->button30->text = 'Розбл. Запису';
            $this->form('MainForm')->button16->text = 'Крит. Розблк.';
            $this->form('MainForm')->button24->text = 'ОЕМ Блок.';
            $this->form('MainForm')->button26->text = 'Блок. Запису';
            $this->form('MainForm')->button18->text = 'Крит. Блок.';
            $this->form('MainForm')->button31->text = 'До EDL';
            $this->form('MainForm')->button32->text = 'Записати';
            $this->form('MainForm')->button14->text = 'Локально';
            $this->form('MainForm')->button42->text = 'Застосунки на пристрої';
            $this->form('MainForm')->button19->text = 'Виконати';
            $this->form('MainForm')->button22->text = 'Обрати';
            $this->form('MainForm')->button23->text = 'Інформація';
            $this->form('MainForm')->edit->promptText = 'Назва пакету...';
            $this->form('MainForm')->button38->text = 'Пошук';
            $this->form('MainForm')->button35->text = 'Шукати в мережі';
            $this->form('MainForm')->button46->text = 'Бездротове з`єднання';
            app()->form('MainForm')->spoilerAlt->text = 'Кольорові позначення';
            app()->form('MainForm')->label5->text = 'Сторонні застосунки';
            app()->form('MainForm')->label22->text = 'Системні';
        }
        elseif ($locale == 'ru-RU')
        {
            /*
                    For MainForm
            */      
            $this->form('MainForm')->tabPane->selectFirstTab();
            $this->form('MainForm')->tabPane->selectedTab->text = 'Приложения (Ручное)';
            $this->form('MainForm')->tabPane->selectNextTab();
            $this->form('MainForm')->tabPane->selectedTab->text = 'Fastboot (Ручное)';
            $this->form('MainForm')->tabPane->selectNextTab();
            $this->form('MainForm')->tabPane->selectedTab->text = 'Скрипты';
            $this->form('MainForm')->tabPane->selectNextTab();
            $this->form('MainForm')->tabPane->selectedTab->text = 'Консоль (Ручное)';
            $this->form('MainForm')->tabPane->selectFirstTab();
            $this->form('MainForm')->checkboxAlt->text = 'Все';
            $this->form('MainForm')->checkbox3->text = 'Отключенные';
            $this->form('MainForm')->checkbox4->text = 'Включенные';
            $this->form('MainForm')->checkbox5->text = 'Системные';
            $this->form('MainForm')->checkbox6->text = 'Сторонние';
            $this->form('MainForm')->spoiler->text = 'Управление устройством';
            $this->form('MainForm')->spoiler3->text = 'Стереть';
            $this->form('MainForm')->spoiler5->text = 'Записать';
            $this->form('MainForm')->combobox->promptText = 'Раздел...';
            $this->form('MainForm')->comboboxAlt->promptText = 'Раздел...';
            $this->form('MainForm')->combobox3->promptText = 'Устройство...';
            $this->form('MainForm')->combobox3->text = 'Устройство...';
            $this->form('MainForm')->editimg->promptText = 'Образ записи...';
            $this->form('MainForm')->editAlt->promptText = 'Имя скрипта';
            $this->form('MainForm')->edit3->promptText = 'Команда';
            $this->form('MainForm')->button7->text = 'Перезагрузить';
            $this->form('MainForm')->button9->text = 'К загрузчику';
            $this->form('MainForm')->button11->text = 'Выключить';
            $this->form('MainForm')->button10->text = 'Вкл/Выкл Экран';
            $this->form('MainForm')->button20->text = 'Управление...';
            $this->form('MainForm')->button12->text = 'Стереть';
            $this->form('MainForm')->button29->text = 'ОЕМ Разбл.';
            $this->form('MainForm')->button30->text = 'Разбл. Записи';
            $this->form('MainForm')->button16->text = 'Крит. Разблк.';
            $this->form('MainForm')->button24->text = 'ОЕМ Блок.';
            $this->form('MainForm')->button26->text = 'Блок. Записи';
            $this->form('MainForm')->button18->text = 'Крит. Блок.';
            $this->form('MainForm')->button31->text = 'Перейти к EDL';
            $this->form('MainForm')->button32->text = 'Записать';
            $this->form('MainForm')->button14->text = 'Локально';
            $this->form('MainForm')->button42->text = 'Приложения устройства';
            $this->form('MainForm')->button19->text = 'Выполнить';
            $this->form('MainForm')->button22->text = 'Выбрать';
            $this->form('MainForm')->button23->text = 'Информация';
            $this->form('MainForm')->edit->promptText = 'Имя пакета...';
            $this->form('MainForm')->button38->text = 'Поиск';
            $this->form('MainForm')->button35->text = 'Искать в сети';
            $this->form('MainForm')->button46->text = 'Беспроводное соединение';
            app()->form('MainForm')->spoilerAlt->text = 'Цветовые обозначения';
            app()->form('MainForm')->label5->text = 'Стороннее ПО';
            app()->form('MainForm')->label22->text = 'Системные';
        }
        elseif ($locale == 'en-US')
        {
            /*
                    For MainForm
            */        
            app()->form('MainForm')->tabPane->selectFirstTab();
            app()->form('MainForm')->tabPane->selectedTab->text = 'Apps (Manual)';
            app()->form('MainForm')->tabPane->selectNextTab();
            app()->form('MainForm')->tabPane->selectedTab->text = 'Fastboot (Manual)';
            app()->form('MainForm')->tabPane->selectNextTab();
            app()->form('MainForm')->tabPane->selectedTab->text = 'Scripts';
            app()->form('MainForm')->tabPane->selectNextTab();
            app()->form('MainForm')->tabPane->selectedTab->text = 'Console (Manual)';
            app()->form('MainForm')->tabPane->selectFirstTab();
            app()->form('MainForm')->checkboxAlt->text = 'All';
            app()->form('MainForm')->checkbox3->text = 'Disabled';
            app()->form('MainForm')->checkbox4->text = 'Enabled';
            app()->form('MainForm')->checkbox5->text = 'System';
            app()->form('MainForm')->checkbox6->text = 'Third-party';
            app()->form('MainForm')->spoiler->text = 'Device controls';
            app()->form('MainForm')->spoiler3->text = 'Wipe';
            app()->form('MainForm')->spoiler5->text = 'Flash';
            app()->form('MainForm')->combobox->promptText = 'Partition...';
            app()->form('MainForm')->comboboxAlt->promptText = 'Partition...';
            app()->form('MainForm')->combobox3->promptText = 'Device...';
            app()->form('MainForm')->combobox3->text = 'Device...';
            app()->form('MainForm')->editimg->promptText = 'Flashing image...';
            app()->form('MainForm')->editAlt->promptText = 'Script name';
            app()->form('MainForm')->edit3->promptText = 'Command';
            app()->form('MainForm')->button7->text = 'Reboot';
            app()->form('MainForm')->button9->text = 'Reboot to B-loader';
            app()->form('MainForm')->button11->text = 'Shutdown';
            app()->form('MainForm')->button10->text = 'On/Off Screen';
            app()->form('MainForm')->button20->text = 'Control...';
            app()->form('MainForm')->button12->text = 'Wipe';
            app()->form('MainForm')->button29->text = 'ОЕМ Unlock';
            app()->form('MainForm')->button30->text = 'Flash Unlock';
            app()->form('MainForm')->button16->text = 'Critical Unlock';
            app()->form('MainForm')->button24->text = 'ОЕМ Lock';
            app()->form('MainForm')->button26->text = 'Flash Lock';
            app()->form('MainForm')->button18->text = 'Critical Lock';
            app()->form('MainForm')->button31->text = 'Move to EDL';
            app()->form('MainForm')->button32->text = 'Flash';
            app()->form('MainForm')->button14->text = 'Local';
            app()->form('MainForm')->button42->text = 'Packages on device';
            app()->form('MainForm')->button19->text = 'Execute';
            app()->form('MainForm')->button22->text = 'Select';
            app()->form('MainForm')->button23->text = 'Get info';
            app()->form('MainForm')->edit->promptText = 'Package name...';
            app()->form('MainForm')->button38->text = 'Search';
            app()->form('MainForm')->button35->text = 'Search on the web';
            app()->form('MainForm')->button46->text = 'Wireless connect';
            app()->form('MainForm')->spoilerAlt->text = 'Color codes';
            app()->form('MainForm')->label5->text = 'Third-Party Software';
            app()->form('MainForm')->label22->text = 'System';
        }
    }
    
    /**
    * Getting Information About Updates
    */
    function getUpdate()
    {   
        $version_cur = '2024.05'; //ver
         
        $this->AddToLog("Androcut $version_cur", 'INF');
        
        try 
        {
            $version = str::trim(file_get_contents('https://samarin-dev.github.io/androcut/VERSION.md'));
        }
        catch (Error $e) {$this->toast($e);}
        
        try 
        {
            $changelog = fs::get('https://samarin-dev.github.io/androcut/CHANGELOG.md');
        }
        catch (Error $e) {$this->toast($e);}
        
        if ($version != $version_cur)
        {
            app()->showForm('update');
            $this->form('update')->labelAlt->text = "Current ver.: $version_cur";
            $this->form('update')->label3->text = "New ver.: $version";
            $this->form('update')->textArea->text = $changelog;
            $this->AddToLog("A new version found: $version", 'INF');
        }
    }
    
    /**
    * AI Assistant Backend
    */
    function AICompare($input)
    {
        $db = $this->ini->sections();
        $needle = $this->listView->selectedItem->text;
        
        $this->form('MainForm')->label17->text = "Name: N/A";
        $this->form('MainForm')->label18->text = "Type: N/A";
        $this->form('MainForm')->label19->text = "Rating: N/A";
        $this->form('MainForm')->label20->text = "Reccomends: N/A";
        
        foreach ($db as $part)
        {
            if (str::contains($part, $needle) == true)
            {
                $info = $this->ini->section($part);
                $this->form('MainForm')->label17->text = "Name: $info[0]";
                $this->form('MainForm')->label18->text = "Type: $info[1]";
                $this->form('MainForm')->label19->text = "Rating: $info[2]";
                $this->form('MainForm')->label20->text = "Reccomends: $info[3]";
            }
        }
        
        if (str::contains($needle, 'mediatek') == true) //here starts Frameworks    //-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-
        {
            $img_icon = new UXImage('res://.data/img/system.png');
            $this->form('MainForm')->label42->text = 'Part of: MTK Framework';
        }
        elseif (str::contains($needle, 'mtk') == true)
        {
            $img_icon = new UXImage('res://.data/img/system.png');
            $this->form('MainForm')->label42->text = 'Part of: MTK Framework';
        }
        elseif (str::contains($needle, 'sprd') == true)
        {
            $img_icon = new UXImage('res://.data/img/system.png');
            $this->form('MainForm')->label42->text = 'Part of: Spreadtrum Framework';
        }
        elseif (str::contains($needle, 'spreadtrum') == true)
        {
            $img_icon = new UXImage('res://.data/img/system.png');
            $this->form('MainForm')->label42->text = 'Part of: Spreadtrum Framework';
        }
        elseif (str::contains($needle, 'qualcomm') == true)
        {
            $img_icon = new UXImage('res://.data/img/system.png');
            $this->form('MainForm')->label42->text = 'Part of: Qualcomm Framework';
        }
        elseif (str::contains($needle, 'qcom') == true)
        {
            $img_icon = new UXImage('res://.data/img/system.png');
            $this->form('MainForm')->label42->text = 'Part of: Qualcomm Framework';
        }
        elseif (str::contains($needle, 'com.android') == true) //here starts GMS and Android    //-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-
        {
            $img_icon = new UXImage('res://.data/img/android-ser.png');
            $this->form('MainForm')->label42->text = 'Part of: Android OS';
        }
        elseif (str::contains($needle, 'com.google') == true)
        {
            $img_icon = new UXImage('res://.data/img/google.png');
            $this->form('MainForm')->label42->text = 'Part of: Google MSF';
        }
        elseif (str::contains($needle, 'transsion') == true) //here starts OEM/ODM    //-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-
        {
            $img_icon = new UXImage('res://.data/img/hios.png');
            $this->form('MainForm')->label42->text = 'Part of: HiOS OEM';
        }
        elseif (str::contains($needle, 'zte') == true)
        {
            $img_icon = new UXImage('res://.data/img/zte-ser.png');
            $this->form('MainForm')->label42->text = 'Part of: ZTE OEM';
        }
        elseif (str::contains($needle, 'miui') == true)
        {
            $img_icon = new UXImage('res://.data/img/miui.png');
            $this->form('MainForm')->label42->text = 'Part of: MIUI OEM';
        }
        elseif (str::contains($needle, 'xiaomi') == true)
        {
            $img_icon = new UXImage('res://.data/img/miui.png');
            $this->form('MainForm')->label42->text = 'Part of: MIUI OEM';
        }
        elseif (str::contains($needle, 'mi') == true)
        {
            $img_icon = new UXImage('res://.data/img/miui.png');
            $this->form('MainForm')->label42->text = 'Part of: MIUI OEM';
        }
        elseif (str::contains($needle, 'oneplus') == true)
        {
            $img_icon = new UXImage('res://.data/img/oxygen-ser.png');
            $this->form('MainForm')->label42->text = 'Part of: OxygenOS OEM';
        }
        elseif (str::contains($needle, 'meizu') == true)
        {
            $img_icon = new UXImage('res://.data/img/flyme-ser.png');
            $this->form('MainForm')->label42->text = 'Part of: FlymeOS OEM';
        }
        elseif (str::contains($needle, 'samsung') == true)
        {
            $img_icon = new UXImage('res://.data/img/oneui-ser.png');
            $this->form('MainForm')->label42->text = 'Part of: OneUI OEM';
        }
        elseif (str::contains($needle, 'sec') == true)
        {
            $img_icon = new UXImage('res://.data/img/oneui-ser.png');
            $this->form('MainForm')->label42->text = 'Part of: OneUI OEM';
        }
        elseif (str::contains($needle, 'lenovo') == true)
        {
            $img_icon = new UXImage('res://.data/img/launcher-ser.png');
            $this->form('MainForm')->label42->text = 'Part of: Lenovo OEM';
        }
        else 
        {
            $img_icon = new UXImage('res://.data/img/android-ser.png');
            $this->form('MainForm')->label42->text = 'Part of: Unknown';
        }
        $this->form('MainForm')->imageAlt->image = $img_icon;
        
    }
    
    /**
    * Witing All Actions To Log File
    */    
    function AddToLog ($logstring, $logcode)
    {
        $time = Time::now();
        
        if ($logcode == 'ERR')
        {
            file_put_contents('log.md5', "$time ::: ERROR ::: $logstring \r\n", FILE_APPEND);
        }
        elseif ($logcode == 'CMD')
        {
            file_put_contents('log.md5', "$time > $logstring \r\n", FILE_APPEND);
        }
        elseif ($logcode == 'RES')
        {
            file_put_contents('log.md5', "$time | $logstring \r\n", FILE_APPEND);
        }
        elseif ($logcode == 'ACT')
        {
            file_put_contents('log.md5', "$time * $logstring \r\n", FILE_APPEND);
        }
        elseif ($logcode == 'INF')
        {
            file_put_contents('log.md5', ".:: $logstring ::.\r\n", FILE_APPEND);
        }
    }
    
    /**
    * Preparing User Interface Before Application Starts
    */
    function PrepUI ()
    {
        //    Getting Values
        $langid = $this->cfg->get('LocaleID', 'GLOBAL');
        $aimode = $this->cfg->get('AIMode', 'GLOBAL');
        $nobg = $this->cfg->get('NoBg', 'GLOBAL');
        $fsst = $this->cfg->get('StartFullScreen', 'GLOBAL');
        $liteui = $this->cfg->get('Lite', 'GLOBAL');
        $userdb = $this->cfg->get('UsrDB', 'GLOBAL');
        $dbpath = $this->cfg->get('UsrDBPath', 'GLOBAL');
        
        //    Pre-chaching all forms
        app()->showForm('MainForm');
        /*app()->showForm('settings');
        app()->showForm('about');
        app()->showForm('ai');
        app()->showForm('savescript');
        app()->showForm('dump');
        app()->showForm('install');
        app()->showForm('scripteditor');
        app()->showForm('tps');
        app()->showForm('update');
        app()->showForm('wificon');*/
                
        //    Preparing Language Settings 
        if ($langid == 0)
        {
            $this->localization('en-US');
        }
        elseif ($langid == 1)
        {
            $this->localization('uk-UA');
        }
        elseif ($langid == 2)
        {
            $this->localization('ru-RU');
        }
        else 
        {
            $this->localization('en-US');
            $this->AddToLog('Localization switch error', 'ERR');
        }
        
        //    Background
        if ($nobg == 1)
        {
            $this->form('MainForm')->image->visible = false;
            $this->form('settings')->checkbox->selected = true;
        }
        else 
        {
            $this->form('MainForm')->image->visible = true;
            $this->form('settings')->checkbox->selected = false;
        }
        
        //    Fullscreen
        if ($fsst == 1)
        {
            $this->form('MainForm')->fullScreen = true;
            $this->form('settings')->checkboxAlt->selected = true;
        }
        else 
        {
            $this->form('MainForm')->fullScreen = false;
            $this->form('settings')->checkboxAlt->selected = false;
        }
        
        //    Simplified Interface
        if ($liteui == 1)
        {
            $this->LiteUI(true);
            $this->form('settings')->checkbox4->selected = true;
        }
        else 
        {
            $this->LiteUI(false);
            $this->form('settings')->checkbox4->selected = false;
        }
        
        //    AI Mode
        if ($ai == 1)
        {
            app()->hideForm('MainForm');
            app()->showForm('ai');
            $this->form('settings')->checkbox3->selected = true;
        }
        else 
        {
            app()->showForm('MainForm');
            app()->hideForm('ai');
            $this->form('settings')->checkbox3->selected = false;
        }
        
        //    Use user-provided database
        if ($userdb == 1)
        {
            $this->ini->path = $dbpath;
            $this->form('settings')->checkbox5->selected = true;
            $this->form('settings')->panel8->enabled = true;
            $this->form('settings')->edit->text = $dbpath;
        }
        else 
        {
            $this->ini->path = 'https://samarin-dev.github.io/androcut/apps.db';
            
        }
        
        app()->form('MainForm')->title = 'Androcut - 2024.05'; //ver
        
        $img_icon = new UXImageView(new UXImage('res://.data/img/dump.png'));
        $icon = new UXHBox([$img_icon]);
        $this->form('MainForm')->tabPane->selectFirstTab();
        $this->form('MainForm')->tabPane->selectedTab->graphic = $icon;
        
        $img_icon = new UXImageView(new UXImage('res://.data/img/android.png'));
        $icon = new UXHBox([$img_icon]);
        $this->form('MainForm')->tabPane->selectNextTab();
        $this->form('MainForm')->tabPane->selectedTab->graphic = $icon;
        
        $img_icon = new UXImageView(new UXImage('res://.data/img/edit.png'));
        $icon = new UXHBox([$img_icon]);
        $this->form('MainForm')->tabPane->selectNextTab();
        $this->form('MainForm')->tabPane->selectedTab->graphic = $icon;
        
        $img_icon = new UXImageView(new UXImage('res://.data/img/terminal.png'));
        $icon = new UXHBox([$img_icon]);
        $this->form('MainForm')->tabPane->selectNextTab();
        $this->form('MainForm')->tabPane->selectedTab->graphic = $icon;
        
        $this->form('MainForm')->tabPane->selectFirstTab();
        
        $this->form('settings')->combobox4->items->clear();
        
        $lang = new UXLabelEx;
        $lang->rightAnchor = 1;
        $lang->leftAnchor = 1;
        $lang->text = 'English';
        $lang->textColor = UXColor::of('#ffff4d');
        $img_icon = new UXImageView(new UXImage('res://.data/img/uk-ser.png'));                          
        $icon = new UXHBox([$img_icon]);
        $lang->graphic = $icon;
        $this->form('settings')->combobox4->items->add($lang);
        
        $lang = new UXLabelEx;
        $lang->rightAnchor = 1;
        $lang->leftAnchor = 1;
        $lang->text = 'Українська';
        $lang->textColor = UXColor::of('#ffff4d');
        $img_icon = new UXImageView(new UXImage('res://.data/img/dia.png'));                          
        $icon = new UXHBox([$img_icon]);
        $lang->graphic = $icon;
        $this->form('settings')->combobox4->items->add($lang);
        
        $lang = new UXLabelEx;
        $lang->rightAnchor = 1;
        $lang->leftAnchor = 1;
        $lang->text = 'Русский';
        $lang->textColor = UXColor::of('#ffff4d');
        $img_icon = new UXImageView(new UXImage('res://.data/img/rus-ser.png'));                          
        $icon = new UXHBox([$img_icon]);
        $lang->graphic = $icon;
        $this->form('settings')->combobox4->items->add($lang);
        
        $this->form('settings')->combobox4->selectedIndex = $langid;
        
        app()->hideForm('settings');
        
        if (fs::size('rnc.cfg') > 0)
        {
            $this->form('MainForm')->doButton15Action();
            $this->form('MainForm')->doButton33Action();
            
            $path = fs::abs('./');
            $this->form('MainForm')->listView7->items->add("Current application path - $path");
        }
        else 
        {
            app()->showForm('runonce');
            app()->hideForm('MainForm');
        }
        
    }
    
    /**
    * Disable or enbale simplified UI for old machines
    */
    function LiteUI ($simplify = false)
    {
        if ($simplify == true)
        {
            app()->showForm('MainForm');
            $this->form('MainForm')->tabPane->disableAnimation = true;
            $this->form('MainForm')->panel->effects->clear();
            $this->form('MainForm')->panelAlt->effects->clear();
            $this->form('MainForm')->panel3->effects->clear();
            $this->form('MainForm')->panel4->effects->clear();
            $this->form('MainForm')->listView->effects->clear();
            $this->form('MainForm')->listViewAlt->effects->clear();
            $this->form('MainForm')->listView3->effects->clear();
            $this->form('MainForm')->listView4->effects->clear();
            $this->form('MainForm')->listView5->effects->clear();
            $this->form('MainForm')->listView6->effects->clear();
            $this->form('MainForm')->listView7->effects->clear();
        }
    }

}
