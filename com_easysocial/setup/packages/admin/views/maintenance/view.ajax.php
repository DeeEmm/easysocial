<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) 2010 - 2014 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

// Include main views file.
ES::import('admin:/views/views');

class EasySocialViewMaintenance extends EasySocialAdminView
{
	public function runscript()
	{
		if ($this->hasErrors()) {
			return $this->ajax->reject($this->getMessage());
		}

		return $this->ajax->resolve();
	}

	public function getDatabaseStats($versions)
	{
		return $this->ajax->resolve($versions);
	}

	public function synchronizeDatabase()
	{
		return $this->ajax->resolve();
	}

	/**
	 * Get privacy stats that need to be fixed.
	 *
	 * @since	3.1
	 * @access	public
	 */
	public function getPrivacyStats($counts)
	{
		return $this->ajax->resolve($counts);
	}

	/**
	 * Post processing on synchonizing privacy access
	 *
	 * @since	3.1
	 * @access	public
	 */

	public function synchronizePrivacy($counts)
	{
		return $this->ajax->resolve($counts);
	}
}
