<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) 2010 - 2016 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

class SocialUserAppGroupsStreamCreate extends SocialAppsAbstract
{
	public function execute(SocialStreamItem &$item, $params)
	{
		// We want a full display for group creation.
		$item->display = SOCIAL_STREAM_DISPLAY_FULL;
		$item->translations = false;
		$item->label = 'APP_USER_GROUPS_STREAM_TOOLTIP';

		// Get the actor.
		$actor = $item->actor;
		$group = ES::group($item->cluster_id);

		$this->set('group', $group);
		$this->set('actor', $actor);

		$item->title = parent::display('streams/create.title');
		$item->content = parent::display('streams/content');

		// Append the opengraph tags
		$item->addOgDescription(JText::sprintf('APP_USER_GROUPS_STREAM_CREATED_GROUP', $actor->getName(), $group->getName()));

	}
}

