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

class MarketplacesWidgetsPages extends SocialAppsWidgets
{
	public function sidebarBottom($pageId, SocialPage $page)
	{
		$params = $this->getParams();

		if (!$this->config->get('marketplaces.enabled') || !$params->get('widget', true)) {
			return;
		}

		if (!$page->getAccess()->get('marketplaces.pagelisting', true)) {
			return;
		}


		echo $this->getCreatedListings($page, $params);
	}

	public function getCreatedListings(SocialPage $page, $params)
	{
		$my = ES::user();

		$limit = $params->get('widget_total', 5);
		$model = ES::model('Marketplaces');

		$now = ES::date()->toSql();

		$listings = $model->getListings(array(
			'uid' => $page->id,
			'type' => SOCIAL_TYPE_PAGE,
			'state' => SOCIAL_STATE_PUBLISHED,
			'limit' => $limit,
		));

		if (!$listings) {
			return;
		}

		$viewAll = ESR::marketplaces(array('uid' => $page->id, 'type' => SOCIAL_TYPE_PAGE));

		if ($my->isViewer()) {
			$viewAll = ESR::marketplaces(array('filter' => 'mine'));
		}

		$theme = ES::themes();
		$theme->set('page', $page);
		$theme->set('listings', $listings);
		$theme->set('app', $this->app);
		$theme->set('viewAll', $viewAll);

		return $theme->output('themes:/apps/page/marketplaces/widgets/pages/marketplaces');
	}
}
