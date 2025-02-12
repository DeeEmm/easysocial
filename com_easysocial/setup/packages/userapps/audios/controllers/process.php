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

class AudiosControllerProcess extends SocialAppsController
{
	/**
	 * Processes an audio
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function process()
	{
		// Determines the type of audio being inserted
		$type = $this->input->get('type', '', 'word');

		$method = 'process' . ucfirst($type);

		$this->$method();
	}

	/**
	 * Processes story creation via external audio linking
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function processLink()
	{
		$link = $this->input->get('link', '', 'default');
		$link = trim($link);

		// We need to format the url properly.
		$audio = ES::audio();

		if ($audio->hasExceededLimit()) {
			return $this->ajax->reject(JText::_('COM_ES_AUDIO_EXCEEDED_LIMIT'));
		}

		if (!$audio->isValidUrl($link)) {
			return $this->ajax->reject(JText::_('COM_ES_AUDIO_LINK_EMBED_NOT_SUPPORTED'));
		}

		$link = $audio->format($link);

		$crawler = ES::crawler();
		$data = $crawler->scrape($link);

		if (!isset($data->oembed) || !$data->oembed) {
			return $this->ajax->reject(JText::_('COM_ES_AUDIO_LINK_EMBED_NOT_SUPPORTED'));
		}

		$oembed = (array) $data->oembed;

		// Before we proceed, we need to ensure that $data->oembed is really exists.
		// If not exists, throw the appropriate error message to the user.
		if (empty($oembed)) {
			return $this->ajax->reject(JText::_('COM_ES_AUDIO_LINK_EMBED_NOT_SUPPORTED'));
		}

		$html = '';
		$thumbnail = '';

		// If there is an oembed property, try to use it.
		if (isset($data->oembed->html)) {
			$html = $data->oembed->html;
		}

		if (isset($data->oembed->thumbnail_url)) {
			$thumbnail = $data->oembed->thumbnail_url;
		}

		// If there is no thumbnail, we should try to get from the opengraph tag if it exists
		if (!isset($data->oembed->thumbnail_url) && isset($data->opengraph->image)) {
			$thumbnail = $data->opengraph->image;
		}

		if (!isset($data->oembed->html) && isset($data->opengraph->audio)) {
			$html = '<iframe src="' . $data->opengraph->audio . '" frameborder="0"></iframe>';
		}

		return $this->ajax->resolve($data, $thumbnail, $html);
	}
}
