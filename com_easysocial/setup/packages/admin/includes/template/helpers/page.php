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

class ThemesHelperPage extends ThemesHelperAbstract
{
	/**
	 * Renders the private messaging button for users
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function bookmark(SocialPage $page, $iconOnly = false)
	{
		if ($page->isDraft()) {
			return;
		}

		$options = array();
		$options['url'] = $page->getPermalink(false, true);
		$options['display'] = 'dialog';

		$title = strip_tags($page->getTitle());

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
	 * Renders the page's action button
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function action(SocialPage $page, $forceReload = false)
	{
		// If this is the page owner, we don't want to show any actions here since they can't unlike the page
		if ($page->isOwner()) {
			return;
		}

		if (!$this->my->id) {
			$forceReload = false;
		}

		$returnUrl = base64_encode(ES::getURI(true));

		$theme = ES::themes();
		$theme->set('page', $page);
		$theme->set('returnUrl', $returnUrl);
		$theme->set('forceReload', $forceReload);

		$output = $theme->output('site/helpers/page/action');

		return $output;
	}

	/**
	 * Render the page type label
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function type(SocialPage $page, $tooltipPlacement = 'bottom', $pageView = false, $showIcon = true)
	{
		$theme = ES::themes();

		$theme->set('showIcon', $showIcon);
		$theme->set('placement', $tooltipPlacement);
		$theme->set('page', $page);

		$output = $theme->output('site/helpers/page/type');

		return $output;
	}

	/**
	 * Generate a report link for a page
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function report(SocialPage $page, $wrapper = 'list')
	{
		static $output = array();

		if ($page->isDraft()) {
			return;
		}

		$index = $page->id . $wrapper;

		if (!isset($output[$index])) {

			// Ensure that user is allow to report on the site
			if ($page->isOwner() || !$this->config->get('reports.enabled') || !$this->access->allowed('reports.submit')) {
				return;
			}

			$reports = ES::reports();

			$options = [
				'dialogTitle' => 'COM_EASYSOCIAL_PAGES_REPORT_PAGE',
				'dialogContent' => 'COM_EASYSOCIAL_PAGES_REPORT_PAGE_DESC',
				'title' => $page->getName(),
				'permalink' => $page->getPermalink(true,true),
				'type' => 'link',
				'showIcon' => false,
				'actorid' => $page->creator_uid
			];

			$output[$index] = $reports->form(SOCIAL_TYPE_PAGES, $page->id, $options);
		}

		return $output[$index];
	}

	/**
	 * Renders the page admin's action
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function adminActions(SocialPage $page, $returnUrl = '')
	{
		// Check the privileges
		if (!$this->my->isSiteAdmin() && !$page->isOwner() && !$page->isAdmin()) {
			return;
		}

		if (!$returnUrl) {
			$returnUrl = base64_encode(ES::getURI(true));
		}

		$theme = ES::themes();
		$theme->set('page', $page);
		$theme->set('returnUrl', $returnUrl);

		$output = $theme->output('site/helpers/page/admin.actions');

		return $output;
	}

	/**
	 * Renders the page stream object
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function stream(SocialPage $page)
	{
		$theme = ES::themes();
		$theme->set('page', $page);

		$content = $theme->output('site/pages/streams/object');

		return $content;
	}
}
