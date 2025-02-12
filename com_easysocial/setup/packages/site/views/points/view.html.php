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

// Import parent view
ES::import( 'site:/views/views' );

class EasySocialViewPoints extends EasySocialSiteView
{
	private function checkFeature()
	{
		// Do not allow user to access photos if it's not enabled
		if (!$this->config->get('points.enabled')) {
			$this->setMessage('COM_EASYSOCIAL_POINTS_DISABLED', SOCIAL_MSG_ERROR);

			$this->info->set($this->getMessage());

			$this->redirect(ESR::dashboard(array(), false));
		}
	}

	/**
	 * Renders the points page
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function display($tpl = null)
	{
		$this->checkFeature();

		ES::setMeta();

		// Check for user profile completeness
		ES::checkCompleteProfile();

		// Set the page title & breadcrumb
		$this->page->title('COM_EASYSOCIAL_PAGE_TITLE_POINTS');
		$this->page->breadcrumb('COM_EASYSOCIAL_PAGE_TITLE_POINTS');

		// Get list of point.
		$model = ES::model('Points');

		// Get number of point to display per page.
		$limit = ES::getLimit('pointslimit');

		$options = array('limit' => $limit, 'published' => '1');

		$points = $model->getPointStats($options); 
		$pagination = $model->getPagination();

		$this->set('pagination', $pagination);
		$this->set('points', $points);

		parent::display('site/points/default/default');
	}

	/**
	 * Displays user's points history
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function history()
	{
		$this->checkFeature();

		ES::setMeta();

		// Check for user profile completeness
		ES::checkCompleteProfile();

		// Load the user
		$id = $this->input->get('userid', null, 'int');
		$user = ES::user($id);

		// If the user id is not provided, we need to display some error message.
		if (!$user->id) {
			ES::info()->set(JText::_('COM_EASYSOCIAL_POINTS_INVALID_USER_ID_PROVIDED'), SOCIAL_MSG_ERROR);
			return $this->redirect(ESR::dashboard(array(), false));
		}

		// If the user blocked, we need to display some error message.
		if ($user->isBlock()) {
			ES::info()->set( JText::sprintf('COM_EASYSOCIAL_POINTS_USER_NOT_EXIST', $user->getName()), SOCIAL_MSG_ERROR);
			return $this->redirect(ESR::dashboard(array(), false));
		}

		// Chech for privacy
		if (!$user->isViewer() && !$this->my->canViewPointsHistory($user)) {
			$facebook = ES::oauth('facebook');
			$return = base64_encode($user->getPermalink());

			$this->set('return', $return);
			$this->set('facebook', $facebook);
			$this->set('user', $user);

			return parent::display('site/profile/restricted');
		}

		// If the viewer is not the viewed user page, check if the viewer is block by the viewed user
		if (!JFactory::getUser()->guest && (JFactory::getUser()->id != $user->id)) {
			if (ES::user()->isBlockedBy($user->id)) {
				ES::raiseError(404, JText::_('COM_EASYSOCIAL_POINTS_USER_NOT_EXIST'));
			}
		}

		// Language should be loaded for the back end.
		ES::language()->loadAdmin();

		// Set the page title
		$this->page->title(JText::sprintf('COM_EASYSOCIAL_PAGE_TITLE_POINTS_USER_HISTORY', $user->getName()));

		// Set the page breadcrumb
		$this->page->breadcrumb(JText::_( 'COM_EASYSOCIAL_PAGE_TITLE_POINTS' ), ESR::points());
		$this->page->breadcrumb(JText::sprintf('COM_EASYSOCIAL_PAGE_TITLE_POINTS_USER_HISTORY', $user->getName()));

		// Let's test if the current viewer is allowed to view this profile.
		if (!$this->my->canView($user)) {
			$facebook = ES::oauth('facebook');
			$return = base64_encode($user->getPermalink());

			$this->set('return', $return);
			$this->set('facebook', $facebook);
			$this->set('user', $user);

			parent::display('site/profile/restricted');

			return;
		}

		$options = array('limit' => ES::getLimit('points.history.limit'));

		$model = ES::model('Points');

		// Get a list of histories for the user's points achievements.
		$histories = $model->getHistory($user->id, $options);
		$pagination = $model->getPagination();

		$this->set('pagination', $pagination);
		$this->set('histories', $histories);
		$this->set('user', $user);

		parent::display('site/points/history/default');
	}

	/**
	 * Default method to display the point item page.
	 *
	 * @since	1.0
	 * @access	public
	 */
	function item($tpl = null)
	{
		$this->checkFeature();

		ES::setMeta();

		// Check for user profile completeness
		ES::checkCompleteProfile();

		// Get the point id
		$id = $this->input->get('id', 0, 'int');

		// Load the point
		$point = ES::table('Points');
		$point->load($id);

		// If the point id not exist, show error message
		if (!$id || !$point->id) {
			ES::info()->set(null, JText::_('COM_EASYSOCIAL_POINTS_POINT_NOT_EXIST'), SOCIAL_MSG_ERROR);
			return $this->redirect(ESR::dashboard(array(), false));
		}

		$point->loadLanguage();

		// Load language file.
		JFactory::getLanguage()->load('com_easysocial', JPATH_ROOT . '/administrator');

		// Set the page title
		$this->page->title($point->get('title'));

		// Set the page breadcrumb
		$this->page->breadcrumb(JText::_('COM_EASYSOCIAL_PAGE_TITLE_POINTS'), ESR::points());
		$this->page->breadcrumb($point->get('title'));

		$options = ['start' => 0, 'limit' => ES::getLimit('points')];

		// Get list of point achievers.
		$achievers = $point->getAchievers($options);
		$total = $point->getTotalAchievers();

		$this->set('achievers', $achievers);
		$this->set('total', $total);
		$this->set('point', $point);

		parent::display('site/points/item/default');
	}
}
