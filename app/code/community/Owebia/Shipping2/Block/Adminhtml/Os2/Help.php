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

class Owebia_Shipping2_Block_Adminhtml_Os2_Help extends Mage_Adminhtml_Block_Abstract
{
	public function __() {
		$args = func_get_args();
		return Mage::helper('owebia-shipping2')->__($args);
	}

	public function getHtml() {
		$controller = $this->getData('controller');
		$help_id = $this->getData('help_id');
		$content = $this->getData('content');
		$helper = $this->getData('helper');
		$content = str_replace(
			array("\\t", "<c>", "<c class=new>", "</c>", "<string>", "</string>", "<property>", "</property>"),
			array('&nbsp;&nbsp;&nbsp;', "<span class=code>", "<span class=\"code new\">", "</span>", "<span class=code><span class=string>", "</span></span>", "<span class=property>", "</span>"),
			$content);
		$header = null;
		$footer = null;
		$title = null;
		if ($help_id=='changelog') {
			$changelog = @file_get_contents($controller->getModulePath('changelog'));
			if (!$changelog) $changelog = "Empty changelog";
			$changelog = mb_convert_encoding($changelog, 'UTF-8', 'ISO-8859-1');
			if (!$changelog) $changelog = "Encoding error";
			$changelog = htmlspecialchars($changelog, ENT_QUOTES, 'UTF-8');
			$changelog = str_replace("\n", "<br/>", $changelog);
			$content = str_replace('{changelog}', $changelog, $content);
		}
		while (preg_match('#{code=json}(.*?){/code}#s', $content, $result)) {
			$json = str_replace("\r\n", '', $result[1]);
			try {
				$json = Zend_Json::decode($json);
			} catch (Exception $e) {}
			$content = str_replace($result[0], "<div class=code>".$helper::jsonEncode($json, $beautify = true, $html = true)."</div>", $content);
		}
		if (preg_match('#<h4>(.*)</h4>#', $content, $result)) {
			$title = $result[1];
			$content = str_replace($result[0], '', $content);
		}
		$nav = "<div id=os2-help-nav><a href=\"#\" onclick=\"os2editor.refreshHelp();\">".$this->__('Refresh')."</a> | <a href=\"#\" onclick=\"os2editor.previousHelp();\">".$this->__('Previous page')."</a>".($help_id!='summary' ? " | <a href=\"#summary\">".$this->__('Summary')."</a>" : '')."</div>";
		$header = "<div class=\"ui-layout-north os2-help-header\">{$nav}<h4>{$title}</h4></div>";
		$content = ($header ? "{$header}" : '')."<div id=os2-help class=ui-layout-center>{$content}</div>";
		$content = preg_replace('/ href="#([a-z0-9_\-\.]+)"/', ' href="#" onclick="os2editor.help(\'\1\');"', $content);
		return $content;
	}
}
