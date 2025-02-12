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

class ThemesHelperAvatar extends ThemesHelperAbstract
{
	public $sizes = array();
	public $sizesMap = null;

	public function __construct()
	{
		parent::__construct();

		$this->sizes = array(
			'xs' => 16,
			'sm' => 24,
			'md' => 32,
			'lg' => 64,
			'xl' => 120,
			'default' => 40);

		$this->sizesMap = array(
			'xs' => SOCIAL_AVATAR_SMALL,
			'sm' => SOCIAL_AVATAR_SMALL,
			'md' => SOCIAL_AVATAR_MEDIUM,
			'lg' => SOCIAL_AVATAR_LARGE,
			'xl' => SOCIAL_AVATAR_LARGE,
			'default' => SOCIAL_AVATAR_MEDIUM);
	}

	/**
	 * Generates the mini avatar block
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function mini($title, $permalink, $avatar, $size = 'default', $class = '', $attribs = '', $anchorLink = true, $forceStyle = false, $avatarText = false)
	{
		$size = !$size ? 'default' : $size;

		$width = $this->sizes[$size];
		$height = $this->sizes[$size];

		if ($attribs && is_array($attribs)) {
			$attribs = implode(' ', $attribs);
		}

		$sizeClass = $size == 'default' ? '' : ' o-avatar-v2--' . $size;

		$avatarStyle =  $this->config->get('layout.avatar.style') == 'rounded' ? 'o-avatar-v2--rounded' : '';
		if ($forceStyle !== false) {
			$avatarStyle = $forceStyle == 'rounded' ? 'o-avatar-v2--rounded' : $avatarStyle;
			$avatarStyle = $forceStyle == 'square' ? '' : $avatarStyle;
		}

		$avatarTextStyle = '';
		if (!$avatar && $avatarText) {
			$avatarTextStyle = 'o-avatar-v2--bg-' . rand(1,5);
		}

		$theme = ES::themes();
		$theme->set('title', $title);
		$theme->set('avatar', $avatar);
		$theme->set('avatarText', $avatarText);
		$theme->set('class', $class);
		$theme->set('sizeClass', $sizeClass);
		$theme->set('permalink', $permalink);
		$theme->set('width', $width);
		$theme->set('height', $height);
		$theme->set('attribs', $attribs);
		$theme->set('anchorLink', $anchorLink);
		$theme->set('avatarStyle', $avatarStyle);
		$theme->set('avatarTextStyle', $avatarTextStyle);

		$output = $theme->output('site/helpers/avatar/mini');
		return $output;
	}

	/**
	 * Generates the avatar block
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function user($user, $size = 'default', $popbox = true, $showOnlineState = true, $popboxPosition = '', $anchorLink = true, $customClass='')
	{
		static $cache = [];

		// Check for global config
		if ($showOnlineState) {
			$showOnlineState = $this->config->get('users.online.state', true);
		}

		if ($user instanceof SocialPage) {
			return $this->page($user, $size, $popbox, $showOnlineState, $popboxPosition, $anchorLink);
		} else if (!$user instanceof SocialUser) {
			throw ES::exception('Argument 1 passed to ThemesHelperAvatar::user() must be an instance of SocialUser.', 500);
		}

		$index = $user->id . $size . (int) $popbox . (int) $showOnlineState . $popboxPosition . (int) $anchorLink;

		if (!isset($cache[$index])) {
			$class = $size == 'default' ? '' : ' o-avatar-v2--' . $size;
			$width = $this->sizes[$size];
			$height = $this->sizes[$size];

			$showPopbox = false;

			if ($popbox && $user->id) {
				$showPopbox = true;
			}

			// do not render user pop up to guest if user enabled lockdown mode
			if ($popbox && $this->config->get('general.site.lockdown.enabled') && $this->my->guest) {
				$showPopbox = false;
			}

			$avatar = $user->getAvatar($this->sizesMap[$size]);

			$theme = ES::themes();
			$theme->set('anchorLink', $anchorLink);
			$theme->set('avatar', $avatar);
			$theme->set('popboxPosition', $popboxPosition);
			$theme->set('showOnlineState', $showOnlineState);
			$theme->set('showPopbox', $showPopbox);
			$theme->set('user', $user);
			$theme->set('size', $size);
			$theme->set('width', $width);
			$theme->set('height', $height);
			$theme->set('class', $class);
			$theme->set('customClass', $customClass);

			$output = $theme->output('site/helpers/avatar/user');

			$cache[$index] = $output;
		}

		return $cache[$index];
	}

	/**
	 * Generates the avatar block for cluster
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function cluster($cluster, $size = 'default', $tooltip = true, $showOnlineState = false, $popboxPosition = '', $anchorLink = true, $permalink = '', $popbox = true)
	{
		static $cache = array();

		$index = $cluster->id . $size . (int) $tooltip . (int) $showOnlineState . $popboxPosition . (int) $anchorLink . $permalink . ($popbox ? 1 : 0);

		if (!isset($cache[$index])) {
			$class = $size == 'default' ? '' : 'o-avatar-v2--' . $size;
			$width = $this->sizes[$size];
			$height = $this->sizes[$size];

			if (!$permalink || $permalink == '') {
				$permalink = $cluster->getPermalink();
			}

			$theme = ES::themes();
			$theme->set('popbox', $popbox);
			$theme->set('anchorLink', $anchorLink);
			$theme->set('cluster', $cluster);
			$theme->set('size', $size);
			$theme->set('width', $width);
			$theme->set('height', $height);
			$theme->set('class', $class);
			$theme->set('tooltip', $tooltip);
			$theme->set('permalink', $permalink);

			$cache[$index] = $theme->output('site/helpers/avatar/cluster');
		}

		return $cache[$index];
	}

	/**
	 * Generates the avatar for cluster categories
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function clusterCategory($category, $size = 'default', $anchorLink = true)
	{
		static $cache = array();

		$index = $category->id . $size . (int) $anchorLink;

		if (!isset($cache[$index])) {
			$class = $size == 'default' ? '' : 'o-avatar-v2--' . $size;
			$width = $this->sizes[$size];
			$height = $this->sizes[$size];

			$theme = ES::themes();
			$theme->set('anchorLink', $anchorLink);
			$theme->set('category', $category);
			$theme->set('size', $size);
			$theme->set('width', $width);
			$theme->set('height', $height);
			$theme->set('class', $class);

			$cache[$index] = $theme->output('site/helpers/avatar/cluster.category');
		}

		return $cache[$index];
	}

	/**
	 * Deprecated. Use @cluster instead
	 *
	 * @deprecated	3.3.0
	 */
	public function page(SocialPage $page, $size = 'default', $popbox = true, $showOnlineState = false, $popboxPosition = '', $anchorLink = true, $permalink = '', $tooltip = true)
	{
		return $this->cluster($page, $size, $tooltip, $showOnlineState, $popboxPosition, $anchorLink, $permalink, $popbox);
	}

	/**
	 * Generates the avatar block for conversation
	 *
	 * @since	1.5
	 * @access	public
	 */
	public function conversation($users, $size = 'default')
	{
		$class = $size == 'default' ? '' : 'o-avatar-v2--' . $size;
		$width = $this->sizes[$size];
		$height = $this->sizes[$size];

		$count = count($users);

		$theme = ES::themes();
		$theme->set('users', $users);
		$theme->set('size', $size);
		$theme->set('width', $width);
		$theme->set('height', $height);
		$theme->set('class', $class);
		$theme->set('count', $count);

		return $theme->output('site/helpers/avatar/conversation');
	}
}
