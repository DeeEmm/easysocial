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

ES::import('site:/views/views');

class EasySocialViewCover extends EasySocialSiteView
{
	/**
	 * Displays the upload cover dialog
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function uploadDialog()
	{
		ES::requireLogin();

		$uid = $this->input->get('uid', 0, 'int');
		$type = $this->input->get('type', '', 'cmd');

		$theme = ES::themes();
		$theme->set('uid', $uid);
		$theme->set('type', $type);

		$output = $theme->output('site/profile/dialogs/cover.upload');

		return $this->ajax->resolve($output);
	}

	/**
	 * Post processing after a photo cover is uploaded on the site
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function upload($photo = null)
	{
		if ($this->hasErrors()) {
			return $this->ajax->reject($this->getMessage());
		}

		// Get the photo in json format
		$data = $photo->export();

		return $this->ajax->resolve($data);
	}

	/**
	 * Post processing after creating a cover from a photo.
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function create($cover = null)
	{
		if ($this->hasErrors()) {
			return $this->ajax->reject($this->getMessage());
		}

		$result = new stdClass();
		$result->url = $cover->getSource();
		$result->position = $cover->getPosition();
		$result->x = $cover->x;
		$result->y = $cover->y;

		return $this->ajax->resolve($result);
	}


	/**
	 * Post process after a cover is deleted
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function remove()
	{
		if ($this->hasErrors()) {
			return $this->ajax->reject($this->getMessage());
		}

		$cover = ES::table('Cover');
		$cover->type = $this->input->getCmd('type');

		return $this->ajax->resolve($cover->getSource());
	}
}
