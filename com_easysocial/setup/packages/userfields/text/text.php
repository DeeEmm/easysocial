<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2018 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

ES::import('admin:/includes/fields/dependencies');

class SocialFieldsUserText extends SocialFieldItem
{
	public function onRegister()
	{
		$text = $this->params->get('text');

		if (empty($text)) {
			return;
		}

		return $this->render($text);
	}

	public function onEdit()
	{
		$text = $this->params->get('text');

		if (empty($text)) {
			return;
		}

		return $this->render($text);
	}

	public function onDisplay()
	{
		$text = $this->params->get('text');

		if (empty($text)) {
			return;
		}

		return $this->render($text);
	}

	private function render($text)
	{
		$string = ES::string();

		if ($this->params->get('bbcode')) {
			// Don't allow html codes here
			$text = strip_tags($text);
			$text = $string->parseBBCode($text);

		} else {
			$text = $string->escape($text);
		}

		$this->set('text', $text);

		return $this->display();
	}
}
