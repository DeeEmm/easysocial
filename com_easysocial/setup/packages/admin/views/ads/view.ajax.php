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

class EasySocialViewAds extends EasySocialAdminView
{
	/**
	 * Displays confirmation dialog before deleting ads
	 *
	 * @since	3.0
	 * @access	public
	 */
	public function confirmDelete()
	{
		$theme = ES::themes();
		$output = $theme->output('admin/ads/dialog.delete');

		return $this->ajax->resolve($output);
	}

	/**
	 * Displays confirmation dialog before deleting advertiser
	 *
	 * @since	3.0
	 * @access	public
	 */
	public function confirmDeleteAdvertiser()
	{
		$theme = ES::themes();
		$output = $theme->output('admin/ads/advertisers/dialog.delete');

		return $this->ajax->resolve($output);
	}

	/**
	 * Allows caller to browse Advertisers
	 *
	 * @since	3.1
	 * @access	public
	 */
	public function browse()
	{
		$callback = $this->input->get('jscallback', '', 'cmd');

		$theme = ES::themes();
		$theme->set('callback', $callback);
		$content = $theme->output('admin/ads/advertisers/browse');

		return $this->ajax->resolve($content);
	}
}
