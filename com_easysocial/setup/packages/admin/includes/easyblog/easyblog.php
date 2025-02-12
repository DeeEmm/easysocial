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

class SocialEasyBlog extends EasySocial
{
	/**
	 * Determines if EasyBlog is installed and exists on the site
	 *
	 * @since	2.2
	 * @access	public
	 */
	public function exists()
	{
		static $loaded = null;

		if (is_null($loaded)) {
			$file = JPATH_ADMINISTRATOR . '/components/com_easyblog/includes/easyblog.php';

			jimport('joomla.filesystem.file');

			$exists = JFile::exists($file);
			$loaded = false;

			if ($exists && JComponentHelper::isEnabled('com_easyblog')) {
				require_once($file);

				$loaded = true;
			}
		}

		return $loaded;
	}

	/**
	 * Loads language file from EasyBlog
	 *
	 * @since	2.2
	 * @access	public
	 */
	public function loadLanguage()
	{
		static $loaded = null;

		if (is_null($loaded)) {
			JFactory::getLanguage()->load('com_easyblog', JPATH_ROOT);
			
			$loaded = true;	
		}
		
		return $loaded;
	}

	/**
	 * Determines if the toolbar should be rendered
	 *
	 * @since	2.2
	 * @access	public
	 */
	public function hasToolbar()
	{
		if (!$this->config->get('general.layout.toolbareasyblog') || !$this->exists()) {
			return false;
		}

		return true;
	}

	/**
	 * Renders the dropdown toolbar for EasyBlog
	 *
	 * @since	2.2
	 * @access	public
	 */
	public function toolbar()
	{
		if (!$this->hasToolbar()) {
			return;
		}

		$this->loadLanguage();

		$config = EB::config();
		$acl = EB::acl();
		$showManage = false;

		if ($acl->get('add_entry') || $acl->get('create_post_templates') 
			|| (EB::isSiteAdmin() || ($acl->get('moderate_entry') || ($acl->get('manage_pending') && $acl->get('publish_entry')))) 
			|| $acl->get('create_category')
			|| $acl->get('create_tag')) {
			$showManage = true;
		}

		$theme = ES::themes();
		$theme->set('config', $config);
		$theme->set('acl', $acl);
		$theme->set('showManage', $showManage);
		
		$output = $theme->output('site/toolbar/easyblog');

		return $output;
	}
}
