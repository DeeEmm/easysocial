<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) 2010 - 2017 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

class BroadcastControllerBroadcast extends SocialAppsController
{
	/**
	 * Allows caller to get selection items
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function getSelectionItems()
	{
		ES::checkToken();
		ES::requireLogin();

		// Get the event object
		$type = $this->input->get('type', 'profile', 'string');

		$items = array();
		
		if ($type == 'profile') {
			// Get a list of profiles on the site
			$model = ES::model('Profiles');
			$options = array('state' => SOCIAL_STATE_PUBLISHED);
			$items = $model->getProfiles($options);
		}

		if ($type == 'group') {
			// Get a list of groups on the site
			$model = ES::model('Groups');
			$items = $model->getGroups(array('ordering' => 'name', 'state' => SOCIAL_CLUSTER_PUBLISHED, 'types' => 'all'));
		}

		$html = '';

		if (!empty($items)) {
			foreach($items as $item) {
				$html .= '<option value="' . $item->id . '">' . $item->getTitle() . '</option>';
			}
		}

		return $this->ajax->resolve($html);
	}
}
