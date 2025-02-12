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

class EasySocialViewDiscussions extends EasySocialSiteView
{
	/**
	 * Post processing after saving a new discussion
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function save($redirect)
	{
		return $this->redirect($redirect);
	}

	/**
	 * Post proccessing after deleting a discussion
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function delete($cluster)
	{
		// Get the redirection url
		$redirect = $cluster->getAppPermalink('discussions', false);

		return $this->redirect($redirect);
	}
}
