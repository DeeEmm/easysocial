<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

class EasySocialViewAds extends EasySocialSiteView
{
	/**
	 * Displays the confirmation to delete a ad
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function confirmDelete()
	{
		// Only logged in users are allowed here.
		ES::requireLogin();

		// Get the ad object
		$id = $this->input->get('id', 0, 'int');
		$ad = ES::ad($id);

		$theme = ES::themes();
		$theme->set('ad', $ad);
		$contents = $theme->output('site/ads/dialogs/delete');

		return $this->ajax->resolve($contents);
	}
}
