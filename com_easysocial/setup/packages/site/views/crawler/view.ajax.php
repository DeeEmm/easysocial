<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2019 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

class EasySocialViewCrawler extends EasySocialSiteView
{
	/**
	 * Validates a link and ensure that the link is valid
	 *
	 * @since	1.4.8
	 * @access	public
	 */
	public function validate($valid)
	{
		if ($valid) {
			return $this->ajax->resolve();
		}

		return $this->ajax->reject();

	}

	/**
	 * Post processing after crawling given urls
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function fetch(SocialLinks $link, $data = false)
	{
		// Determines if the preview is needed
		$showPreview = $this->input->get('preview', false, 'bool');
		$preview = '';

		if ($showPreview) {

			if (!$data) {
				$data = $link->getData();
			}

			if (!$data || ($data && isset($data->oembedError) && $data->oembedError)) {

				$message = JText::_('COM_ES_LINKS_NOT_EXISTS', true);

				if (isset($data->oembedError) && $data->oembedError) {
					$message = $data->oembedError;
				}

				return $this->ajax->reject($message);
			}

			$allowRemoveThumbnail = true;

			// If the crawler data doesn't have any image, then show remove thumbnail option
			if (isset($data->images) && !$data->images) {
				$allowRemoveThumbnail = false;
			}

			if (isset($data->oembed->html) && !empty($data->oembed->html)) {
				$allowRemoveThumbnail = false;
			}

			$isEmbedExternalVideo = false;

			if ($link->table->renderEmbedExternalVideoLink()) {
				$isEmbedExternalVideo = true;
				$data->externalVideoProviderEmbed = $data->oembed;
			}

			$theme = ES::themes();
			$theme->set('link', $data);
			$theme->set('allowRemoveThumbnail', $allowRemoveThumbnail);
			$theme->set('isEmbedExternalVideo', $isEmbedExternalVideo);

			$preview = $theme->output('site/story/links/preview');
		}

		return $this->ajax->resolve($data, $preview);
	}
}
