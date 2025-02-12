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

ES::import('site:/views/views');

class EasySocialViewApps extends EasySocialSiteView
{
	/**
	 * Displays the apps on the site.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function display($tpl = null)
	{
		// Require user to be logged in
		ES::requireLogin();
		ES::setMeta();

		if (!$this->config->get('apps.browser')) {
			ES::raiseError(404, JText::_('COM_EASYSOCIAL_APPS_BROWSER_DISABLED'));
		}

		// Check for user profile completeness
		ES::checkCompleteProfile();

		// Get current logged in user.
		$my = ES::user();

		// Get model.
		$model = ES::model('Apps');
		$sort = $this->input->get('sort', 'alphabetical');
		$order = $this->input->get('order', 'asc');
		$options = array('type' => SOCIAL_APPS_TYPE_APPS, 'installable' => true, 'sort' => $sort, 'order' => $order, 'group' => SOCIAL_APPS_GROUP_USER, 'uid' => $my->id, 'state' => SOCIAL_STATE_PUBLISHED);
		$modelFunc = 'getApps';

		switch ($sort) {
			case 'recent':
				$options['sort'] = 'a.created';
				$options['order'] = 'desc';
				break;

			case 'alphabetical':
				$options['sort'] = 'a.title';
				$options['order'] = 'asc';
				break;

			case 'trending':
				// need a separate logic to get trending based on apps_map
				$modelFunc = 'getTrendingApps';
				break;
		}

		// Get the current filter
		$filter = $this->input->get('filter', 'browse');
		$title = JText::_('COM_EASYSOCIAL_PAGE_TITLE_BROWSE_APPS');

		if ($filter == 'mine') {
			$options['uid'] = $my->id;
			$options['key'] = SOCIAL_TYPE_USER;
			$options['includedefault'] = true; // this flag used only in my apps page and use conjunction with 'installable'; #1657
			$title = JText::_('COM_EASYSOCIAL_PAGE_TITLE_YOUR_APPS');
		}

		// Set the page title
		$this->page->title($title);

		// Try to fetch the apps now.
		$apps = $model->$modelFunc($options);

		$this->set('filter', $filter);
		$this->set('sort', $sort);
		$this->set('apps', $apps);

		parent::display('site/apps/default/default');
	}

	/**
	 * Displays the application in a main canvas layout which is the full width of the component.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function canvas()
	{
		// Check for user profile completeness
		ES::checkCompleteProfile();

		// Get the unique id of the item that is being viewed
		$uid = $this->input->get('uid', null, 'int');
		$type = $this->input->get('type', SOCIAL_TYPE_USER, 'word');

		// Determines if the type is accessible
		if (!$this->allowed($uid, $type)) {
			return;
		}

		// Increment the hit counter
		if (in_array($type, array(SOCIAL_TYPE_EVENT, SOCIAL_TYPE_PAGE, SOCIAL_TYPE_GROUP))) {
			$clusters = ES::cluster($type, $uid);

			if ($clusters->id) {
				$clusters->hit();
			}
		}

		// Get the current app id.
		$id = $this->input->get('id', 0, 'int');

		// Get the current app.
		$app = ES::table('App');
		$state = $app->load($id);

		// Default redirection url
		$redirect = ESR::dashboard(array(), false);

		// Check if the user has access to this app
		if ($type == SOCIAL_TYPE_USER && !$app->accessible($uid, $type)) {
			$this->info->set(null, JText::_('COM_EASYSOCIAL_APPS_CANVAS_APP_IS_NOT_INSTALLED'), SOCIAL_MSG_ERROR);
			return $this->redirect($redirect);
		}

		// If id is not provided, we need to throw some errors here.
		if (!$id || !$state) {
			$this->setMessage(JText::_('COM_EASYSOCIAL_APPS_INVALID_ID_PROVIDED'), SOCIAL_MSG_ERROR);
			$this->info->set($this->getMessage());

			return $this->redirect($redirect);
		}

		// Try to load the app's css.
		$app->loadCss();

		// Check if the app provides any custom view
		$appView = $this->input->get('customView', 'canvas', 'default');

		// We need to set the breadcrumb for the cluster type
		if ($type == 'group') {
			$group = ES::group($uid);
			$this->page->breadcrumb($group->getName());
		}

		// Set the breadcrumbs with the app's title
		$this->page->breadcrumb($app->get('title'));

		// Load the library.
		$lib = ES::apps();
		$contents = $lib->renderView(SOCIAL_APPS_VIEW_TYPE_CANVAS, $appView, $app, array('uid' => $uid));

		$this->set('contents', $contents);

		echo parent::display('site/apps/canvas/default');
	}

	/**
	 * Determines if the current viewer can view the app's contents
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function allowed($uid, $type)
	{
		if ($type == SOCIAL_TYPE_GROUP) {
			$group = ES::group($uid);

			if (!$group->id) {
				throw ES::exception(JText::_('COM_EASYSOCIAL_GROUPS_GROUP_NOT_FOUND'), 404);
			}

			// Always allow site admin
			if ($group->isAdmin()) {
				return true;
			}

			if ($group->isOpen()) {
				return true;
			}

			if ($group->isClosed() && !$group->isMember()) {
				$label = 'COM_ES_VIEW_RESTRICTED_PAGE';
				$text = 'COM_ES_PAGES_PRIVACY_NOT_ALLOWED';
				// Display private info
				$this->set('node', $group);
				$this->set('label', $label);
				$this->set('text', $text);
				parent::display('site/groups/restricted/default');
				return false;
			}
		}

		// @TODO: Other user checks.

		return true;
	}

	/**
	 * Renders the qrcode for mobile
	 *
	 * @since	3.1.18
	 * @access	public
	 */
	public function mobileAppQrcode()
	{
		if (!$this->my->id) {
			return;
		}

		$data = $this->my->getMobileQrcodeData();

		require_once(JPATH_ADMINISTRATOR . '/components/com_easysocial/includes/qrcode/qrlib.php');
		echo QRcode::png($data);
		exit;
	}
}
