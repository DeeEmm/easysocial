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

class EasySocialViewBadges extends EasySocialSiteView
{
	/**
	 * Default method to display the registration page.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function display($tpl = null)
	{
		// Check for user profile completeness
		ES::checkCompleteProfile();

		ES::setMeta();

		// Set the page title
		ES::document()->title(JText::_('COM_EASYSOCIAL_PAGE_TITLE_BADGES'));

		// Set the page breadcrumb
		ES::document()->breadcrumb(JText::_('COM_EASYSOCIAL_PAGE_TITLE_BADGES'));

		if (!$this->config->get('badges.enabled')) {
			return $this->redirect(ESR::dashboard(array(), false));
		}

		// Get list of badges.
		$model = ES::model('Badges');

		// Get number of badges to display per page.
		$limit = ES::getLimit('badgeslimit');

		$options = array('state' => SOCIAL_STATE_PUBLISHED, 'limit' => $limit, 'achieveType' => 'all');
		$badges = $model->getItems($options);
		$pagination	= $model->getPagination();

		$this->set( 'pagination', $pagination );
		$this->set( 'badges' 	, $badges );

		parent::display('site/badges/default');
	}

	/**
	 * Default method to display the registration page.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function item($tpl = null)
	{
		// Check for user profile completeness
		ES::checkCompleteProfile();
		ES::setMeta();

		if (!$this->config->get('badges.enabled')) {
			return $this->redirect(ESR::dashboard(array(), false));
		}

		$id = $this->input->get('id', 0, 'int');

		$badge = ES::table('Badge');
		$badge->load($id);

		if (!$id || !$badge->id) {
			$this->setMessage('COM_EASYSOCIAL_BADGES_INVALID_BADGE_ID_PROVIDED', SOCIAL_MSG_ERROR);
			$this->info->set($this->getMessage());
			return $this->redirect(ESR::badges());
		}

		// Load the badge language
		$badge->loadLanguage();

		// Set the page title
		$this->page->title($badge->_('title'));

		// Set the page breadcrumb
		$this->page->breadcrumb('COM_EASYSOCIAL_PAGE_TITLE_BADGES', ESR::badges());
		$this->page->breadcrumb($badge->_('title'));

		// Get the badges model
		$options = [
						'start' => 0,
						'limit' => ES::getLimit('achieverslimit')
					];

		$achievers = $badge->getAchievers($options);
		$totalAchievers = $badge->getTotalAchievers();

		$this->set('totalAchievers', $totalAchievers);
		$this->set('achievers', $achievers);
		$this->set('badge', $badge);

		parent::display('site/badges/item/default');
	}

	/**
	 * Displays a list of badges the user has achieved
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function achievements()
	{
		// Check for user profile completeness
		ES::checkCompleteProfile();
		ES::setMeta();

		if (!$this->config->get('badges.enabled')) {
			return $this->redirect(ESR::dashboard(array(), false));
		}

		// Get the current user id that should be displayed
		$userId = $this->input->get('userid', 0, 'int');
		$userId = $userId == 0 ? null : $userId;
		$user = ES::user($userId);

		// If user is not found, we need to redirect back to the dashboard page
		if (!$user->id) {
			return $this->redirect(ESR::dashboard(array(), false));
		}

		// If the user is blocked, they should not be accessible #5069
		if ($user->isBlock() && !$this->my->isSiteAdmin()) {
			ES::raiseError(404, JText::_('COM_EASYSOCIAL_PROFILE_INVALID_USER'));
		}


		$title = 'COM_EASYSOCIAL_PAGE_TITLE_ACHIEVEMENTS';

		if (!$user->isViewer()) {
			$title = JText::sprintf('COM_EASYSOCIAL_PAGE_TITLE_ACHIEVEMENTS_USER', $user->getName());

			// Let's test if the current viewer is allowed to view this user's achievements.
			$privacy = $this->my->getPrivacy();
			$allowed = $privacy->validate('achievements.view', $user->id, SOCIAL_TYPE_USER);

			if (!$allowed) {
				$this->set('user', $user);
				parent::display('site/profile/restricted');

				return;
			}
		}

		$permalink = ESR::badges(array('userid' => $user->id, 'layout' => 'achievements'));

		// Set the page title
		$this->page->title($title);
		$this->page->breadcrumb('COM_EASYSOCIAL_PAGE_TITLE_ACHIEVEMENTS', $permalink);

		$model = ES::model('Badges');
		$badges = $model->getBadges($user->id);

		$totalBadges = count($badges);

		$this->set('totalBadges', $totalBadges);
		$this->set('badges', $badges);
		$this->set('user', $user);

		parent::display('site/badges/achievements/default');
	}
}
