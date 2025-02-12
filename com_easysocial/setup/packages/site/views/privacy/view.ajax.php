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

class EasySocialViewPrivacy extends EasySocialSiteView
{
	/**
	 * Returns an ajax chain.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function update($tooltips = '')
	{
		return $this->ajax->resolve($tooltips);
	}

	/**
	 * Suggest a list of friends that can be used for privacy
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function getfriends()
	{
		ES::requireLogin();
		ES::checkToken();

		$query = $this->input->get('q', '', 'default');
		$userId = $this->input->get('userid', 0, 'int');
		$exclude = $this->input->get('exclude', '', 'default');

		// Perhaps we need to get the current logged in user
		if (!$userId) {
			$userId = null;
		}

		if (!$query) {
			return $this->ajax->reject(JText::_('COM_EASYSOCIAL_PRIVACY_CUSTOM_SEARCH_EMPTY_QUERY'));
		}

		$my = ES::user($userId);
		$model = ES::model('Friends');
		$type = $this->config->get('users.displayName');

		// Check if we need to apply privacy or not.
		$options = array();

		if ($exclude) {
			$options['exclude'] = $exclude;
		}

		// Try to get the search result.
		$friends = $model->search($my->id , $query , $type, $options);

		$return = array();
		
		if ($friends) {
			foreach ($friends as $row) {
				$friend = new stdClass();
				$friend->id = $row->id;
				$friend->title = $row->getName();

				$return[] = $friend;
			}
		}

		return $this->ajax->resolve($return);
	}
}
