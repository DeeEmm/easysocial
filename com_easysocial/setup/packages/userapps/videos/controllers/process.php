<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

class VideosControllerProcess extends SocialAppsController
{
	/**
	 * Processes a video
	 *
	 * @since   1.4
	 * @access  public
	 */
	public function process()
	{
		// Determines the type of video being inserted
		$type = $this->input->get('type', '', 'word');

		$method = 'process' . ucfirst($type);

		$this->$method();
	}

	/**
	 * Processes story creation via video uploads
	 *
	 * @since   1.4
	 * @access  public
	 */
	public function processUpload()
	{
	}

	/**
	 * Processes story creation via external video linking
	 *
	 * @since   1.4
	 * @access  public
	 */
	public function processLink()
	{
		$link = $this->input->get('link', '', 'default');
		$link = trim($link);

		// We need to format the url properly.
		$video = ES::video();

		if ($video->hasExceededLimit()) {
			return $this->ajax->reject(JText::_('COM_EASYSOCIAL_VIDEOS_EXCEEDED_LIMIT'));
		}

		$link = $video->format($link);

		$crawler = ES::crawler();
		$data = $crawler->scrape($link);

		// Before we proceed, we need to ensure that $data->oembed is really exists.
		// If not exists, throw the appropriate error message to the user.
		if (!$data || !isset($data->oembed) || !$data->oembed || empty($data->oembed) || is_null($data->oembed) || isset($data->oembedError) || (!isset($data->oembed->html) && !isset($data->opengraph->video))) {

			$message = JText::_('COM_EASYSOCIAL_VIDEO_LINK_EMBED_NOT_SUPPORTED');

			if (isset($data->oembedError) && $data->oembedError) {
				$message = $data->oembedError;
			}

			return $this->ajax->reject($message);
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

		if (!isset($data->oembed->html) && isset($data->opengraph->video)) {
			$html = '<iframe src="' . $data->opengraph->video . '" frameborder="0"></iframe>';
		}

		return $this->ajax->resolve($data, $thumbnail, $html);
	}
}
