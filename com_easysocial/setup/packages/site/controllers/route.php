<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2018 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

class EasySocialControllerRoute extends EasySocialController
{
	/**
	 * Determines if the view should be visible on lockdown mode
	 *
	 * @since	2.2.2
	 * @access	public
	 */
	public function isLockDown($task)
	{
		$allowed = array('sef');

		if (in_array($task, $allowed)) {
			return false;
		}
		return true;
	}

	/**
	 * Used by backend to generate sef link if site running sef mode.
	 *
	 * @since   2.2.2
	 * @access  public
	 */
	public function sef()
	{
		// Redirect
		$url = $this->input->get('url', '', 'default');

		if ($url) {
			$url = base64_decode($url);
		}

		$response = new stdClass();
		$response->link = '';

		if ($url) {
			$link = ESR::_($url, false);
			$response->link = $link;
		}

		header('Content-type: text/x-json; UTF-8');
		echo json_encode($response);
		exit;
	}
}
