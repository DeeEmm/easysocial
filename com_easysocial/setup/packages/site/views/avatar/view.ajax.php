<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2016 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

ES::import('site:/views/views');

class EasySocialViewAvatar extends EasySocialSiteView
{
	/**
	 * Renders the dialog for users to upload picture for their profile or cluster
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function upload()
	{
		// Only allow logged in users
		ES::requireLogin();

		// Get the unique item id
		$uid = $this->input->get('uid', 0, 'int');
		$type = $this->input->get('type', '', 'cmd');
		$return = $this->getReturnUrl();
		$isMobile = ES::responsive()->isMobile() ? '_MOBILE' : '';

		$theme = ES::themes();
		$theme->set('uid', $uid);
		$theme->set('type', $type);
		$theme->set('isMobile', $isMobile);
		$theme->set('return', $return);

		$output = $theme->output('site/avatar/dialogs/upload');

		return $this->ajax->resolve($output);
	}

	/**
	 * Displays the dialog to allow user to crop avatar
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function crop()
	{
		ES::requireLogin();

		// Get the unique item id
		$uid = $this->input->get('uid', 0, 'int');
		$type = $this->input->get('type', '', 'cmd');

		// Get photo id
		$photoId = $this->input->get('id', 0, 'int');
		$table = ES::table('Photo');
		$table->load($photoId);

		// Get redirect url after avatar is created
		$defaultRedirect = ESR::referer();

		// Try to get redirect url from the url
		$redirectUrl = $this->getReturnUrl($defaultRedirect);

		// Load up the library
		$lib = ES::photo($table->uid, $table->type, $table);

		if (!$table->id) {
			return $this->deleted($lib);
		}

		// Check if the user is really allowed to upload avatar
		if (!$lib->canUseAvatar()) {
			return $this->ajax->reject('You are not allowed to use this avatar');
		}

		$redirect = false;

		if ($redirectUrl) {
			$redirect = true;
		}

		$theme = ES::themes();

		$theme->set('uid', $uid);
		$theme->set('type', $type);
		$theme->set('redirectUrl', $redirectUrl);
		$theme->set('photo', $lib->data);
		$theme->set('redirect', $redirect);

		$output = $theme->output('site/avatar/dialogs/crop');

		return $this->ajax->resolve($output);
	}
}
