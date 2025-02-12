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

class EasySocialViewBroadcasts extends EasySocialSiteView
{
	/**
	 * Displays confirmation dialog before deleting broadcast
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function confirmDelete()
	{
		$theme = ES::themes();
		$output = $theme->output('site/streams/broadcasts/dialog.delete');

		return $this->ajax->resolve($output);
	}

	/**
	 * Post process of the broadcast delete
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function delete()
	{
		if ($this->hasErrors()) {
			return $this->ajax->reject($this->getMessage());
		}

		$redirectUrl = ESR::dashboard();
		return $this->ajax->resolve($redirectUrl);
	}
}
