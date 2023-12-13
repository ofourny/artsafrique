<?php

/**
 * Copyright (c) 2008-13 Owebia
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"),
 * to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense,
 * and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS
 * IN THE SOFTWARE.
 *
 * @website    http://www.owebia.com/
 * @project    Magento Owebia Shipping 2 module
 * @author     Antoine Lemoine
 * @license    http://www.opensource.org/licenses/MIT  The MIT License (MIT)
**/

class Owebia_Shipping2_Helper_Data extends Mage_Core_Helper_Data
{
	protected $_translate_inline;

	public function __() {
		$args = func_get_args();
		if (isset($args[0]) && is_array($args[0]) && count($args)==1) {
			$args = $args[0];
		}
		$message = array_shift($args);
		if ($message instanceof OS_Message) {
			$args = $message->args;
			$message = $message->message;
		}
		
		$output = parent::__($message);
		
		/*if (true) {
			$translations = @file_get_contents('translations.os2');
			$translations = eval('return '.$translations.';');
			if (!is_array($translations)) $translations = array();

			$file = 'NC';
			$line = 'NC';
			$backtrace = debug_backtrace();
			foreach ($backtrace as $trace) {
				if (!isset($trace['function'])) continue;
				if (substr($trace['function'], strlen($trace['function'])-2, strlen($trace['function']))=='__') {
					$file = ltrim(str_replace(Mage::getBaseDir(), '', $trace['file']), '/');
					$line = $trace['line'];
					continue;
				}
				//$file = ltrim(str_replace(Mage::getBaseDir(), '', $trace['file']), '/');
				//echo $file.', '.$trace['function'].'(), '.$line.', '.$message.'<br/>';
				break;
			}

			$translations[Mage::app()->getLocale()->getLocaleCode()][$file][$message] = $output;
			ksort($translations[Mage::app()->getLocale()->getLocaleCode()]);
			file_put_contents('translations.os2', var_export($translations, true));
		}*/

		if (count($args)==0) {
			$result = $output;
		} else {
			if (!isset($this->_translate_inline)) $this->_translate_inline = Mage::getSingleton('core/translate')->getTranslateInline();
			if ($this->_translate_inline) {
				$parts = explode('}}{{', $output);
				$parts[0] = vsprintf($parts[0], $args);
				$result = implode('}}{{', $parts);
			} else  {
				$result = vsprintf($output, $args);
			}
		}
		return $result;
	}
}
