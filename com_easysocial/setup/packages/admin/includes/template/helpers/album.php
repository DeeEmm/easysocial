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

class ThemesHelperAlbum extends ThemesHelperAbstract
{
	/**
	 * Generates the report button for an album
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function report($album)
	{
		static $output = array();

		$index = $album->id;

		if (!isset($output[$index])) {

			// Ensure that the user is allowed to report objects on the site
			if (!$this->config->get('reports.enabled') || !$this->access->allowed('reports.submit')) {
				return;
			}

			$reports = ES::reports();

			// Reporting options
			$options = [
				'dialogTitle' => 'COM_EASYSOCIAL_ALBUMS_REPORT_ALBUM_TITLE',
				'dialogContent' => 'COM_EASYSOCIAL_ALBUMS_REPORT_DESC',
				'title' => $album->_('title'),
				'permalink' => $album->getPermalink(true, true),
				'type' => 'button',
				'actorid' => $album->user_id
			];

			$output[$index] = $reports->form(SOCIAL_TYPE_ALBUM, $album->id, $options);
		}

		return $output[$index];
	}

	/**
	 * Renders the private messaging button for users
	 *
	 * @since	2.0
	 * @access	public
	 * @param	SocialUser 	This would be the target user
	 * @return
	 */
	public function bookmark($album, $iconOnly = true)
	{
		$options = array();
		$options['url'] = $album->getPermalink(false, true);
		$options['display'] = 'dialog';

		$sharing = ES::sharing($options);

		$output = $sharing->button();

		return $output;
	}
}
