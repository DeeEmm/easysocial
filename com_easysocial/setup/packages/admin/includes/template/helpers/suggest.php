<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2016 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

class ThemesHelperSuggest extends ThemesHelperAbstract
{
	/**
	 * Generates hints for hashtags
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function hashtags()
	{
		$theme = ES::themes();

		$output = $theme->output('site/helpers/suggest/hashtags.hints');

		return $output;
	}

	/**
	 * Generates hints for hashtags
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function friends()
	{
		$theme = ES::themes();

		$output = $theme->output('site/helpers/suggest/friends.hints');

		return $output;
	}

	/**
	 * Generates hints for emoticons
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function emoticons()
	{
		$theme = ES::themes();

		$output = $theme->output('site/helpers/suggest/emoticons.hints');

		return $output;
	}

	/**
	 * Generates hints for mention autocomplete
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function autocomplete()
	{
		$theme = ES::themes();

		$output = $theme->output('site/helpers/suggest/autocomplete.hints');

		return $output;
	}
}
