<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2018 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

class FollowersWidgetsPages extends SocialAppsWidgets
{
	/**
	 * Renders members widget on mobile
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function mobileAfterIntro($pageId, SocialPage $page)
	{
		// Enforce hard limit for mobile to 10
		$limit = 10;

		return $this->sidebarBottom($pageId, $page, $limit);
	}

	/**
	 * Renders the sidebar widget for page followers
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function sidebarBottom($pageId, SocialPage $page, $limit = null)
	{
		if (!$this->app->getParams()->get('show_followers', true)) {
			return;
		}

		$theme = ES::themes();

		$params = $this->app->getParams();

		if (!$limit) {
			$limit = (int) $params->get('limit', 10);
		}

		// Load up the page
		$page = ES::page($pageId);

		$options = array('state' => SOCIAL_STATE_PUBLISHED, 'limit' => $limit, 'ordering' => 'created', 'direction' => 'desc');

		// only show them followers
		$options['followers'] = true;

		$model = ES::model('Pages');
		$followers = $model->getMembers($page->id, $options);
		$link = ESR::pages(array('id' => $page->getAlias(),'appId' => $this->app->getAlias(),'layout' => 'item'));

		$theme->set('page', $page);
		$theme->set('followers', $followers);
		$theme->set('link', $link);

		echo $theme->output('themes:/apps/page/followers/widgets/followers');
	}
}
