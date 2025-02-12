<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2019 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

class EasySocialModSidebarHelper extends EasySocial
{
	public $views = array(
		'profile',
		'profiles',
		'groups',
		'events',
		'pages',
		'users',
		'polls',
		'audios',
		'videos',
		'users',
		'friends',
		'search',
		'activities',
		'followers',
		'albums',
		'photos',
		'marketplaces'
	);

	/**
	 * Loads the adapter for a certain view
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function getAdapter($lib)
	{
		static $adapters = array();

		$view = $this->getCurrentView();

		if (!isset($adapters[$view])) {
			$adapters[$view] = false;
			$path = __DIR__ . '/adapters/' . strtolower($view) . '.php';

			$exists = JFile::exists($path);

			if (!$exists) {
				return $adapters[$view];
			}

			require_once($path);

			$className = 'SocialSidebar' . ucfirst($view);

			if (!class_exists($className)) {
				return $adapters[$view];
			}
			$adapters[$view] = new $className($lib);
		}

		return $adapters[$view];
	}

	/**
	 * Retrieves the current view
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function getCurrentView()
	{
		$view = $this->input->get('view', '', 'cmd');

		return $view;
	}

	/**
	 * Determines if the current view is a supported view which requires
	 * the sidebar
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function isSupportedView()
	{
		static $supported = null;

		if (is_null($supported)) {
			$supported = false;
			$view = $this->getCurrentView();
			$option = $this->input->get('option', '', 'default');

			if ($option !== 'com_easysocial') {
				return $supported;
			}

			// Handle item views
			if (!in_array($view, $this->views)) {
				return $supported;
			}

			$supported = true;
		}

		return $supported;
	}
}
