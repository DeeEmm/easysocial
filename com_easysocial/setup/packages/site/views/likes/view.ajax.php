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

class EasySocialViewLikes extends EasySocialSiteView
{
	/**
	 * Post processing after reaction is made on an item
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function react(SocialLikes $lib, $action)
	{
		// Refresh the count
		$count = $lib->getCount();

		// Determines if we should display the output of reactions
		$hideInfo = $count > 0 ? false : true;

		$isMobile = ES::responsive()->isMobile();

		// Get the info
		$label = $lib->toString(null, false, $isMobile);

		return $this->ajax->resolve($label, $hideInfo, $action, $count);
	}

	/**
	 * Displays the popbox of reaction stats
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function popbox()
	{
		// Load front end's language file
		ES::language()->loadSite();

		$uid = $this->input->get('uid', 0, 'int');
		$verb = $this->input->get('verb', '', 'string');
		$group = $this->input->get('group', '', 'word');
		$element = $this->input->get('type', '', 'string');
		$clusterId = $this->input->get('clusterid', 0, 'int');

		// There is instances where we only want to retrieve a single reaction stats
		$reactionFilter = $this->input->get('filter', '', 'default');

		$options = array();
		if ($clusterId) {
			$options['clusterId'] = $clusterId;
		}

		$likes = ES::likes($uid, $element, $verb, $group, '', $options);
		$key = $likes->getKey();

		$model = ES::model('Likes');
		$reactions = $model->getReactionsResult($uid, $key, null, true, $options);

		// Format the reactions with users
		foreach ($reactions as $reaction) {
			$reaction->users = $model->getReactionsUsers($uid, $key, $reaction->getKey(), $options);
		}

		$theme = ES::themes();
		$theme->set('reactions', $reactions);
		$theme->set('reactionFilter', $reactionFilter);

		$contents = $theme->output('site/likes/popbox/stats');

		return $this->ajax->resolve($contents);
	}

	/**
	 * Deprecated. Use @react instead
	 *
	 * @deprecated	2.1
	 */
	public function toggle(SocialLikes $lib, $label, $action)
	{
		return $this->react($lib, $action);
	}
}
