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

class EasySocialViewPagesItemHelper extends EasySocial
{
	/**
	 * Retrieves the about permalink for the page
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function getAboutPermalink()
	{
		static $permalink = null;

		if (is_null($permalink)) {
			$page = $this->getActivePage();
			$defaultDisplay = $this->getDefaultDisplay();

			$permalink = ESR::pages(array('id' => $page->getAlias(), 'type' => 'info', 'layout' => 'item'));


			if ($defaultDisplay == 'info') {
				$permalink = $page->getPermalink();
			}
		}

		return $permalink;
	}

	/**
	 * Determines the default display page of the page
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function getDefaultDisplay()
	{
		static $default = null;

		if (is_null($default)) {
			$default = $this->config->get('pages.item.display', 'timeline');
		}

		return $default;
	}

	/**
	 * Determines the page that is currently being viewed
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function getActivePage()
	{
		static $page = null;

		if (is_null($page)) {
			$id = $this->input->get('id', 0, 'int');
			$page = ES::page($id);

			// Check if the page is valid
			if (!$id || !$page->id || !$page->canAccess()) {
				ES::raiseError(404, JText::_('COM_EASYSOCIAL_PAGE_NOT_FOUND'));
			}
		}

		return $page;
	}
}
