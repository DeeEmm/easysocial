<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) 2010 - 2017 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

// Import parent view
ES::import('site:/views/views');

class EasySocialViewAudios extends EasySocialSiteView
{
	/**
	 * Post process after an audio has been uploaded via story form.
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function uploadStory(SocialAudio $audio)
	{
		$response = new stdClass();

		if ($this->hasErrors()) {
			$response->error = $this->getMessage();

			return $this->json->send($response);
		}

		$audioData = new stdClass();
		$audioData->id = $audio->table->id;
		$audioData->title = $audio->table->title;
		$audioData->description = $audio->table->description;
		$audioData->link = '';
		$audioData->artist = $audio->table->artist;
		$audioData->artist = $audio->table->album;

		$response->error = false;
		$response->data = $audioData;
		$response->thumbnail = $audio->getAlbumArt();
		$response->html = $audio->getEmbedCodes();

		// This needs to respect the settings whether on the fly conversion should be supported or not.
		$response->isEncoding = $audio->isProcessing();

		return $this->json->send($response);
	}
}
