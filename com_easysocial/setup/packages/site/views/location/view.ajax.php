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

class EasySocialViewLocation extends EasySocialSiteView
{
	/**
	 * Retrieves the location's caption for story form
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getStoryCaption()
	{
		$address = $this->input->get('address', '', 'default');

		$theme = ES::themes();
		$theme->set('address', $address);

		$output = $theme->output('site/location/story/caption');

		return $this->ajax->resolve($output);
	}

	/**
	 * Given an error code, decode it to the correct message
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getErrorMessage()
	{
		$errorCode = $this->input->get('code', 3, 'int');
		$errors = array(
					1 => JText::_('COM_EASYSOCIAL_LOCATION_PERMISSION_ERROR'),
					2 => JText::_('COM_EASYSOCIAL_LOCATION_TIMEOUT_ERROR'),
					3 => JText::_('COM_EASYSOCIAL_LOCATION_UNAVAILABLE_ERROR')
				);

		$error = $errors[$errorCode];
		
		return $this->ajax->resolve($error);
	}

	/**
	 * Formats the location
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function format()
	{
		$locations = $this->input->get('locations', '', 'default');

		$output = array();

		if ($locations) {
			foreach ($locations as $location) {
				$theme = ES::themes();
				$theme->set('location', $location);

				$output[] = $theme->output('site/location/story/suggestions');
			}
		}

		return $this->ajax->resolve($output);
	}
}
