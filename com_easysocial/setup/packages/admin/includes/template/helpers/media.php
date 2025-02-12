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

class ThemesHelperMedia extends ThemesHelperAbstract
{
	/**
	 * Displays the stream's page title
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function navigation($navigation, $uid, $type, $mediaType)
	{
		$prevImage = '';
		$nextImage = '';
		$getImageAction = $mediaType == 'audio' ? 'getAlbumArt' : 'getThumbnail';

		if ($navigation->prev) {
			$prevImage = $navigation->prev->$getImageAction();
		}

		if ($navigation->next) {
			$nextImage = $navigation->next->$getImageAction();
		}

		$themes = ES::themes();
		$themes->set('uid', $uid);
		$themes->set('type', $type);
		$themes->set('prevImage', $prevImage);
		$themes->set('nextImage', $nextImage);
		$themes->set('navigation', $navigation);

		$output = $themes->output('site/helpers/media/navigation');

		return $output;
	}
}
