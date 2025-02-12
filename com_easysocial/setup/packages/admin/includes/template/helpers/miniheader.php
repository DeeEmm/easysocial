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

class ThemesHelperMiniHeader
{
	/**
	 * Renders the audio genre header
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function audioGenre($genre)
	{
		$permalink = $genre->getPermalink();
		$title = $genre->_('title');
		$description = $genre->_('description');

		$theme = ES::themes();
		$theme->set('permalink', $permalink);
		$theme->set('title', $title);
		$theme->set('avatar', false);
		$theme->set('description', $description);
		$theme->set('moreText', false);

		$output = $theme->output('site/helpers/miniheader/objects');

		return $output;
	}

	/**
	 * Renders an object header
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function eventCategory(SocialTableEventCategory $category)
	{
		$theme = ES::themes();

		$permalink = $category->getPermalink();
		$title = $category->getTitle();
		$avatar = $category->getAvatar();
		$description = $category->getDescription();
		$moreText = 'COM_EASYSOCIAL_EVENTS_VIEW_CATEGORY';

		// Get a list of child categories here
		$model = ES::model('ClusterCategory');
		$childs = $model->getImmediateChildCategories($category->id, SOCIAL_TYPE_EVENT);

		$theme->set('childs', $childs);
		$theme->set('permalink', $permalink);
		$theme->set('title', $title);
		$theme->set('avatar', $avatar);
		$theme->set('description', $description);
		$theme->set('moreText', $moreText);

		$output = $theme->output('site/helpers/miniheader/clusters');

		return $output;
	}

	/**
	 * Renders an object header
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function groupCategory(SocialTableGroupCategory $category)
	{
		$theme = ES::themes();

		$permalink = $category->getPermalink();
		$title = $category->getTitle();
		$avatar = $category->getAvatar();
		$description = $category->getDescription();
		$moreText = 'COM_EASYSOCIAL_GROUPS_MORE_INFO_CATEGORY';

		// Get a list of child categories here
		$model = ES::model('ClusterCategory');
		$childs = $model->getImmediateChildCategories($category->id, SOCIAL_TYPE_GROUP);

		$theme->set('childs', $childs);
		$theme->set('permalink', $permalink);
		$theme->set('title', $title);
		$theme->set('avatar', $avatar);
		$theme->set('description', $description);
		$theme->set('moreText', $moreText);

		$output = $theme->output('site/helpers/miniheader/clusters');

		return $output;
	}

	/**
	 * Renders the marketplace category header
	 *
	 * @since   3.3
	 * @access  public
	 */
	public function marketplaceCategory($category)
	{
		$permalink = $category->getFilterPermalink();
		$title = $category->getTitle();
		$avatar = $category->getAvatar();
		$description = $category->getDescription();
		$moreText = false;

		// Get a list of child categories here
		$model = ES::model('MarketplaceCategories');
		$childs = $model->getImmediateChildCategories($category->id);

		$theme = ES::themes();
		$theme->set('childs', $childs);
		$theme->set('permalink', $permalink);
		$theme->set('title', $title);
		$theme->set('avatar', $avatar);
		$theme->set('description', $description);
		$theme->set('moreText', $moreText);

		// we share the same theme file with the clusters
		$output = $theme->output('site/helpers/miniheader/clusters');

		return $output;
	}

	/**
	 * Renders an object header
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function profileType(SocialTableProfile $profile)
	{
		$theme = ES::themes();

		$permalink = $profile->getPermalink();
		$title = $profile->getTitle();
		$avatar = $profile->getAvatar();
		$description = $profile->getDescription();
		$moreText = 'COM_EASYSOCIAL_USERS_VIEW_PROFILE_TYPE';

		$theme->set('permalink', $permalink);
		$theme->set('title', $title);
		$theme->set('avatar', $avatar);
		$theme->set('description', $description);
		$theme->set('moreText', $moreText);

		$output = $theme->output('site/helpers/miniheader/objects');

		return $output;
	}

	/**
	 * Renders an object header for page category
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function pageCategory(SocialTablePageCategory $category)
	{
		$theme = ES::themes();

		$permalink = $category->getPermalink();
		$title = $category->getTitle();
		$avatar = $category->getAvatar();
		$description = $category->getDescription();
		$moreText = 'COM_EASYSOCIAL_PAGES_MORE_INFO_CATEGORY';

		// Get a list of child categories here
		$model = ES::model('ClusterCategory');
		$childs = $model->getImmediateChildCategories($category->id, SOCIAL_TYPE_PAGE);

		$theme->set('childs', $childs);
		$theme->set('permalink', $permalink);
		$theme->set('title', $title);
		$theme->set('avatar', $avatar);
		$theme->set('description', $description);
		$theme->set('moreText', $moreText);

		$output = $theme->output('site/helpers/miniheader/clusters');

		return $output;
	}

	/**
	 * Renders the video category header
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function videoCategory($category)
	{
		$permalink = $category->getPermalink();
		$title = $category->_('title');
		$description = $category->_('description');

		$theme = ES::themes();
		$theme->set('permalink', $permalink);
		$theme->set('title', $title);
		$theme->set('avatar', false);
		$theme->set('description', $description);
		$theme->set('moreText', false);

		$output = $theme->output('site/helpers/miniheader/objects');

		return $output;
	}

	/**
	 * Renders a mini header for users
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function user(SocialUser $user)
	{
		static $data = [];

		if (isset($data[$user->id])) {
			return $data[$user->id];
		}

		$theme = ES::themes();
		$theme->set('user', $user);

		$items[$user->id] = $theme->output('site/helpers/miniheader/user');

		return $items[$user->id];
	}
}
