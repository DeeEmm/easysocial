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

class EasySocialViewMembers extends EasySocialSiteView
{
	/**
	 * Responsible to output the JSON object of a result when searched.
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function suggest($result)
	{
		// If there's nothing, just return the empty object.
		if (!$result) {
			return $this->ajax->resolve(array());
		}

		$items = array();
		$objects = array();

		// Determines if we should use a specific input name
		$inputName = $this->input->get('inputName', '', 'default');

		foreach ($result as $user) {
			$theme = ES::themes();
			$theme->set('user', $user);
			$theme->set('inputName', $inputName);

			$items[] = $theme->output('site/friends/suggest/item');
		}
		return $this->ajax->resolve($items);
	}
}