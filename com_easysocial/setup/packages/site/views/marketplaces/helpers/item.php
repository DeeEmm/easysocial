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

class EasySocialViewMarketplacesItemHelper extends EasySocial
{
	/**
	 * Determines the listing that is currently being viewed
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function getActiveListing()
	{
		static $listing = null;

		if (is_null($listing)) {
			$id = $this->input->get('id', 0, 'int');
			$listing = ES::marketplace($id);

			if (!$listing || !$listing->id) {
				return ES::raiseError(404, JText::_('COM_ES_MARKETPLACES_NO_FOUND'));
			}
		}

		return $listing;
	}
}
