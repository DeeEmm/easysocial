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

ES::import('admin:/includes/views');

class EasySocialSiteView extends EasySocialView
{
	public $rssLink = '';

	public function __construct($config = array())
	{
		// We want to allow child classes to easily access theme configurations on the view
		$this->themeConfig = ES::themes()->getConfig();

		parent::__construct($config);

		// Check if there is a method isFeatureEnabled exists. If it does, we should do a check all the time.
		if (method_exists($this, 'isFeatureEnabled')) {
			$this->isFeatureEnabled();
		}

		// check if user is required to reset password or not.
		if ($this->my->require_reset) {
			$controller = $this->input->get('controller', '', 'cmd');
			$view = $this->input->get('view', '', 'cmd');

			if ($view != 'account' && $view != 'registration' && $view != 'oauth' && $controller != 'account') {
				$url = ESR::account(array('layout' => 'requirePasswordReset'), false);

				$this->redirect($url);
				return;
			}
		}

		// When the user doesn't have community access, ensure that they can only view selected views.
		if (!$this->my->hasCommunityAccess()) {

			// Get the current view
			$view = $this->getName();
			$layout = $this->input->get('layout', '', 'cmd');

			// If this is an ajax call, we need to allow some ajax calls to go through
			$allowedAjaxNamespaces = array(
				'site/views/profile/showFormError',
				'site/controllers/photos/checkFileSize',
				'site/controllers/audios/checkFileSize',
				'site/controllers/videos/checkFileSize',
				'site/controllers/uploader/checkFileSize',
				'site/controllers/explorer/checkFileSize'
			);

			if ($this->doc->getType() == 'ajax') {
				$namespace = $this->input->get('namespace', '', 'default');

				// If this is an ajax call, and the namespace is valid, skip checking below
				if (in_array($namespace, $allowedAjaxNamespaces)) {
					return;
				}
			}

			// Define allowed views and layout
			$allowedViews = array('profile', 'account', 'download');
			$allowedLayouts = array('edit', 'requirePasswordReset', 'download', 'confirmDownload');

			// views that we should redirect the user to profile edit page.
			$redirectView = array('dashboard', 'profile', 'login', 'registration');

			// User should be allowed to logout from the site
			$isLogout = (($this->input->get('controller', '', 'cmd') == 'account') && ($this->input->get('task', '', 'cmd') == 'logout')) || (($this->input->get('view', '', 'cmd') == 'login') && ($this->input->get('layout', '', 'cmd') == 'logout'));

			// user should be allowed to save their profile details on the site.
			$isProfileSaving = ($this->input->get('controller', '', 'cmd') == 'profile') && ( $this->input->get('task', '', 'cmd') == 'save');

			// User should be allowed to reset password
			$isResetPassword = ($this->input->get('controller', '', 'cmd') == 'account') && ($this->input->get('task', '', 'cmd') == 'completeRequireResetPassword');

			$isDownloading = (($this->input->get('namespace', '', 'default') == 'site/views/profile/confirmDownload') ||
								($this->input->get('controller', '', 'cmd') == 'profile' && ($this->input->get('task', '', 'cmd') == 'download')) ||
								($this->input->get('view', '', 'cmd') == 'download')
							);

			$isDeletingAccount = (($this->input->get('namespace', '', 'default') == 'site/views/profile/confirmDelete') ||
								($this->input->get('controller', '', 'cmd') == 'profile' && ($this->input->get('task', '', 'cmd') == 'delete'))
							);

			$message = JText::_('COM_EASYSOCIAL_NOT_ALLOWED_TO_VIEW_SECTION');
			$redirectEditProfilePage = ESR::profile(array('layout' => 'edit'), false);

			if (in_array($view, $redirectView) && (!$layout || $layout == 'completed') && !$isLogout && !$isProfileSaving && !$isDownloading && !$isDeletingAccount) {
				// we need to redirect the user to profile edit page.
				$this->info->set(false, $message, SOCIAL_MSG_INFO);
				$this->redirect($redirectEditProfilePage);
				return;
			}

			// Ensure that the restricted user is not able to view other views
			if (!in_array($view, $allowedViews) && !$isLogout && !$isProfileSaving && !$isDownloading && !$isDeletingAccount) {
				$this->info->set(false, $message, SOCIAL_MSG_INFO);
				$this->redirect($redirectEditProfilePage);
				return;
			}

			// Ensure that the user is only viewing the allowed layouts
			if (!in_array($layout, $allowedLayouts) && !$isLogout && !$isProfileSaving && !$isDownloading && !$isDeletingAccount) {
				$this->info->set(false, $message, SOCIAL_MSG_INFO);
				$this->redirect($redirectEditProfilePage);
				return;
			}
		}
	}

	/**
	 * Adds the RSS into the headers
	 *
	 * @since	1.3
	 * @access	public
	 */
	public function addRss($link)
	{
		if (!$this->config->get('rss.enabled')) {
			return;
		}

		$glue = $this->jconfig->getValue('sef') ? '?' : '&';
		$rss = $link . $glue . 'format=feed&type=rss';
		$atom = $link . $glue . 'format=feed&type=atom';

		if ($this->doc->getType() == 'html') {
			// Add RSS specs
			$attribs = array('type' => 'application/rss+xml', 'title' => 'RSS 2.0');
			$this->doc->addHeadLink($rss, 'alternate', 'rel', $attribs);

			// Add Atom specs
			$attribs = array('type' => 'application/atom+xml', 'title' => 'Atom 1.0');
			$this->doc->addHeadLink($atom, 'alternate', 'rel', $attribs);
		}

		// Set the rss link to the class scope so child can easily get the url
		$this->rssLink = $rss;
	}

	/**
	 * determine author email in rss
	 *
	 * @since	1.3
	 * @access	public
	 */
	public function getRssEmail($author)
	{
		if ($this->jconfig->getValue('feed_email') == 'none') {
			return '';
		}

		if ($this->jconfig->getValue('feed_email') == 'author') {
			return $author->email;
		}

		return $this->jconfig->getValue('mailfrom');
	}

	/**
	 * Determines if the current view should be locked down.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function lockdown()
	{
		// Default, all views are locked down.
		$state 	= true;

		if( method_exists( $this , 'isLockDown' ) )
		{
			$state 	= $this->isLockDown();
		}

		return $state;
	}

	/**
	 * Responsible to render the views / layouts from the front end.
	 * This is a single point of entry function.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function display($tpl = null)
	{
		$type = $this->doc->getType();
		$show = $this->input->get('show', '', 'string');

		if ($type != 'html') {
			return parent::display($tpl);
		}

		// Set vary user-agent
		$this->app->setHeader('Vary', 'User-Agent', true);

		// we no longer need to check this. @sam @2020-11-17
		// Check for unsynced privacy access
		// if (ES::hasUnsyncedPrivacy()) {
		// 	$theme = ES::themes();
		// 	echo $theme->output('site/structure/privacy.error');
		// 	return;
		// }

		// Check for language translation on sef
		ES::checkSefLangTranslation();

		// Check for welcome message
		ES::checkWelcomeMessage();

		// check if there is any error message from oauth dialog
		ES::checkOauthErrorMessage();

		// check if keepalive is required or not.
		ES::keepAlive();

		// Include main structure here.
		$theme = ES::themes();

		// Do not allow zooming on mobile devices
		if ($theme->isMobile()) {
			$viewportAttribute = 'width=device-width, initial-scale=1.0';

			if (!$this->config->get('mobile.userscaling')) {
				$viewportAttribute .= ', maximum-scale=1.0, user-scalable=no';
			}

			$this->doc->setMetaData('viewport', $viewportAttribute);
		}

		// Capture output.
		ob_start();
		parent::display($tpl);
		$contents = ob_get_contents();
		ob_end_clean();

		// Trigger apps to allow them to attach html output on the page too.
		$dispatcher = ES::dispatcher();
		$dispatcher->trigger('user', 'onComponentOutput', array(&$contents));

		// Get the menu's suffix
		$suffix = $this->getMenuSuffix();

		// Get the current view.
		$view = $this->input->get('view', '', 'cmd');
		$view = !empty($view) ? ' view-' . $view : '';

		// Get the current task
		$task = $this->input->get('task', '', 'cmd');
		$task = !empty($task) ? ' task-' . $task : '';

		// Get any "id" or "cid" from the request.
		$object = $this->input->get('id', $this->input->get('cid', 0, 'int'), 'int');
		$object = !empty($object) && is_string($object) ? ' object-' . $object : '';

		// Get any layout
		$layout = $this->input->get('layout', '', 'cmd');
		$layout = !empty($layout) ? ' layout-' . $layout : '';


		$pageTitle = $this->page->title;

		// remove linefeed
		$pageTitle = str_replace(array("\r\n", "\r", "\n", "&#10;"), "", $pageTitle);

		$theme->set('pageTitle', $pageTitle);
		$theme->set('suffix', $suffix);
		$theme->set('layout', $layout);
		$theme->set('object', $object);
		$theme->set('task', $task);
		$theme->set('view', $view);
		$theme->set('show', $show);
		$theme->set('contents', $contents);

		// Component template scripts
		$page = ES::document();
		$scripts = '<script>' . implode('</script><script>', $page->inlineScripts) . '</script>';
		$theme->set('scripts', $scripts);

		// Ensure component template scripts don't get added to the head.
		$page->inlineScripts = array();

		echo $theme->output('site/structure/default');
	}

	/**
	 * Retrieve the menu suffix for a page
	 *
	 * @since	1.2.8
	 * @access	public
	 */
	public function getMenuSuffix()
	{
		$menu 	= $this->app->getMenu()->getActive();
		$suffix	= '';

		if ($menu) {
			$suffix = $menu->getParams()->get('pageclass_sfx', '');
		}

		return $suffix;
	}

	/**
	 * Generic 404 error page
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function error()
	{
		// Need to respect the "Enable 404 Error Redirection" setting behavior
		return ES::raiseError(404, JText::_('COM_EASYSOCIAL_PAGE_IS_NOT_AVAILABLE'));
	}


	/**
	 * Allows child library to validate an authentication code
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function validateAuth()
	{
		// Get user's authentication code
		$auth = $this->input->get('auth', '', 'string');

		// Get user id
		$userId = $this->input->get('userid', 0, 'int');

		if (!$auth || !$userId) {
			$post = $this->input->getArray('POST');
			$auth = isset($post['auth']) ? $post['auth'] : null;
			$userId = isset($post['userid']) ? $post['userid'] : null;
		}

		// Get the current logged in user's information
		$user = ES::user($userId);

		// If user authentication key is not valid, throw an error
		if (!$auth || $auth != $user->auth) {

			$this->set('code', 403);
			$this->set('message', JText::_('Invalid user id provided.'));

			return self::display();
		}

		return $user->id;
	}
}
