<?php

use bundle\gignight\Localization;
use bundle\gignight\exception\LocalizationException;

/**
 * @property string $line Line name of string
 * @property string $code Language code
 * 
 * @throws \LocalizationException
 * 
 * @return string
 */
public function __($line, $code = 'ru')
{
    return new Localization($code)->get($line);
}
