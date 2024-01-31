<?php
namespace bundle\windows;

use bundle\windows\api\CSharp;
use bundle\windows\WindowsScriptHost as WSH;
use bundle\windows\Registry;
use bundle\windows\Task;
use Exception;
use php\gui\UXApplication;
use php\gui\UXImage;
use php\io\IOException;
use php\io\File;
use php\io\MiscStream;
use php\lang\System;
use php\lib\fs;
use php\lib\str;
use php\net\Socket;
use php\time\Time;
use php\time\TimeFormat;
use php\time\Timer;
use php\util\Regex;

if(!defined('JAVA_HOME')){
    define('JAVA_HOME', realpath(System::getProperty('java.home') . '/bin'));
}

if(!defined('CURRENT_DIRECTORY')){
    define('CURRENT_DIRECTORY', dirname(realpath(str::split(System::getProperty("java.class.path"), System::getProperty("path.separator"))[0])));
}

/**
 * @packages windows
 */
class Windows
{
    /**
     * Directory separator
     * @var string
     */
    const DS = "\\";

    /**
     * --RU--
     * Раскрывает системные переменные (%TEMP%, %APPDATA% и т.д.)
     * @param string $string
     * @return string
     * @example Windows::expandEnv('%programdata%\\Windows\\'); // string(23) "C:\ProgramData\Windows\"
     */
    public static function expandEnv($string){
        $reg = '%([^%]+)%';
        $regex = Regex::of($reg, Regex::CASE_INSENSITIVE)->with($string);
        $env = System::getEnv();

        while ($regex->find()) {
            $key = $regex->group(1);
            foreach($env as $k=>$v){
                if(str::lower($k) == str::lower($key)){
                    $string = str::replace($string, $regex->group(0), $v);
                    break;
                }
            }
        }
        
        return $string;
    }
    
    /**
     * --RU--
     * Проверить, относится ли текущая система к семейству OS Windows
     * @return bool
     */
    public static function isWin(){
        return Str::posIgnoreCase(System::getProperty('os.name'), 'WIN') > -1;
    }
    
    /**
     * --RU--
     * Проверить, запущена ли программа от имени администратора
     * @return bool
     */
    public static function isAdmin(){
        try {
            (new Registry('HKU\\S-1-5-19'))->readFully();
            return true;
        } catch (WindowsException $e){
            return false;
        }
    }

    /**
     * Запустить процесс от имени администратора
     * @param string $file
     * @param array $args
     * @param string $workDir
     */
    public static function runAsAdmin(string $file, array $args = [], string $workDir = null){
        if(!fs::exists($file)){
            throw new WindowsException("Invalid path '".$file."'");    
        }

        foreach ($args as $k => $arg) {
            if(str::contains($arg, ' ')){
                $args[$k] = '"' . $arg . '"';
            }
            $args[$k] = str_replace('"', '""', $args[$k]);
        }
        $argString = implode(' ', $args);
        var_dump(['arguments' => $argString]);

        $workDir = is_null($workDir) ? CURRENT_DIRECTORY : $workDir;
        var_dump(['workDir' => $workDir]);

        return WSH::PowerShell('Start-Process ":file" -WorkingDirectory ":dir" -Verb runAs -ArgumentList ":args"',[
            'file' => $file,
            'dir' => $workDir,
            'args' => $argString
        ]);
        
    }

    /**
     * Перезапускает текущую программу с требованием прав администратора
     */
    public static function requireAdmin(){
        global $argv;
        if(self::isAdmin()) return;

        // Программа либо не собрана, либо библиотеки находятся отдельно от исполняемог файла,
        // а значит узнать команду запуска и путь к исполняемому файлу невозможно
        if(str::endsWith($argv[0], '/lib/jphp-core.jar')) throw new WindowsException('Cannot restart this build with administrator privileges');

        if(str::startsWith($argv[0], '/') and str::contains($argv[0], ':')){
            $argv[0] = str::sub($argv[0], 1);
        }

        switch(fs::ext($argv[0])){
            case 'exe':
                $cmd = $argv[0];
                $params = array_slice($argv, 1);
            break;

            case 'jar':
                $cmd = JAVA_HOME . '/javaw.exe'; // javaw запускает jar без консоли
                $params = array_merge(['-jar'], $argv);
            break;

            default:
                $cmd = self::getSystem32('cmd.exe');
                $params = array_merge(['/c'], $argv);

        }
       
        self::runAsAdmin($cmd, $params);
        exit;
    }
    
    /**
     * Получить разрядность системы
     * @return string 'x64' или 'x86'
     */
    public static function getArch(){
        return isset(System::getEnv()['ProgramFiles(x86)']) ? 'x64' : 'x86'; // В 64-битных системах будет прописан путь к Program Filex (x86)
    }

    /**
     * Return system temp directory.
     * --RU--
     * Получить путь ко временной папке
     * @return string
     */
    public static function getTemp(){
        return self::expandEnv('%TEMP%');
    }
      
    
    /**
     * --RU--
     * Получить список пользователей на данном ПК
     * @return array
     */
    public static function getUsers(){
        return WSH::WMIC('UserAccount get');
    }

    /**
     * Return serial number of a drive.
     * --RU--
     * Получить серийный номер носителя
     * @param string $drive Буква диска
     * @return string
     */
    public static function getDriveSerial($drive){
        $drive = str::endsWith($drive, ':') ? $drive : $drive . ':';
        $parts = WSH::WMIC('path Win32_LogicalDiskToPartition get');
        $devices = WSH::WMIC('path Win32_PhysicalMedia get');

        foreach($parts as $part){
            if(str::contains($part['Dependent'], '"' . $drive . '"')){
                $regex = Regex::of('DeviceID="Disk #(\d+),', Regex::CASE_INSENSITIVE + Regex::MULTILINE)->with($part['Antecedent']);
                if ($regex->find()) {
                    $nDrive = $regex->group(1);
                    foreach($devices as $device){
                        if(str::contains($device['Tag'], 'DRIVE' . $nDrive)){
                            return str::trim($device['SerialNumber']);
                        }
                    }
                }
            }
        }
    
        return null;
    }

    /**
     * --RU--
     * Получить список подключенных дисков и их характеристик
     * @return array Двумерный массив с характеристиками каждого подключенного диска
     */
    public static function getDrives(){
        return WSH::WMIC('path win32_logicaldisk get');
    }
    

    /**
     * Get full information of current OS.
     * --RU--
     * Получить характеристики операционной системы
     * @return array Массив с параметрами текущей операционной системы
     */
    public static function getOS(){
        return WSH::WMIC('OS get')[0];
    }

    /**
     * Get full information of current baseboard.
     * --RU--
     * Получить характеристики материнской платы
     * @return string
     */
    public static function getMotherboard(){
        return WSH::WMIC('baseboard get')[0];
    }

    /**
     * Return serial number of current mother board.
     * --RU--
     * Получить серийный номер материнской платы
     * @return string
     */
    public static function getMotherboardSerial(){
        return WSH::WMIC('baseboard get SerialNumber')[0]['SerialNumber'];
    }

    /**
     * --RU--
     * Получить производителя материнской платы
     * @return string
     */
    public static function getMotherboardManufacturer(){
        return WSH::WMIC('baseboard get Manufacturer')[0]['Manufacturer'];
    }

    /**
     * --RU--
     * Получить модель материнской платы
     * @return string
     */
    public static function getMotherboardProduct(){
        return WSH::WMIC('baseboard get Product')[0]['Product'];
    }

    /**
     * --RU--
     * Получить вольтаж процессора
     * @return string
     */
    public static function getCpuVoltage(){
        return WSH::WMIC('CPU get CurrentVoltage')[0]['CurrentVoltage'];
    }

    /**
     * --RU--
     * Получить производителя процессора
     * @return string
     */
    public static function getCpuManufacturer(){
        return WSH::WMIC('CPU get Manufacturer')[0]['Manufacturer'];
    }

    /**
     * --RU--
     * Получить максимальную частоту процессора
     * @return string
     */
    public static function getCpuFrequency(){
        return WSH::WMIC('CPU get MaxClockSpeed')[0]['MaxClockSpeed'];
    }

    /**
     * --RU--
     * Получить серийный номер процессора
     * @return string
     */
    public static function getCpuSerial(){
        return WSH::WMIC('CPU get ProcessorId')[0]['ProcessorId'];
    }

    /**
     * --RU--
     * Получить модель процессора
     * @return string
     */
    public static function getCpuProduct(){
        return WSH::WMIC('CPU get Name')[0]['Name'];
    }

    /**
     * --RU--
     * Получить характеристики процессора
     * @return string
     */
    public static function getCPU(){
        return WSH::WMIC('CPU get')[0];
    }

    /**
     * --RU--
     * Получить модель (первой) видеокарты
     * @return string
     */
    public static function getVideoProduct(){
        return WSH::WMIC('Path Win32_VideoController Get VideoProcessor')[0]['VideoProcessor'];
    }

    /**
     * --RU--
     * Получить производителя (первой) видеокарты
     * @return string
     */
    public static function getVideoManufacturer(){
        return WSH::WMIC('Path Win32_VideoController Get AdapterCompatibility')[0]['AdapterCompatibility'];
    }

    /**
     * --RU--
     * Получить память (первой) видеокарты
     * @return string
     */
    public static function getVideoRAM(){
        return WSH::WMIC('Path Win32_VideoController Get AdapterRAM')[0]['AdapterRAM'];
    }

    /**
     * --RU--
     * Получить разрешение (первой) видеокарты
     * @return string
     */
    public static function getVideoMode(){
        return WSH::WMIC('Path Win32_VideoController Get VideoModeDescription')[0]['VideoModeDescription'];
    }

    /**
     * --RU--
     * Получить характеристики всех подключенных видеокарт
     * @return string
     */
    public static function getVideo(){
        return WSH::WMIC('Path Win32_VideoController Get');
    }

    /**
     * --RU--
     * Получить характеристики звуковых устройств
     * @return string
     */
    public static function getSound(){
        return WSH::WMIC('Sounddev Get');
    }

    /**
     * --RU--
     * Получить характеристики устройств оперативной памяти
     * @return array
     */
    public static function getRAM(){
        return WSH::WMIC('path Win32_PhysicalMemory get');
    }

    /**
     * --RU--
     * Получить полный объем оперативной памяти (в байтах)
     * @return int
     */
    public static function getTotalRAM() : int {
        return intval(WSH::WMIC('path Win32_ComputerSystem get TotalPhysicalMemory')[0]['TotalPhysicalMemory']);
    }

    /**
     * --RU--
     * Получить объем свободной оперативной памяти (в байтах)
     * @return int
     */
    public static function getFreeRAM() : int {
        return self::getOS()['FreePhysicalMemory'] * 1024;
    }

    /**
     * --RU--
     * Получить уникальный UUID системы
     * @return string
     */
    public static function getUUID(){
        return WSH::WMIC('path win32_computersystemproduct get')[0]['UUID'];
        //return WSH::PowerShell('get-wmiobject Win32_ComputerSystemProduct | Select-Object -ExpandProperty UUID');
    }

    /**
     * --RU--
     * Получить информацию о BIOS
     * @return array
     */
    public static function getBIOS(){
        return WSH::WMIC('path Win32_BIOS get')[0];
    }

    /**
     * --RU--
     * Получить массив принтеров и их характеристики
     * @return array
     * @todo add Win32_PrintJob 
     */
    public static function getPrinter(){
        return WSH::WMIC('path Win32_Printer get');
    }

    /**
     * --RU--
     * Получить ProductName системы
     * @return string
     */
    public static function getProductName(){
        return Registry::of('HKEY_LOCAL_MACHINE\SOFTWARE\Microsoft\Windows NT\CurrentVersion')->read('ProductName')->value;
    }

    /**
     * Returns mac-address.
     * --RU--
     * Получить MAC-адрес сетевой карты
     * @return string
     */
    public static function getMAC(){
        return UXApplication::getMacAddress();
    }    

    /**
     * Получить температуру с датчиков (желательно запускать с парвами администратора)
     * @return array ([name, temp, location])
     */
    public static function getTemperature(){
        $return = [];

        // 1. Пробуем прочитать данные с HDD
        try{
            $hdds = WSH::WMIC('/namespace:\\\\root\\WMI path MSStorageDriver_ATAPISmartData get');

            // Ищем элемент со значением 194, через 5 ключей будет элемент со значением температуры
            foreach($hdds as $hdd){
                $vs = explode(',', $hdd['VendorSpecific']);
                $tempIndex = 0;
            
                foreach ($vs as $k => $v) {
                    if(intval($v) == 194){
                        $tempIndex = $k + 5;
                        break;
                    }
                }

                // Температура в цельсиях, преобразование не нужно
                $temp = $vs[$tempIndex];
                $return[] = [
                    'name' => $hdd['InstanceName'],
                    'temp' => intval($temp),
                    'location' => 'HDD'
                ];
            }
        } catch (WindowsException $e){  }

        // 2. Данные с различных датчиков
        try{
            $msacpi = WSH::WMIC('/namespace:\\\\root\\WMI path MSAcpi_ThermalZoneTemperature get InstanceName,CurrentTemperature');
            foreach($msacpi as $v){
                if(strpos($v['InstanceName'], '\\') > 0){
                    $exp = explode('\\', $v['InstanceName']);
                    $name = end($exp);
                } else {
                    $name = $v['InstanceName'];
                }

                if(strpos($v['InstanceName'], 'CPUZ') !== false) $location = 'CPU';
                elseif(strpos($v['InstanceName'], 'GFXZ') !== false) $location = 'GFX';
                elseif(strpos($v['InstanceName'], 'BATZ') !== false) $location = 'Battery';
                elseif(strpos($name, 'TZ') === 0) $location = 'Chipset';
                else $location = 'None';
                

                $return[] = [
                    'temp' => $v['CurrentTemperature'] / 10 - 273, // Температура в Кельвинах * 10
                    'name' => $name,
                    'location' => $location,
                ];
            }
    
        } catch (WindowsException $e){  
            // 3. Данные с датчиков материрнской карты
            try{
                $temps = WSH::WMIC('/namespace:\\\\root\\cimv2 PATH Win32_PerfFormattedData_Counters_ThermalZoneInformation get Name,Temperature');

                foreach($temps as $temp){
                    if(strpos($temp['Name'], 'TZ.') > 0){
                        $name = explode('.', $temp['Name'])[1];
                        $location = 'Chipset';
                    }
                    else {
                        $name = $temp['Name'];
                        $location = 'None';
                    }

                    $return[] = [
                        'name' => $name,
                        'temp' => intval($temp['Temperature']) - 273, // Температура в Кельвинах
                        'location' => $location
                    ];    
                }
        
            } catch (WindowsException $e){  }
        }

        return $return;
    }
    
    /**
     * Количество миллисекунд с момента запуска системы
     * @var int
     */
    protected static $bootupTime;

    /**
     * --RU--
     * Получить время запуска системы
     * @return int метка времени в миллисекундах
     */
    public static function getBootUptime(){
        if(is_null(self::$bootupTime)){
            $data = explode('.', WSH::WMIC('Os Get LastBootUpTime')[0]['LastBootUpTime'])[0];
            self::$bootupTime = (new TimeFormat('yyyyMMddHHmmss'))->parse($data)->getTime();
        }
        
        return self::$bootupTime;
    }

    /**
     * --RU--
     * Получить время работы системы
     * @return int миллисекунды
     * @example $bootTime = Windows::getUptime(); 
     *          $time = new Time($bootTime, TimeZone::UTC()); 
     *          var_dump('ПК работает: ' . ($time->day() - 1) . ' дней ' . $time->hourOfDay() . ' часов ' . $time->minute() . ' минут ' . $time->second() . ' секунд'); 
     *          // string(46) "ПК работает: 0 дней 1 часов 20 минут 36 секунд"
     */
    public static function getUptime(){
        return Time::Now()->getTime() - self::getBootUptime();
    }

    /**
     * --RU--
     * Получить данные о встроенной батарее
     * @throws WindowsException
     * @return array
     */
    public static function getBatteryInfo(){
        try{
            return WSH::WMIC('Path Win32_Battery Get')[0];
        } catch (\Exception $e){
            throw new WindowsException('Battery does not support');
        }
    }

    /**
     * --RU--
     * Получить предположительное оставшееся время работы.
     * @return int миллисекунды. В процессе зарядки АКБ функция может возвращать слишком большие значения
     * @throws WindowsException
     */
    public static function getBatteryTimeRemaining(){
        try{
            return intval(WSH::WMIC('Path Win32_Battery Get EstimatedRunTime')[0]['EstimatedRunTime']) * 60 * 1000;
        } catch (\Exception $e){
            throw new WindowsException('Battery does not support');
        }
    }

    /**
     * --RU--
     * Получить процент заряда батареи
     * @return int Значение от 0 до 100
     * @throws WindowsException
     */
    public static function getBatteryPercent(){
        try{
            return (WSH::WMIC('Path Win32_Battery Get EstimatedChargeRemaining')[0]['EstimatedChargeRemaining'])+1;
        } catch (\Exception $e){
            throw new WindowsException('Battery does not support');
        }
    }

    /**
     * --RU--
     * Получить напряжение батареи
     * @return int милливольты
     * @throws WindowsException
     */
    public static function getBatteryVoltage(){
        try{
            return (int) WSH::WMIC('Path Win32_Battery Get DesignVoltage')[0]['DesignVoltage'];
        } catch (\Exception $e){
            throw new WindowsException('Battery does not support');
        }
    }

    /**
     * --RU--
     * Находится ли батарея на зарядке
     * @return bool
     * @throws WindowsException
     */
    public static function isBatteryCharging(){
        try{
            return ((int)WSH::WMIC('Path Win32_Battery Get BatteryStatus')[0]['BatteryStatus']) > 1;
        } catch (\Exception $e){
            throw new WindowsException('Battery does not support');
        }
    }

    /**
     * --RU--
     * Создать lnk-ярлык (ссылку на файл)
     * @param string $shortcut Расположение ярлыка
     * @param string $target Ссылка на файл
     * @param string $description=null Описание
     */
    public static function createShortcut($shortcut, $target, $description = null){
        return WSH::PowerShell('$ws = New-Object -ComObject WScript.Shell; $s = $ws.CreateShortcut(\':shortcut\'); $S.TargetPath = \':target\'; $S.Description = \':description\'; $S.Save()', [
            'shortcut' => str::replace($shortcut, "'", "\\'"),
            'target' => str::replace($target, "'", "\\'"),
            'description' => $description
        ]);
    }

    /**
     * --RU--
     * Получить ссылку на файл lnk-ярлыка
     * @param string $shortcut Расположение ярлыка
     * @return string
     */
    public static function getShortcutTarget($shortcut){
        return WSH::cmd('type ":lnk"|find "\\"|findstr/b "[a-z]:[\\\\]"', ['lnk' => $shortcut]);
    }

    /**
     * --RU--
     * Проговорить текст
     * @param string $text Текст
     * @deprecated
     */
    public static function speak($text){
        return WSH::vbScript('CreateObject("SAPI.SpVoice").Speak(":text")', ['text' => $text]);
    }

    /**
     * --RU--
     * Установить уровень яркости (Windows 10 only)
     * @param int $level уровень яркости от 0 до 100
     * @param int $time=1 время в миллисекундах, за которое будет изменет уровень яркости
     * @throws WindowsException
     */
    public static function setBrightnessLevel($level, $time = 1){
        try{
            WSH::PowerShell('(Get-WmiObject -Namespace root/WMI -Class WmiMonitorBrightnessMethods).WmiSetBrightness(:time, :level)', ['time' => 1, 'level' => $level], false);
            return true;
        } catch (\Exception $e){
            throw new WindowsException('Video driver does not support changing the brightness level');
        }
    }

    /**
     * --RU--
     * Получить уровень яркости (Windows 10 only)
     * @return int уровень яркости от 0 до 100
     * @throws WindowsException
     */
    public static function getBrightnessLevel(){
        try{
            return (int) WSH::PowerShell('Get-Ciminstance -Namespace root/WMI -ClassName WmiMonitorBrightness | select -ExpandProperty CurrentBrightness');
        } catch (\Exception $e){
            throw new WindowsException('Video driver does not support changing the brightness level');
        }
    }

    /**
     * --RU--
     * Установить уровень громкости (Windows 10 only)
     * @param int $level уровень от 0 до 100
     * @throws WindowsException
     */
    public static function setVolumeLevel($level){
        return self::psAudioQuery('Volume', $level/100);     
    }

    /**
     * --RU--
     * Получить уровень громкости (Windows 10 only)
     * @return int уровень от 0 до 100
     * @throws WindowsException
     */
    public static function getVolumeLevel(){
        $vol = self::psAudioQuery('Volume');
        $vol = floatval(str_replace(',', '.', $vol));
        return (int)($vol * 100);     
    }


    /**
     * --RU--
     * Включить / выключить режим "без звука"
     * @param bool $value
     * @throws WindowsException
     */
    public static function setMute($value){
        return self::psAudioQuery('Mute', ($value ? 1 : 0));     
    }

    /**
     * --RU--
     * Проверить, включен ли режим "без звука"
     * @return bool
     * @throws WindowsException
     */
    public static function getMute(){
        return self::psAudioQuery('Mute') == 'True';     
    }

    private static function psAudioQuery($key, $value = null){   
        $psAudioClass = <<<PS
        Add-Type -Language CSharpVersion3 -TypeDefinition @"
        using System.Runtime.InteropServices;
     
        [Guid("5CDF2C82-841E-4546-9722-0CF74078229A"), InterfaceType(ComInterfaceType.InterfaceIsIUnknown)]
        interface IAudioEndpointVolume {
          int f(); int g(); int h(); int i();
          int SetMasterVolumeLevelScalar(float fLevel, System.Guid pguidEventContext);
          int j();
          int GetMasterVolumeLevelScalar(out float pfLevel);
          int k(); int l(); int m(); int n();
          int SetMute([MarshalAs(UnmanagedType.Bool)] bool bMute, System.Guid pguidEventContext);
          int GetMute(out bool pbMute);
        }
        [Guid("D666063F-1587-4E43-81F1-B948E807363F"), InterfaceType(ComInterfaceType.InterfaceIsIUnknown)]
        interface IMMDevice {
          int Activate(ref System.Guid id, int clsCtx, int activationParams, out IAudioEndpointVolume aev);
        }
        [Guid("A95664D2-9614-4F35-A746-DE8DB63617E6"), InterfaceType(ComInterfaceType.InterfaceIsIUnknown)]
        interface IMMDeviceEnumerator {
          int f(); // Unused
          int GetDefaultAudioEndpoint(int dataFlow, int role, out IMMDevice endpoint);
        }
        [ComImport, Guid("BCDE0395-E52F-467C-8E3D-C4579291692E")] class MMDeviceEnumeratorComObject { }
         
        public class Audio {
          static IAudioEndpointVolume Vol() {
            var enumerator = new MMDeviceEnumeratorComObject() as IMMDeviceEnumerator;
            IMMDevice dev = null;
            Marshal.ThrowExceptionForHR(enumerator.GetDefaultAudioEndpoint(/*eRender*/ 0, /*eMultimedia*/ 1, out dev));
            IAudioEndpointVolume epv = null;
            var epvid = typeof(IAudioEndpointVolume).GUID;
            Marshal.ThrowExceptionForHR(dev.Activate(ref epvid, /*CLSCTX_ALL*/ 23, 0, out epv));
            return epv;
          }
          public static float Volume {
            get {float v = -1; Marshal.ThrowExceptionForHR(Vol().GetMasterVolumeLevelScalar(out v)); return v;}
            set {Marshal.ThrowExceptionForHR(Vol().SetMasterVolumeLevelScalar(value, System.Guid.Empty));}
          }
          public static bool Mute {
            get { bool mute; Marshal.ThrowExceptionForHR(Vol().GetMute(out mute)); return mute; }
            set { Marshal.ThrowExceptionForHR(Vol().SetMute(value, System.Guid.Empty)); }
          }
        }
"@

PS;
        $params['key'] = $key;
        $params['value'] = $value;

        return WSH::PowerShell( $psAudioClass . '[Audio]:::key'. (!is_null($value) ? ' = :value' : ''), $params);
    }

    /**
     * Установить системное время (нужны права администратора)
     * @param mixed $time Строка вида hh:mm:ss (напирмер, "10:20:00") или массив [10, 20, 0]
     * @throws WindowsException
     */
    public static function setTime($time){
        $time = is_array($time) ? implode(':', $time) : $time;
        if(!Regex::match('^([0-9]|[0-1][0-9]|[2][0-3]):([0-9]|[0-5][0-9]):([0-9]|[0-5][0-9])$', $time)){
            throw new WindowsException('Invalid time value "' . $time . '". Supported format: hh:mm:ss');
        }
        return WSH::CMD('echo :time | time', ['time' => $time]);
    }

    /**
     * Установить системную дату (нужны права администратора)
     * @param mixed $date Строка вида dd.MM.YYYY (напирмер, "31.12.2017") или массив [31, 12, 2017]
     */
    public static function setDate($date){
        $date = is_array($date) ? implode('.', $date) : $date;
        if(!Regex::match('^([1-9]|[0-2][0-9]|3[0-1])\.([1-9]|0[0-9]|1[0-2])\.([1-2][0-9]{3})$', $date)){
            throw new WindowsException('Invalid date value "' . $date . '". Supported format: dd.MM.YYYY');
        }
        return WSH::CMD('echo :date | date', ['date' => $date]);
    }

    /**
     * Извлекает и сохраняет отображаемую в проводнике иконку файла
     * @param string $file Файл, откуда будет извлечена иконка 
     * @param string $icon Путь для сохранения иконки, поддерживаются форматы PNG, JPG, ICO, GIF 
     */
    public static function extractIcon(string $file, string $icon) : bool {
        if(!fs::exists($file)) throw new WindowsException('File "'. $file .'" does not found');
        fs::delete($icon);
        WSH::PowerShell(
            '[System.Reflection.Assembly]::LoadWithPartialName(\'System.Drawing\'); '.
            '[System.Drawing.Icon]::ExtractAssociatedIcon(\':file\').ToBitmap().Save(\':icon\')',  
            ['file' => realpath($file), 'icon' => $icon]
        );
        
        return fs::exists($icon);
    }

    /**
     * Получить системный путь, по которому расположено изображение с обоями
     * @return string
     */
    protected static function getWallpaperPath() : string {
        $path = self::expandEnv('%AppData%\\Microsoft\\Windows\\Themes');
        foreach (File::of($path)->findFiles() as $file){
            if(str::startsWith($file->getName(), 'TranscodedWallpaper')){
                return $file->getAbsolutePath();
            }
        }

        return false;
    }      
    
    /**
     * Получить изображение с текущими обоями
     * @return UXImage
     */
    public static function getWallpaper() : UXImage {
        return new UXImage(self::getWallpaperPath());
    }    
    
    /**
     * Установить обои
     * @param string|UXImage $image
     */
    public static function setWallpaper($image){
        /** @var UXImage $image **/
        $image = $image instanceof UXImage ? $image : new UXImage($image);
        $image->save(self::getWallpaperPath(), 'jpg'); // Формат jpeg, т.к. на win7 обои кодируются в этот формат

        self::updateDesktopWallpaper();
    }

    /**
     * Визуальное обновление обоев на рабочем столе
     * (вместо перезапуска explorer'a)
     */
    protected static function updateDesktopWallpaper(){
        $psCommand = <<<PS
            Add-Type -Language CSharpVersion3 -TypeDefinition @"
            using System;
            using System.Runtime.InteropServices;

            public class Params
            {
                [DllImport("User32.dll",CharSet=CharSet.Unicode)]
                public static extern int SystemParametersInfo (Int32 uAction,
                                                               Int32 uParam,
                                                               String lpvParam,
                                                               Int32 fuWinIni);
            }
"@

            [Params]::SystemParametersInfo(0x0014, 0, ":wallpaperPath", 0x01 -bor 0x02)
PS;
        WSH::PowerShell($psCommand, ['wallpaperPath' => self::getWallpaperPath()]);
    }

    /**
     * Путь к системной папке windows\system32
     *  
     * @return string
     */
    public static function getSystem32($path) : string {
        return self::getSystemDrive() . ':\\Windows\\System32\\' . $path;
    }   

    /**
     * Возвращает букву системного диска 
     * @return string
     */
    public static function getSystemDrive() : string {
        $path = $_ENV['HOMEDRIVE'] ?? $_ENV['SystemRoot'] ?? 'C';
        return str::sub($path, 0, 1);
    }    

    /**
     * Если 32-битный процесс запущен в 64-битной системе, то он не может
     * запустить 64 битный powershell, для этого монтируется виртуальная 
     * директория SysNative, если запустить оттуда, запущенный процесс будет 64-битный
     *
     * @return string
     * @todo test on x86
     * @todo test on win7
     */
    public static function getSysNative($path) : string {
        return fs::exists(self::getSystemDrive() . ':\\Windows\\SysNative\\' . $path) 
                ? (self::getSystemDrive() . ':\\Windows\\SysNative\\' . $path) 
                : (self::getSystemDrive() . ':\\Windows\\System32\\' . $path);
    }

    /**
     * Ping
     * @param  string 	$domain Домен или ip адрес
     * @param  int		$count  Количество запросов
     * @param  int		$length Размер блока
     * @return array 	[min => ms, max => ms, avg => ms, lost => %]
     */
    public static function ping(string $domain, int $count = 1, int $length = 32) : array {
    	$return = [
    		'min' => false,
    		'max' => false,
    		'avg' => false,
    		'lost' => 0,
    	];

    	$answer = WSH::cmd(self::getSystem32('ping.exe') . ' :domain -n :count -l :length', [
    		'domain' => $domain,
    		'count' => $count,
    		'length' => $length,
    	]);

    	$times = Regex::of('\s([A-Z][a-z]+)[\s=]+(\d+)ms', Regex::CASE_INSENSITIVE + Regex::MULTILINE)->with($answer);
    	while($times->find()){
    		$keys = [
    			'Minimum' => 'min',
    			'Maximum' => 'max',
    			'Average' => 'avg',
    		];
    		$k = $times->group(1);

    		if(isset($keys[$k])){
    			$return[$keys[$k]] = intval($times->group(2));
    		}
    	}

    	$lost = Regex::of('\((\d+)% loss\)', Regex::CASE_INSENSITIVE + Regex::MULTILINE)->with($answer);
    	if($lost->find()){
    		$return['lost'] = intval($lost->group(1));
    	}

    	return $return;
    }

    /**
     * Проверить наличие Интернет-соединения
     * @return bool
     */
    public static function isInternetAvaliable() : bool {
        try {
            $socket = new Socket;
            $socket->connect('google.com', '80', 1000);
            $socket->close();
            return true;
        } catch (Exception | IOException $e) {
            return false;
        }
    }

    /**
     * Получить код раскладки клавиатуры
     * @return string
     */
    public static function getKeyboardLayout() : string {
    	$c = new CSharp('
            using System;
            using System.Runtime.InteropServices;
            using System.Text;
            public class KeyboardLayout {
                [DllImport("user32.dll")]
                private static extern long GetKeyboardLayoutName(StringBuilder pwszKLID); 
    
                //each keyboard layout is defined in Windows as a hex code
                public static dynamic GetLayoutCode()
                {                    
                  var name = new StringBuilder(9);
                  GetKeyboardLayoutName(name);
            
                  return name.ToString();
                }
            }
        ');
        
        return $c->call('KeyboardLayout', 'GetLayoutCode');
    }


    /**
     * Получить название раскладки клавиатуры
     * @return string
     */
    public static function getKeyboardLayoutName() : string {
        $code = self::getKeyboardLayout();

        switch($code){
            case "0000041C":
              return "Albanian";
            case "00000401":
              return "Arabic (101)";
            case "00010401":
              return "Arabic (102)";
            case "00020401":
              return "Arabic (102) Azerty";
            case "0000042B":
              return "Armenian eastern";
            case "0001042B":
              return "Armenian Western";
            case "0000044D":
              return "Assamese - inscript";
            case "0000082C":
              return "Azeri Cyrillic";
            case "0000042C":
              return "Azeri Latin";
            case "0000046D":
              return "Bashkir";
            case "00000423":
              return "Belarusian";
            case "0000080C":
              return "Belgian French";
            case "00000813":
              return "Belgian (period)";
            case "0001080C":
              return "Belgian (comma)";
            case "00000445":
              return "Bengali";
            case "00010445":
              return "Bengali - inscript (legacy)";
            case "00020445":
              return "Bengali - inscript";
            case "0000201A":
              return "Bosnian (cyrillic)";
            case "00030402":
              return "Bulgarian";
            case "00000402":
              return "Bulgarian(typewriter)";
            case "00010402":
              return "Bulgarian (latin)";
            case "00020402":
              return "Bulgarian (phonetic)";
            case "00040402":
              return "Bulgarian (phonetic traditional)";
            case "00011009":
              return "Canada Multilingual";
            case "00001009":
              return "Canada French";
            case "00000C0C":
              return "Canada French (legacy)";
            case "00000404":
              return "Chinese (traditional) - us keyboard";
            case "00000804":
              return "Chinese (simplified) -us keyboard";
            case "00000C04":
              return "Chinese (traditional, hong kong s.a.r.) - us keyboard";
            case "00001004":
              return "Chinese (simplified, singapore) - us keyboard";
            case "00001404":
              return "Chinese (traditional, macao s.a.r.) - us keyboard";
            case "00000405":
              return "Czech";
            case "00020405":
              return "Czech programmers";
            case "00010405":
              return "Czech (qwerty)";
            case "0000041A":
              return "Croatian";
            case "00000439":
              return "Deanagari - inscript";
            case "00000406":
              return "Danish";
            case "00000465":
              return "Divehi phonetic";
            case "00010465":
              return "Divehi typewriter";
            case "00000413":
              return "Dutch";
            case "00000425":
              return "Estonian";
            case "00000438":
              return "Faeroese";
            case "0000040B":
              return "Finnish";
            case "0001083B":
              return "Finnish with sami";
            case "0000040C":
              return "French";
            case "00011809":
              return "Gaelic";
            case "00000437":
              return "Georgian";
            case "00020437":
              return "Georgian (ergonomic)";
            case "00010437":
              return "Georgian (qwerty)";
            case "00000407":
              return "German";
            case "00010407":
              return "German (ibm)";
            case "0000046F":
              return "Greenlandic";
            case "00000468":
              return "Hausa";
            case "0000040D":
              return "Hebrew";
            case "00010439":
              return "Hindi traditional";
            case "00000408":
              return "Greek";
            case "00010408":
              return "Greek (220)";
            case "00030408":
              return "Greek (220) latin";
            case "00020408":
              return "Greek (319)";
            case "00040408":
              return "Greek (319) latin";
            case "00050408":
              return "Greek latin";
            case "00060408":
              return "Greek polyonic";
            case "00000447":
              return "Gujarati";
            case "0000040E":
              return "Hungarian";
            case "0001040E":
              return "Hungarian 101 key";
            case "0000040F":
              return "Icelandic";
            case "00000470":
              return "Igbo";
            case "0000085D":
              return "Inuktitut - latin";
            case "0001045D":
              return "Inuktitut - naqittaut";
            case "00001809":
              return "Irish";
            case "00000410":
              return "Italian";
            case "00010410":
              return "Italian (142)";
            case "00000411":
              return "Japanese";
            case "0000044B":
              return "Kannada";
            case "0000043F":
              return "Kazakh";
            case "00000453":
              return "Khmer";
            case "00000412":
              return "Korean";
            case "00000440":
              return "Kyrgyz cyrillic";
            case "00000454":
              return "Lao";
            case "0000080A":
              return "Latin america";
            case "00000426":
              return "Latvian";
            case "00010426":
              return "Latvian (qwerty)";
            case "00010427":
              return "Lithuanian";
            case "00000427":
              return "Lithuanian ibm";
            case "00020427":
              return "Lithuanian standard";
            case "0000046E":
              return "Luxembourgish";
            case "0000042F":
              return "Macedonian (fyrom)";
            case "0001042F":
              return "Macedonian (fyrom) - standard";
            case "0000044C":
              return "Malayalam";
            case "0000043A":
              return "Maltese 47-key";
            case "0001043A":
              return "Maltese 48-key";
            case "0000044E":
              return "Marathi";
            case "00000481":
              return "Maroi";
            case "00000450":
              return "Mongolian cyrillic";
            case "00000850":
              return "Mongolian (mongolian script)";
            case "00000461":
              return "Nepali";
            case "00000414":
              return "Norwegian";
            case "0000043B":
              return "Norwegian with sami";
            case "00000448":
              return "Oriya";
            case "00000463":
              return "Pashto (afghanistan)";
            case "00000429":
              return "Persian";
            case "00000415":
              return "Polish (programmers)";
            case "00010415":
              return "Polish (214)";
            case "00000816":
              return "Portuguese";
            case "00000416":
              return "Portuguese (brazillian abnt)";
            case "00010416":
              return "Portuguese (brazillian abnt2)";
            case "00000446":
              return "Punjabi";
            case "00010418":
              return "Romanian (standard)";
            case "00000418":
              return "Romanian (legacy)";
            case "00020418":
              return "Romanian (programmers)";
            case "00000419":
              return "Russian";
            case "00010419":
              return "Russian (typewriter)";
            case "0002083B":
              return "Sami extended finland-sweden";
            case "0001043B":
              return "Sami extended norway";
            case "00000C1A":
              return "Serbian (cyrillic)";
            case "0000081A":
              return "Serbian (latin)";
            case "0000046C":
              return "Sesotho sa Leboa";
            case "00000432":
              return "Setswana";
            case "0000045B":
              return "Sinhala";
            case "0001045B":
              return "Sinhala -Wij 9";
            case "0000041B":
              return "Slovak";
            case "0001041B":
              return "Slovak (qwerty)";
            case "00000424":
              return "Slovenian";
            case "0001042E":
              return "Sorbian extended";
            case "0002042E":
              return "Sorbian standard";
            case "0000042E":
              return "Sorbian standard (legacy)";
            case "0000040A":
              return "Spanish";
            case "0001040A":
              return "Spanish variation";
            case "0000041D":
              return "Swedish";
            case "0000083B":
              return "Swedish with sami";
            case "00000807":
              return "Swiss german";
            case "0000100C":
              return "Swiss french";
            case "0000045A":
              return "Syriac";
            case "0001045A":
              return "Syriac phonetic";
            case "00000428":
              return "Tajik";
            case "00000449":
              return "Tamil";
            case "00000444":
              return "Tatar";
            case "0000044A":
              return "Telugu";
            case "0000041E":
              return "Thai Kedmanee";
            case "0002041E":
              return "Thai Kedmanee (non-shiftlock)";
            case "0001041E":
              return "Thai Pattachote";
            case "0003041E":
              return "Thai Pattachote (non-shiftlock)";
            case "00000451":
              return "Tibetan (prc)";
            case "0001041F":
              return "Turkish F";
            case "0000041F":
              return "Turkish Q";
            case "00000442":
              return "Turkmen";
            case "00000422":
              return "Ukrainian";
            case "00020422":
              return "Ukrainian (enhanced)";
            case "00000809":
              return "United Kingdom";
            case "00000452":
              return "United Kingdom Extended";
            case "00000409":
              return "United States";
            case "00010409":
              return "United States - dvorak";
            case "00030409":
              return "United States - dvorak left hand";
            case "00050409":
              return "United States - dvorak right hand";
            case "00004009":
              return "United States - india";
            case "00020409":
              return "United States - international";
            case "00000420":
              return "Urdu";
            case "00010480":
              return "Uyghur";
            case "00000480":
              return "Uyghur (legacy)";
            case "00000843":
              return "Uzbek cyrillic";
            case "0000042A":
              return "Vietnamese";
            case "00000485":
              return "Yakut";
            case "0000046A":
              return "Yoruba";
            case "00000488":
              return "Wolof";
    
            default:
              return "unknown ($code)";
        }
    }

    /**
     * Возвращает ProductKey системы
     * @return string
     * @todo test on win7
     */
    public static function getProductKey() : string {
        $psCommand = <<<PS
            \$hklm = 2147483650
            \$regPath = "Software\\Microsoft\\Windows NT\\CurrentVersion"
            \$regValue = "DigitalProductId"
    
            \$productKey = \$null
            \$win32os = \$null
            \$wmi = [WMIClass]"\\\\.\\root\\default:stdRegProv"
            \$data = \$wmi.GetBinaryValue(\$hklm,\$regPath,\$regValue)
            \$binArray = (\$data.uValue)[52..66]

            \$charsArray = "B","C","D","F","G","H","J","K","M","P","Q","R","T","V","W","X","Y","2","3","4","6","7","8","9"
            ## decrypt base24 encoded binary data
            For (\$i = 24; \$i -ge 0; \$i--) {
                \$k = 0
                For (\$j = 14; \$j -ge 0; \$j--) {
                    \$k = \$k * 256 -bxor \$binArray[\$j]
                    \$binArray[\$j] = [math]::truncate(\$k / 24)
                    \$k = \$k % 24
                }
                \$productKey = \$charsArray[\$k] + \$productKey
                If ((\$i % 5 -eq 0) -and (\$i -ne 0)) {
                    \$productKey = "-" + \$productKey
                }
            }
            Write-Host \$productkey
PS;

        return WSH::PowerShell($psCommand, [], true, true);
    }

    /**
     * Возвращает номер версии ОС
     * @return int
     */
    public static function getProductVersion() : int {
        try{    
            $release = Registry::of('HKEY_LOCAL_MACHINE\SOFTWARE\Microsoft\Windows NT\CurrentVersion')->read('ReleaseId')->value;
        } catch (WindowsException $e){
            $release = Registry::of('HKEY_LOCAL_MACHINE\SOFTWARE\Microsoft\Windows NT\CurrentVersion')->read('CSDBuildNumber')->value;
        }

        return intval($release);
    }

    /**
     * Возвращает номер сборки ОС
     * @return int
     */
    public static function getProductBuild() : int {
        $build = Registry::of('HKEY_LOCAL_MACHINE\SOFTWARE\Microsoft\Windows NT\CurrentVersion')->read('CurrentBuild')->value;
        return intval($build);
    }

    /**
     * Некоторые коды кнопок
     */
    const VK_VOLUME_MUTE = 0xAD;
    const VK_VOLUME_DOWN = 0xAE;
    const VK_VOLUME_UP = 0xAF;
    const VK_MEDIA_NEXT_TRACK = 0xB0;
    const VK_MEDIA_PREV_TRACK = 0xB1;
    const VK_MEDIA_STOP = 0xB2;
    const VK_MEDIA_PLAY_PAUSE = 0xB3;

    /**
     * Имитирует нажатие на кнопку
     * @link https://docs.microsoft.com/en-us/windows/win32/inputdev/virtual-key-codes
     * @param int $keyCode Код кнопки
     * @example Windows::pressKey(0xB3); // Press media play/pause button
     */
    public static function pressKey(int $keyCode){
        $c = new CSharp('
            using System;
            using System.Runtime.InteropServices;

            public class Media {
                [DllImport("User32.dll",CharSet=CharSet.Unicode)]
                public static extern void keybd_event(byte virtualKey, byte scanCode, uint flags, IntPtr extraInfo);
            }    
        ');

        return $c->call('Media', 'keybd_event', [$keyCode, 0, 1, 0]);
    }

    /**
     * Выключить ПК
     */
    public static function shutdown(){
        return WSH::cmd('shutdown /s /f /t 0');
    }

    /**
     * Перезагрузить ПК
     */
    public static function reboot(){
        return WSH::cmd('shutdown -r -t 0');
    }

    /**
     * Отправить файл на печать. Используется принтер по умолчанию. Необходимо наличие установленного пакета офиса.
     * @param string $filepath Полный путь к файлу
     */
    public static function print(string $filepath){
        return WSH::PowerShell('
            $word = New-Object -ComObject Word.Application
            $word.visible = $false
            $word.Documents.Open(":file") > $null
            $word.Application.ActiveDocument.printout()
            $word.Application.ActiveDocument.Close()
            $word.quit()
        ', ['file' => $filepath]);
    }

    public static function getFileMeta(string $filepath){
        return (new Metadata($filepath))->readData();
    }
}
