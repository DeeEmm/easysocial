<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2017 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

class EasySocialViewPages extends EasySocialAdminView
{
	/**
	 * Displays a dialog confirmation before deleting a page category
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function confirmDeleteCategory()
	{
		$theme = ES::themes();

		$contents = $theme->output('admin/pages/dialogs/delete.category');
		return $this->ajax->resolve($contents);
	}

	/**
	 * Allow caller to delete category avatar
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function confirmRemoveCategoryAvatar()
	{
		$theme = ES::themes();
		$id = $this->input->get('id', 0, 'int');

		$theme->set('id', $id);
		$contents = $theme->output('admin/pages/dialogs/remove.category.avatar');

		return $this->ajax->resolve($contents);
	}

	/**
	 * Displays the new page creation form as we need the admin to select a category.
	 *
	 * @since 	2.0
	 * @access	public
	 */
	public function createDialog()
	{
		$categoryList = ES::populateCategories('category_id', false, array(), SOCIAL_TYPE_PAGE, 'data-input-category', false, true);

		$theme = ES::themes();
		$theme->set('categoryList', $categoryList);
		$contents = $theme->output('admin/pages/dialogs/create.page');

		return $this->ajax->resolve($contents);
	}

	/**
	 * Show user listing to add users into the page
	 *
	 * @since  1.2
	 * @access public
	 */
	public function addMembers()
	{
		$clusterId = $this->input->get('id', 0, 'int');

		$theme = ES::themes();
		$theme->set('clusterId', $clusterId);
		$contents = $theme->output('admin/pages/dialogs/browse.addusers');

		return $this->ajax->resolve($contents);
	}

	/**
	 * Displays the owner switching form
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function switchOwner()
	{
		$theme = ES::themes();

		$ids = $this->input->get('ids', array(), 'default');

		if (!$ids) {
			return $this->ajax->reject(JText::_('COM_ES_NO_ITEMS_SELECTED'));
		}

		$theme->set('ids', $ids);
		$contents = $theme->output('admin/pages/dialogs/browse.users');

		return $this->ajax->resolve($contents);
	}

	/**
	 * Displays delete page confirmation
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function deleteConfirmation()
	{
		$theme = ES::themes();

		$contents = $theme->output('admin/pages/dialogs/delete.page');

		return $this->ajax->resolve($contents);
	}

	/**
	 * Renders the dialog to switch category
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function switchCategory()
	{
		$ids = $this->input->getVar('ids');
		$categories = ES::populateCategories('category', 0, array(), SOCIAL_TYPE_PAGE, '', false, true);

		$theme = ES::themes();
		$theme->set('ids', $ids);
		$theme->set('categories', $categories);
		$theme->set('type', SOCIAL_TYPE_PAGES);

		$contents = $theme->output('admin/clusters/dialogs/category.switch');

		return $this->ajax->resolve($contents);
	}

	/**
	 * Displays the owner switching form
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function confirmSwitchOwner()
	{
		$theme = ES::themes();

		$ids = $this->input->get('id');
		$userId = $this->input->get('userId');
		$newOwner = ES::user($userId);

		$theme->set('ids', $ids);
		$theme->set('user', $newOwner);
		$theme->set('type', 'pages');

		$contents = $theme->output('admin/clusters/dialogs/switch.owner');

		return $this->ajax->resolve($contents);
	}

	/**
	 * Displays the reject dialog
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function rejectPage()
	{
		// Get the page ids that should be rejected
		$ids = $this->input->get('ids', array(), 'array');

		$theme = ES::themes();
		$theme->set('ids', $ids);
		$contents = $theme->output('admin/pages/dialogs/reject');

		return $this->ajax->resolve($contents);
	}

	/**
	 * Displays the approve dialog
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function approvePage()
	{
		// Get the page ids that should be rejected
		$ids = $this->input->get('ids');
		$ids = ES::makeArray($ids);

		$theme = ES::themes();
		$theme->set('ids', $ids);
		$contents = $theme->output('admin/pages/dialogs/approve');

		return $this->ajax->resolve($contents);
	}

	/**
	 * Allows caller to browse pages
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function browse()
	{
		$callback = $this->input->get('jscallback', '', 'cmd');

		$theme = ES::themes();
		$theme->set('callback', $callback);
		$content = $theme->output('admin/pages/dialogs/browse');

		return $this->ajax->resolve($content);
	}

	/**
	 * Allows caller to browse a category via the internal dialog system
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function browseCategory()
	{
		$callback = $this->input->get('jscallback', '', 'cmd');

		$theme = ES::themes();
		$theme->set('callback', $callback);
		$content = $theme->output('admin/pages/dialogs/browse.category');

		return $this->ajax->resolve($content);
	}

	public function createBlankCategory($data)
	{
		if ($data === false) {
			return $this->ajax->reject($this->getError());
		}

		$this->ajax->resolve($data);
	}
}
