<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

class ThemesHelperEmail
{
	/**
	 * Renders the button in the e-mail template
	 *
	 * @since	4.0.4
	 * @access	public
	 */
	public function button($text, $link)
	{
		$theme = ES::themes();
		$theme->set('text', $text);
		$theme->set('link', $link);
		$html = $theme->output('site/emails/helpers/button');

		return $html;
	}

	/**
	 * Renders the divider section of an e-mail
	 *
	 * @since	4.0.4
	 * @access	public
	 */
	public function divider()
	{
		static $html = null;

		if (is_null($html)) {
			$theme = ES::themes();
			$html = $theme->output('site/emails/helpers/divider');
		}

		return $html;
	}

	/**
	 * Renders the heading of an e-mail
	 *
	 * @since	4.0.4
	 * @access	public
	 */
	public function heading($title, $subtitle = '')
	{
		$title = JText::_($title);
		$subtitle = JText::_($subtitle);

		$theme = ES::themes();
		$theme->set('title', $title);
		$theme->set('subtitle', $subtitle);

		$html = $theme->output('site/emails/helpers/heading');

		return $html;
	}
}
