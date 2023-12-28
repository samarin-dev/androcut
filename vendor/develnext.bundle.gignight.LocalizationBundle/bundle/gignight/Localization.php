<?php
namespace bundle\gignight;

use facade\Json;
use php\io\File;
use php\util\Regex;
use bundle\gignight\exception\LocalizationException;

/**
 * Class Localization
 * 
 * @author    GIGNIGHT
 * @copyright 2019 - 2020 GIGNIGHT
 * @license   MIT https://opensource.org/licenses/MIT
 * @link      http://github.com/GIGNIGHT/dn-localization-ext
 * @packages  localization
 */
class Localization 
{

    /**
     * @var string
     */
    public $defaultLang = 'ru';
    
    /**
     * @var string
     */
    public $defaultPath = 'lang';
    
    /**
     * @var string
     */
    public $fullPath = '';
    
    /**
     * @var string
     */
    private $currentLang = '';
    
    /**
     * @var array
     */
    protected $lines = [];

    /**
     * @var array
     */
    protected $newLines = [];

    /**
     * @var File
     */
    protected $file;
    
    /**
     * Init language
     * @params string $langCode
     * 
     * @throws LocalizationException
     */
    public function __construct($langCode = 'ru')
    {
        $file = File::of($fullPath = $this->defaultPath.DIRECTORY_SEPARATOR.$langCode);
        if (!$file->exists())
             throw new LocalizationException("Could not find \"{$langCode}\ language in file '{$fullPath}'");

        $lines             = Json::fromFile($fullPath);
        $this->lines       = ($lines == null) ? array() : $lines;
        $this->file        = $file;
        $this->currentLang = $langCode;
        $this->fullPath    = $fullPath;
    }

    /**
     * @return string
     */
    public function getCurrentLanguage(): string
    {
        return $this->currentLang;
    }
    
    /**
     * @return string
     */
    public function getDirectory(): string
    {
        return $this->file->getParent();
    }

    /**
     * @param string $line
     * @param string|number $value
     * 
     * @throws LocalizationException
     * 
     * @return Localization
     */
    public function set(string $line, ?string $value): Localization
    {
        $line = strtoupper(trim($line));
        if (empty($line))
            throw new LocalizationException("Line name cannot be empty");

        $this->newLines[$line] = $value;
        return $this;
    }
    
    /**
     * @param  array $lines
     * @return bool
     */
    public function setAll(array $lines): bool
    {
        $this->newLines = $lines;
        return $this->_update();
    }
    
    /**
     * @return bool
     */
    public function save(): bool
    {
        return $this->_update();
    }
    
    /**
     * @param string       $line
     * @param mixed|array  $params
     * 
     * @throws LocalizationException
     * 
     * @return string
     */
    public function get($line, ...$params): ?string
    {
        if (!isset($value = $this->lines[$line = strtoupper(trim($line))]))
            throw new LocalizationException("Line '{$line}' not found in current language configuration \"{$this->currentLang}\"");

        //return sprintf($value, ...$params);
        return $value;
    }

    /**
     * @return array
     */
    public function getAll(): array
    {
        return $this->lines;
    }
    
    /**
     * @param string      $format
     * @param array|mixed $params
     * 
     * @return string
     */
    protected function sprintf($format, ...$params): string
    {
        $int     = ['d', 'u', 'c', 'o', 'x', 'X', 'b'];
        $double  = ['g', 'G', 'e', 'E', 'f', 'F'];
        $matches = Regex::of("%(s|d|u|c|o|x|X|b|g|G|e|E|f|F)")->with($format); //%(.{1})
        
        if (count($matches = $matches->all()) !== count($params[0]))
            throw new LocalizationException();

        foreach ($matches as $key => $pattern)
        {
            $replacement = $params[0][$key];
            if (in_array($pattern[1], $int))
                $replacement = (int)$replacement;
            elseif (in_array($pattern[1], $double))
                $replacement = (double)$replacement;

            $format = &Regex::of($pattern[0])->with($format)->replace($replacement);
        }

        return $format;
    }
    
    /**
     * @return bool
     */
    protected function _update(): bool
    {
        if (!$this->lines)
            $newLines = array_merge($this->newLines, $this->lines);
        else
            $newLines = array_merge($this->lines, $this->newLines);
            
        $this->lines = $newLines;
        return $this->_write($newLines);
    }

    /**
     * @param string $data
     */
    protected function _write($data): bool
    {
        Json::toFile($file = $this->file->getAbsolutePath(), $data);
        return $this->file->exists() && (filesize($file) > 0);
    }

}