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

class FeedsViewCanvas extends SocialAppsView
{
	public function display($uid = null, $docType = null)
	{
		$user = ES::user($uid);
		$params = $this->getUserParams($user->id);

		// Get the app params
		$appParams = $this->app->getParams();

		$limit = $params->get('total', $appParams->get('total', 5));

		$id = $this->input->get('cid', 0, 'int');

		$feed = $this->getTable('Feed');
		$feed->load($id);

		$parser = $feed->getParser();
		$feed->total = $parser->count();

		$model = ES::model('Rss');
		$feed->items = $model->formatItems($parser, $limit);

		$this->setTitle($feed->title);

		$backLink = $this->app->getUserPermalink($user->getAlias());

		$this->set('totalDisplayed', $limit);
		$this->set('backLink', $backLink);
		$this->set('feed', $feed);
		$this->set('user', $user);
		$this->set('params', $params);

		echo parent::display('themes:/site/feeds/item/default');
	}
}
