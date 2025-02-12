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

class ThemesHelperGroup
{
	/**
	 * Renders the group type label
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function type(SocialGroup $group, $tooltipPlacement = 'bottom', $groupView = false, $icon = true)
	{
		$theme = ES::themes();

		$theme->set('icon', $icon);
		$theme->set('placement', $tooltipPlacement);
		$theme->set('group', $group);
		$theme->set('groupView', $groupView);

		$output = $theme->output('site/helpers/group/type');

		return $output;
	}

	/**
	 * Generates a report link for a group
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function report(SocialGroup $group, $wrapper = 'list')
	{
		static $output = array();

		if ($group->isDraft()) {
			return;
		}

		$index = $group->id . $wrapper;

		if (!isset($output[$index])) {

			// Ensure that the user is allowed to report objects on the site
			$access = ES::access();
			$config = ES::config();

			if ($group->isOwner() || !$config->get('reports.enabled') || !$access->allowed('reports.submit')) {
				return;
			}

			$reports = ES::reports();

			// Reporting options
			$options = [
				'dialogTitle' => 'COM_EASYSOCIAL_GROUPS_REPORT_GROUP',
				'dialogContent' => 'COM_EASYSOCIAL_GROUPS_REPORT_GROUP_DESC',
				'title' => $group->getName(),
				'permalink' => $group->getPermalink(true, true),
				'type' => 'link',
				'showIcon' => false,
				'actorid' => $group->creator_uid
			];

			$output[$index] = $reports->form(SOCIAL_TYPE_GROUPS, $group->id, $options);
		}

		return $output[$index];
	}

	/**
	 * Renders the private messaging button for users
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function bookmark(SocialGroup $group, $iconOnly = false)
	{
		if ($group->isDraft()) {
			return;
		}

		$options = array();
		$options['url'] = $group->getPermalink(false, true);
		$options['display'] = 'dialog';

		$title = strip_tags($group->getTitle());

		if (ESJString::strlen($title) >= 50) {
			$title = ESJString::substr($title, 0, 50) . JText::_('COM_EASYSOCIAL_ELLIPSIS');
		}

		$options['title'] = $title;

		$sharing = ES::sharing($options);

		// Determines if the text should be displayed instead
		$showText = false;

		if ($iconOnly) {
			$showText = false;
		}

		$output = $sharing->button();

		return $output;
	}

	/**
	 * Renders the group's admin button
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function adminActions(SocialGroup $group, $returnUrl = '')
	{
		// Check for privileges
		$my = ES::user();

		if (!$my->isSiteAdmin() && !$group->isOwner() && !$group->isAdmin()) {
			return;
		}

		if (!$returnUrl) {
			$returnUrl = base64_encode(ES::getURI(true));
		}

		$theme = ES::themes();
		$theme->set('group', $group);
		$theme->set('returnUrl', $returnUrl);

		$output = $theme->output('site/helpers/group/admin.actions');

		return $output;
	}

	/**
	 * Renders the group's action button
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function action(SocialGroup $group, $forceReload = false)
	{
		// If this is the group owner, we don't want to show any actions here since they can't leave the group
		if ($group->isOwner()) {
			return;
		}

		$my = ES::user();

		if (!$my->id) {
			$forceReload = false;
		}

		$theme = ES::themes();
		$theme->set('group', $group);
		$theme->set('forceReload', $forceReload);

		$output = $theme->output('site/helpers/group/action');

		return $output;
	}

	/**
	 * Renders the group stream object
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function stream(SocialGroup $group)
	{
		$theme = ES::themes();
		$theme->set('group', $group);

		$content = $theme->output('site/groups/streams/object');

		return $content;
	}

	/**
	 * Renders the group's email digest button
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function subscribe(SocialGroup $group)
	{
		if (! $group->isMember()) {
			return;
		}

		// If this is the group owner, onwer should always get notification.
		if ($group->isOwner()) {
			return;
		}


		$intervals = array('default' => SOCIAL_DIGEST_DEFAULT,
						'daily' => SOCIAL_DIGEST_DAILY,
						'weekly' => SOCIAL_DIGEST_WEEKLY,
						'monthly' => SOCIAL_DIGEST_MONTHLY
					);

		$my = ES::user();
		$selected = $group->hasSubsribeDigest($my->id);

		$theme = ES::themes();
		$theme->set('group', $group);
		$theme->set('intervals', $intervals);
		$theme->set('selected', $selected);

		$output = $theme->output('site/helpers/group/subscribe');

		return $output;
	}
}
