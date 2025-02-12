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

class EasySocialViewMarketplaces extends EasySocialAdminView
{
	/**
	 * Post process after a video has been uploaded via story form.
	 *
	 * @since	1.4
	 * @access	public
	 */
	public function uploadPhotos($uri, $path, $inputName)
	{
		$response = new stdClass();

		if ($this->hasErrors()) {
			$response->error = $this->getMessage();

			return $this->json->send($response);
		}

		// Photo html
		$theme = ES::themes();
		$theme->set('uri', $uri);
		$theme->set('path', $path);
		$theme->set('inputName', $inputName);

		$html = $theme->output('site/marketplaces/create/photo.item');

		$response = new stdClass();
		$response->html = $html;

		return $this->json->send($response);
	}
}
