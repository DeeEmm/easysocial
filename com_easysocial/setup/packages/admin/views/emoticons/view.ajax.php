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

class EasySocialViewEmoticons extends EasySocialAdminView
{
	/**
	 * Displays confirmation dialog before deleting Emoticons
	 *
	 * @since	3.0
	 * @access	public
	 */
	public function confirmDelete()
	{
		$theme = ES::themes();
		$output = $theme->output('admin/emoticons/dialog.delete');

		return $this->ajax->resolve($output);
	}

	/**
	 * Allows caller to browse emojis
	 *
	 * @since	3.0
	 * @access	public
	 */
	public function browseEmojis()
	{
		$theme = ES::themes();

		$library = SOCIAL_CONFIG_DEFAULTS . '/emoticons.json';

		$contents = file_get_contents($library);
		$emojis = json_decode($contents);
		
		$theme->set('emojis', $emojis);
		$content = $theme->output('admin/emoticons/browse.emojis');

		return $this->ajax->resolve($content);
	}

}