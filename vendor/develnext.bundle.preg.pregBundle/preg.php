<?php
/**
 * Становятся доступa preg_* функции
 *
 * Обязательно, чтоб функции были объявлены в корневом namespace
 */

namespace;

// Для autoloader'а
class preg{

}

use bundle\preg\Preg;
use php\framework\Logger;
use php\util\Regex;

define('PREG_PATTERN_ORDER', 1 << 0);
define('PREG_SET_ORDER', 1 << 1);
define('PREG_GREP_INVERT', 1 << 2);

/**
 * --RU--
 * Выполняет глобальный поиск шаблона в строке
 * 
 * @link    http://php.net/manual/function.preg-match-all.php
 *
 * @param   string  $pattern  Искомый шаблон
 * @param   string  $subject  Входная строка
 * @param   array   $matches  (optional)  Параметр будет заполнен результатами поиска
 * @param   int     $flags    (optional)  Возможные значения - PREG_PATTERN_ORDER, PREG_SET_ORDER
 * @return  int
 */
function preg_match_all($pattern, $subject, &$matches = null, $flags = PREG_PATTERN_ORDER){
	$preg = new Preg($pattern, $subject);
	$preg->compile();
	$matches = [];

	while ($preg->find()){
		if($flags & PREG_PATTERN_ORDER){
			foreach($preg->matches() as $k=>$v){
				$matches[$k][] = $v;
			}
		}
		elseif($flags & PREG_SET_ORDER){
			$matches[] = $preg->matches();
		}
	}

	return isset($matches[0]) ? sizeof($matches[0]) : 0;
}

/**
 * --RU--
 * Выполняет проверку на соответствие регулярному выражению
 * 
 * @link    http://php.net/manual/function.preg-match.php
 * 
 * @param   string  $pattern  Искомый шаблон
 * @param   string  $subject  Входная строка
 * @param   array   $matches  (optional)  Параметр будет заполнен результатами поиска
 * @return  int
 */
function preg_match($pattern, $subject, &$matches = null){
	// todo: добавить поддержку параметров $flags, $offset
	$preg = new preg($pattern, $subject);
	$preg->compile();
	$matches = [];

	if($preg->find()){
		$matches = $preg->matches();
	}

	return sizeof($matches);
}

/**
 * --RU--
 * Возвращает массив вхождений, которые соответствуют шаблону
 * 
 * @link    http://php.net/manual/function.preg-grep.php
 * 
 * @param   string  $pattern  Искомый шаблон
 * @param   array   $subject  Входящий массив
 * @param   int     $flags    (optional)  Возможное значение - PREG_GREP_INVERT
 * @return  array
 */
function preg_grep($pattern, $input, $flags = 0){
	$return = [];
	foreach($input as $subject){
		$preg = new preg($pattern, $subject);
		$preg->compile();
		$find = $preg->find();

		if(
			($find && !($flags & PREG_GREP_INVERT)) ||
			(!$find && ($flags & PREG_GREP_INVERT)) 
			){
				$return[] = $subject;
		}
		
		//PREG_GREP_INVERT
	}

	return $return;
}

/**
 * --RU--
 * Выполняет поиск и замену по регулярному выражению
 * 
 * @link    http://php.net/manual/function.preg-replace.php
 * 
 * @param   mixed  $pattern      Искомый шаблон. Может быть как строкой, так и массивом строк.
 * @param   mixed  $replacement  Строка или массив строк для замены
 * @param   mixed  $subject      Строка или массив строк для поиска и замены
 * @return  mixed  Строка или массив, в зависимости от параметра $subject
 */
function preg_replace($pattern, $replacement, $subject){
	// todo: поддержка параметров $limit, $count
	if(is_array($subject)){
		foreach($subject as $k=>$one){
			$subject[$k] = preg_replace($pattern, $replacement, $one);
		}

		return $subject;
	}

	if(is_array($pattern)){
		foreach($pattern as $k=>$p){
			$replaceItem = (
				(is_array($replacement)) 
					? (
						(isset($replacement[$k]))
						? $replacement[$k]
						: end($replacement)
					  ) 
					: $replacement
			);
			$subject = preg_replace($p, $replaceItem, $subject);
		}
		return $subject;
	}

	try{
		$preg = new preg($pattern, $subject);
		$preg->compile();
		return $preg->replace($replacement);
	} catch (\php\lang\IllegalArgumentException $e){
		Logger::Warn('preg_replace: ' . $e->getMessage());
		return NULL;
	}
}

/**
 * --RU--
 * Выполняет поиск по регулярному выражению и замену с использованием callback-функции
 * 
 * @link    http://php.net/manual/function.preg-replace-callback.php
 * 
 * @param   mixed     $pattern   Искомый шаблон (строка или массив)
 * @param   callable  $callback  Вызываемая callback-функция function( array $matches )
 * @param   mixed     $subject   Строка или массив для поиска и замены
 * @return  mixed     Строка или массив, в зависимости от параметра $subject
 */
function preg_replace_callback($pattern, $callback, $subject){
	if(is_array($subject)){
		foreach($subject as $k=>$one){
			$subject[$k] = preg_replace_callback($pattern, $callback, $one);
		}

		return $subject;
	}

	if(is_array($pattern)){
		foreach($pattern as $k=>$p){
			$subject = preg_replace_callback($p, $callback, $subject);
		}
		return $subject;
	}

	try{
		$preg = new preg($pattern, $subject);
		$preg->compile();
		return $preg->replaceCallback($callback);
	} catch (\php\lang\IllegalArgumentException $e){
		Logger::Warn('preg_replace_callback: ' . $e->getMessage());
		return NULL;
	}
}

/**
 * --RU--
 * Разбивает строку по регулярному выражению
 * 
 * @link    http://php.net/manual/function.preg-split.php
 * 
 * @param   string  $pattern  Строка, содержащая искомый шаблон.
 * @param   mixed   $subject  Входная строка
 * @param   int     $limit    (optional)  Если указан, функция возвращает не более, чем limit подстрок
 * @return  array
 */
function preg_split($pattern, $subject, $limit = 0){
	$preg = new preg($pattern, $subject);
	$p = $preg->getPattern();
	return Regex::split($p, $subject, $limit);
}

/**
 * --RU--
 * Экранирует символы в регулярных выражениях
 * 
 * @link    http://php.net/manual/function.preg-quote.php
 * 
 * @param   string  $str        Входная строка
 * @param   string  $delimiter  (optional)  Символ, который будет также экранироваться
 * @return  string
 */
 function preg_quote($str, $delimiter = null){
 	$symbols = ['.', "\\", '+', '*', '?', '[', '^', ']', '$', '(', ')', '{', '}', '=', '!', '<', '>', '|', ':', '-', $delimiter];
 	foreach ($symbols as $symbol) {
 		$str = str_replace($symbol, "\\" . $symbol, $str);
 	}
 	return $str;
 }