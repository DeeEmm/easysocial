<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2017 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

class EasySocialViewVideos extends EasySocialSiteView
{
	/**
	 * Displays the single video item
	 *
	 * @since	1.4
	 * @access	public
	 */
	public function item()
	{
		// Get the video id
		$id = $this->input->get('id', 0, 'int');

		$table = ES::table('Video');
		$table->load($id);

		// Load up the video
		$video = ES::video($table->uid, $table->type, $table);

		// Ensure that the viewer can really view the video
		if (!$id || !$video->id || !$video->isViewable() || !$this->config->get('video.layout.item.embed')) {
			die();
		}

		ES::initialize();

		$environment = $this->config->get('general.environment');
		$minified = true;
		if ($environment == 'development') {
			$minified = false;
		}

		$theme = strtolower($this->config->get('theme.site'));
		$stylesheet = ES::stylesheet('site', $theme);
		$stylesheet->process($minified, true, false);
		$cssFiles = array_keys($stylesheet::$attached);

		$jsFiles = array();
		$scripts = ES::scripts();

		if ($environment == 'development') {
			$root = rtrim(JURI::root(), '/');

			// Render the bootloader on the page first
			$jsFiles[] = $root . '/media/com_easysocial/scripts/bootloader.js';

			$dependencies = $scripts->getDependencies(false, true);

			// Render dependencies from the core
			foreach ($dependencies as $dependency) {
				$jsFiles[] = $root . '/media/com_easysocial/scripts/vendors/' . $dependency;
			}

			// Render easysocial's dependencies
			$jsFiles[] = $root . '/media/com_easysocial/scripts/site/site.js';
		} else {
			$jsFiles[] = $scripts->getFileUri('site', true, $this->config->get('general.jquery'));
		}

		// Whenever a viewer visits a video, increment the hit counter
		$video->hit();

		$this->set('jsFiles', $jsFiles);
		$this->set('cssFiles', $cssFiles);
		$this->set('video', $video);

		parent::display('site/videos/item/embed');
	}
}
