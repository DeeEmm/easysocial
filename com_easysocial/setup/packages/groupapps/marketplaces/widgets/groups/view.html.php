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

class MarketplacesWidgetsGroups extends SocialAppsWidgets
{
	public function sidebarBottom($groupId, $group)
	{
		$params = $this->getParams();

		if (!$this->config->get('marketplaces.enabled') || !$params->get('widget', true)) {
			return;
		}

		if (!$group->getAccess()->get('marketplaces.grouplisting', true)) {
			return;
		}


		echo $this->getCreatedListings($group, $params);
	}

	public function getCreatedListings(SocialGroup $group, $params)
	{
		$my = ES::user();

		$limit = $params->get('widget_total', 5);
		$model = ES::model('Marketplaces');

		$now = ES::date()->toSql();

		$listings = $model->getListings(array(
			'uid' => $group->id,
			'type' => SOCIAL_TYPE_GROUP,
			'state' => SOCIAL_STATE_PUBLISHED,
			'limit' => $limit,
		));

		if (!$listings) {
			return;
		}

		$allowCreate = $my->isSiteAdmin() || $group->canCreateListing();

		$viewAll = ESR::marketplaces(array('uid' => $group->id, 'type' => SOCIAL_TYPE_GROUP));

		if ($my->isViewer()) {
			$viewAll = ESR::marketplaces(array('filter' => 'mine'));
		}

		$theme = ES::themes();
		$theme->set('group', $group);
		$theme->set('listings', $listings);
		$theme->set('app', $this->app);
		$theme->set('viewAll', $viewAll);

		return $theme->output('themes:/apps/group/marketplaces/widgets/groups/marketplaces');
	}
}
