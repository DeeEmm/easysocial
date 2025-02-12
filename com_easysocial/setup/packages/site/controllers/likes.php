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

class EasySocialControllerLikes extends EasySocialController
{
	/**
	 * Allows client side to react to an item
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function react()
	{
		ES::requireLogin();
		ES::checkToken();

		$id = $this->input->get('id', 0, 'int');
		$type = $this->input->get('type', '', 'string');
		$group = $this->input->get('group', SOCIAL_APPS_GROUP_USER, 'string');
		$verb = $this->input->get('verb', '', 'string');
		$reaction = $this->input->get('reaction', 'like', 'word');
		$streamId = $this->input->get('streamid', 0, 'int');
		$clusterId = $this->input->get('clusterid', 0, 'int');

		$options = array();

		// We need to store the cluster to be used later
		if ($clusterId) {
			$options['clusterId'] = $clusterId;
		}

		$options['uri'] = $this->input->get('uri', '', 'default');
		$options['reactAs'] = $this->input->get('reactas', 'user', 'string');

		// If id is invalid, throw an error.
		if (!$id || !$type) {
			return $this->view->exception('COM_EASYSOCIAL_ERROR_UNABLE_TO_LOCATE_ID');
		}

		$likes = ES::likes($id, $type, $verb, $group, $streamId, $options);

		// Determines if the user can really react to this object
		if (!$likes->canReact()) {
			return $this->view->exception('You are not allowed to react');
		}

		$table = $likes->getUserReaction();

		$action = $table->reaction == $reaction ? 'withdraw' : 'react';

		$state = $likes->$action($reaction);

		// If there's an error, log this down here.
		if (!$state) {
			$this->view->setMessage($likes->getError(), ES_ERROR);
		}

		return $this->view->call(__FUNCTION__, $likes, $action);
	}

	/**
	 * Allows caller to like / unlike an object
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function toggle()
	{
		ES::requireLogin();
		ES::checkToken();

		$id = $this->input->get('id', 0, 'int');
		$type = $this->input->get('type', '', 'string');
		$group = $this->input->get('group', SOCIAL_APPS_GROUP_USER, 'string');
		$verb = $this->input->get('verb', '', 'string');
		$streamId = $this->input->get('streamid', 0, 'int');
		$clusterId = $this->input->get('clusterid', 0, 'int');

		$options = array();

		// We need to store the cluster to be used later
		if ($clusterId) {
			$options['clusterId'] = $clusterId;
		}

		// If id is invalid, throw an error.
		if (!$id || !$type) {
			return $this->view->exception('COM_EASYSOCIAL_ERROR_UNABLE_TO_LOCATE_ID');
		}

		$likes = ES::likes($id, $type, $verb, $group, $streamId, $options);
		$hasLiked = $likes->hasLiked();

		$action = $hasLiked ? 'unlike' : 'like';
		$state = $likes->$action();

		// Determines the next label to show to the user
		$label = $action == 'like' ? 'COM_EASYSOCIAL_LIKES_UNLIKE' : 'COM_EASYSOCIAL_LIKES_LIKE';
		$label = JText::_($label);

		// If there's an error, log this down here.
		if (!$state) {
			$this->view->setMessage($likes->getError(), ES_ERROR);
		}

		return $this->view->call(__FUNCTION__, $likes, $label, $action);
	}
}
