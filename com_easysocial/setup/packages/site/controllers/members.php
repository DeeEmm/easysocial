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

class EasySocialControllerMembers extends EasySocialController
{
	/**
	 * Suggest a list of friend names for a user.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function suggest()
	{
		ES::requireLogin();
		ES::checkToken();

		// Properties
		$clusterId  = $this->input->get('uid', 0, 'int');
		$search  = $this->input->get('search', '', 'default');
		$exclude = $this->input->get('exclude', '', 'default');

		// Determine what type of string we should search for.
		$type = $this->config->get('users.displayName');

		$options = array();

		if ($exclude) {
			$options['exclude'] = $exclude;
		}

		// Try to get the search result.
		$model = ES::model('Clusters');
		$result = $model->searchNodes($clusterId, $search, $type, $options);

		return $this->view->call(__FUNCTION__, $result);
	}
}
