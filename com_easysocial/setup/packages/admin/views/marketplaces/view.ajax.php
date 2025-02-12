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

class EasySocialViewMarketplaces extends EasySocialAdminView
{
	/**
	 * Renders the marketplace listing event dialog
	 *
	 * @since   3.3
	 * @access  public
	 */
	public function createDialog()
	{
		$categoryList = ES::populateCategories('category_id', false, array(), SOCIAL_TYPE_MARKETPLACE, 'data-input-category', false);

		$theme = ES::themes();
		$theme->set('categoryList', $categoryList);

		$contents = $theme->output('admin/marketplaces/dialogs/create');

		return $this->ajax->resolve($contents);
	}

	/**
	 * Displays the delete confirmation dialog
	 *
	 * @since   4.0
	 * @access  public
	 */
	public function deleteDialog()
	{
		$theme = ES::themes();
		$contents = $theme->output('admin/marketplaces/dialogs/delete');

		return $this->ajax->resolve($contents);
	}

	/**
	 * Renders the dialog to confirm removal of category avatar
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function confirmRemoveCategoryAvatar()
	{
		$id = $this->input->get('id', 0, 'int');

		$theme = ES::themes();
		$theme->set('id', $id);
		$contents = $theme->output('admin/clusters/dialogs/remove.category.avatar');

		return $this->ajax->resolve($contents);
	}

	/**
	 * Renders the delete marketplaces category dialog
	 *
	 * @since   4.0
	 * @access  public
	 */
	public function deleteCategoryDialog()
	{
		$theme = ES::themes();

		$contents = $theme->output('admin/marketplaces/dialogs/delete.category');

		return $this->ajax->resolve($contents);
	}

	/**
	 * Post process after an event avatar has been removed
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function removeCategoryAvatar()
	{
		return $this->ajax->resolve();
	}

	/**
	 * Renders the browse listing dialog
	 *
	 * @since   4.0
	 * @access  public
	 */
	public function browse()
	{
		$callback = $this->input->get('jscallback');
		$multiple = $this->input->get('multiple', true, 'bool');

		$theme = ES::themes();
		$theme->set('multiple', $multiple);
		$theme->set('callback', $callback);
		$content = $theme->output('admin/marketplaces/dialogs/browse');

		return $this->ajax->resolve($content);
	}

	/**
	 * Browses for category
	 *
	 * @since   4.0
	 * @access  public
	 */
	public function browseCategory()
	{
		$callback = $this->input->get('jscallback', '', 'cmd');

		$theme = ES::themes();
		$theme->set('callback', $callback);
		$content = $theme->output('admin/marketplaces/dialogs/browse.category');

		return $this->ajax->resolve($content);
	}

	/**
	 * Post process after saving the event
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function store()
	{
		return $this->ajax->resolve();
	}

	/**
	 * Displays the reject dialog
	 *
	 * @since   4.0
	 * @access  public
	 */
	public function rejectListing()
	{
		// Get the listing ids that should be rejected
		$ids = $this->input->get('ids');
		$ids = ES::makeArray($ids);

		$theme = ES::themes();
		$theme->set('ids', $ids);
		$contents = $theme->output('admin/marketplaces/dialogs/reject');

		return $this->ajax->resolve($contents);
	}

	/**
	 * Displays the approve dialog
	 *
	 * @since   4.0
	 * @access  public
	 */
	public function approveListing()
	{
		// Get the listing ids that should be rejected
		$ids = $this->input->get('ids');
		$ids = ES::makeArray($ids);

		$theme = ES::themes();
		$theme->set('ids', $ids);
		$contents = $theme->output('admin/marketplaces/dialogs/approve');

		return $this->ajax->resolve($contents);
	}

	public function createBlankCategory($data)
	{
		if ($data === false) {
			return $this->ajax->reject($this->getError());
		}

		$this->ajax->resolve($data);
	}

	/**
	 * Renders the switch event owner dialog
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function confirmSwitchOwner()
	{
		$userid = $this->input->getInt('userId');
		$user = ES::user($userid);
		$ids = $this->input->get('ids', array(), 'default');

		$theme = ES::themes();

		$theme->set('user', $user);
		$theme->set('ids', $ids);

		$contents = $theme->output('admin/marketplaces/dialogs/switch.owner');

		return $this->ajax->resolve($contents);
	}

	/**
	 * Renders the user listings browser for admin to choose a new owner for a listing
	 *
	 * @since   4.0.0
	 * @access  public
	 */
	public function switchOwner()
	{
		$ids = $this->input->get('ids', array(), 'default');

		if (!$ids) {
			return $this->ajax->reject(JText::_('COM_ES_NO_ITEMS_SELECTED'));
		}

		$theme = ES::themes();
		$theme->set('ids', $ids);

		$contents = $theme->output('admin/marketplaces/dialogs/browse.users');

		return $this->ajax->resolve($contents);
	}

	/**
	 * Renders the switch category form for listing
	 *
	 * @since   4.0.0
	 * @access  public
	 */
	public function switchCategory()
	{
		$ids = $this->input->getVar('ids');
		$categories = ES::populateCategories('category', 0, array(), SOCIAL_TYPE_MARKETPLACE, '', false);

		$theme = ES::themes();
		$theme->set('ids', $ids);
		$theme->set('categories', $categories);
		$theme->set('type', SOCIAL_TYPE_MARKETPLACES);

		$contents = $theme->output('admin/clusters/dialogs/category.switch');

		return $this->ajax->resolve($contents);
	}
}
