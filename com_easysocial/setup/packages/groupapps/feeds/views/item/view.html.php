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

class FeedsViewItem extends SocialAppsView
{
	/**
	 * Renders the list of feeds from a group
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function display($uid = null, $docType = null )
	{
		$rssId = $this->input->get('rssid', 0, 'int');

		$group = ES::group($uid);

		if (!$group->canAccessFeeds()) {
			return $this->redirect(ESR::dashboard([], false));
		}

		$this->setTitle('APP_FEEDS_APP_TITLE');

		$params = $this->app->getParams();
		$limit 	= $params->get('total', 5);

		$model = ES::model('RSS');
		$item = $model->getItem($rssId);

		if (!$item) {
			return $this->redirect(ESR::dashboard([], false));
		}

		$backLink = $group->getAppsPermalink($this->app->getAlias());
		$parser = $model->getParser($item->url);
		$item->items = $model->formatItems($parser, $limit);

		$this->set('totalDisplayed', $limit);
		$this->set('backLink', $backLink);
		$this->set('feed', $item);
		$this->set('user', $this->my);

		echo parent::display('themes:/site/feeds/item/default');
	}
}
