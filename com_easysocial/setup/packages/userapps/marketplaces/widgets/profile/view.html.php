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

class MarketplacesWidgetsProfile extends SocialAppsWidgets
{
	public function sidebarBottom($user)
	{
		$params = $this->getParams();

		if (!$this->config->get('marketplaces.enabled') || !$params->get('widget_profile', true)) {
			return;
		}

		echo $this->getCreatedListings($user, $params);
	}

	public function getCreatedListings(SocialUser $user, $params)
	{
		$limit = $params->get('widget_profile_total', 5);
		$model = ES::model('Marketplaces');

		$now = ES::date()->toSql();

		$listings = $model->getListings(array(
			'uid' => $user->id,
			'type' => SOCIAL_TYPE_USER,
			'state' => SOCIAL_STATE_PUBLISHED,
			'limit' => $limit,
		));

		if (!$listings) {
			return;
		}

		$viewAll = ESR::marketplaces(array('type' => SOCIAL_TYPE_USER, 'uid' => $user->getAlias()));

		$theme = ES::themes();
		$theme->set('user', $user);
		$theme->set('listings', $listings);
		$theme->set('app', $this->app);
		$theme->set('viewAll', $viewAll);

		return $theme->output('themes:/apps/user/marketplaces/widgets/profile/marketplaces');
	}
}
