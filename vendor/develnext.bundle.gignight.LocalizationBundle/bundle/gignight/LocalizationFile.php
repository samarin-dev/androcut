<?php
namespace bundle\gignight;

use bundle\gignight\exception\LocalizationFileNotFoundException;
use php\lib\fs;
use php\io\File;

/**
 * Class LocalizationFile
 * 
 * @author    GIGNIGHT
 * @copyright 2019 - 2020 GIGNIGHT
 * @license   MIT https://opensource.org/licenses/MIT
 * @link      http://github.com/GIGNIGHT/dn-localization-ext
 * @packages  localization
 */
class LocalizationFile extends Localization
{

    /**
     * @var File
     */
    private $_file;

    /**
     * @var string
     */
    private $code;

    /**
     * @throws LocalizationException
     */
    public function __construct($file = '')
    {
        if ($file != null)
        {
            $this->_file = new File(fs::normalize( $file == null ? $this->defaultPath.DIRECTORY_SEPARATOR.$this->defaultLang : $file ));
            $this->code  = trim($this->_file->getName());
            $this->create($file);
        }
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->code;
    }

    /**
     * @param  string $file This is save path
     * 
     * @throws \LocalizationException
     * 
     * @return Localization
     */
    public function create($file): Localization
    {
        if ($this->_file->exists())
            return new Localization($this->_file->getName());

        $isCreated = $this->_file->createNewFile(!is_dir($this->_file->getParent()));
        if (!$isCreated)
            throw new LocalizationFileNotFoundException("Error creating file");

        return new Localization($this->_file->getName());
    }
    
    /**
     * @return bool
     */
    public function delete(): bool
    {
        return $this->_file->delete();
    }

}