<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2017 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

ES::import('site:/views/views');

class EasySocialViewBadges extends EasySocialSiteView
{
	/**
	 * Renders the achievers of a badge
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function loadAchievers($achievers, $nextlimit)
	{
		if ($this->hasErrors()) {
			return $this->ajax->reject($this->getMessage());
		}

		$html = '';

		if ($achievers) {
			foreach ($achievers as $user) {
				$theme = ES::themes();
				$html .= $theme->output('site/badges/item/achiever', array('user' => $user));
			}
		}

		return $this->ajax->resolve($html, $nextlimit);
	}
}
