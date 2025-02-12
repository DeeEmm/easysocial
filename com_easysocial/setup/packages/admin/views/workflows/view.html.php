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

class EasySocialViewWorkflows extends EasySocialAdminView
{
	/**
	 * Renders workflow form page
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function form()
	{
		$this->hideSidebar();

		$id = $this->input->get('id', 0, 'int');
		$type = $this->input->get('type', SOCIAL_TYPE_USER, 'default');

		$workflow = ES::workflows($id, $type);

		if ($id) {
			$type = $workflow->type;
		}

		$title = 'COM_ES_MENU_GROUP_WORKFLOWS_FORM';
		$description = '';

		$steps = $workflow->getSteps();
		$installedFields = $workflow->getInstalledFields();

		JToolbarHelper::apply('apply', JText::_('COM_EASYSOCIAL_TOOLBAR_TITLE_BUTTON_SAVE'), false, false);
		JToolbarHelper::save('save', JText::_('COM_EASYSOCIAL_TOOLBAR_TITLE_BUTTON_SAVE_AND_CLOSE'));
		JToolbarHelper::save2new('savenew', JText::_('COM_EASYSOCIAL_TOOLBAR_TITLE_BUTTON_SAVE_AND_NEW'));

		if ($workflow->id) {
			JToolbarHelper::save2copy('savecopy', JText::_('COM_EASYSOCIAL_TOOLBAR_TITLE_BUTTON_SAVE_AS_COPY'));
		}

		JToolbarHelper::divider();
		JToolbarHelper::cancel('cancel', JText::_('COM_EASYSOCIAL_TOOLBAR_TITLE_BUTTON_CANCEL'));

		$this->setHeading($workflow->getTitle(), $workflow->getDescription());

		// Set custom action to edit the title and description of the workflow
		$this->setCustomAction('<a href="javascript:void(0);" class="btn btn-sm btn-es-default-o t-mt--md" data-workflow-edit-heading><i class="far fa-edit"></i>&nbsp; ' . JText::_('COM_ES_EDIT_WORKFLOW') . '</a>');

		// Default to be user
		$customRedirect = 'index.php?option=com_easysocial&view=users&layout=workflows';

		if ($type == 'group') {
			$customRedirect = 'index.php?option=com_easysocial&view=groups&layout=workflows';
		}

		if ($type == 'event') {
			$customRedirect = 'index.php?option=com_easysocial&view=events&layout=workflows';
		}

		if ($type == 'page') {
			$customRedirect = 'index.php?option=com_easysocial&view=pages&layout=workflows';
		}

		if ($type == 'marketplace') {
			$customRedirect = 'index.php?option=com_easysocial&view=marketplaces&layout=workflows';
		}

		$this->set('customRedirect', $customRedirect);
		$this->set('workflow', $workflow);
		$this->set('steps', $steps);
		$this->set('installedFields', $installedFields);

		parent::display('admin/workflows/form/default');
	}

	/**
	 * Post processing after workflow is saved
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function save($workflow, $task)
	{
		// If there's an error on the storing, we don't need to perform any redirection.
		if ($this->hasErrors()) {
			return $this->form($workflow);
		}

		$url = 'index.php?option=com_easysocial&view=workflows';
		$customRedirect = $this->input->get('customRedirect', '', 'default');

		if ($customRedirect) {
			$url = $customRedirect;
		}

		if ($task == 'apply') {
			return $this->redirect('index.php?option=com_easysocial&view=workflows&layout=form&id=' . $workflow->id);
		}

		if ($task == 'savenew') {
			return $this->redirect('index.php?option=com_easysocial&view=workflows&layout=form&type=' . $workflow->type);
		}

		return $this->redirect($url);
	}

	/**
	 * Post processing after workflow is saved
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function duplicate()
	{
		// If there's an error on the storing, we don't need to perform any redirection.
		if ($this->hasErrors()) {
			return $this->form($workflow);
		}

		$url = 'index.php?option=com_easysocial&view=workflows';
		$customRedirect = $this->input->get('customRedirect', '', 'default');

		if ($customRedirect) {
			$url = $customRedirect;
		}

		return $this->redirect($url);
	}

	/**
	 * Post processing after workflows is deleted
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function remove()
	{
		$url = 'index.php?option=com_easysocial&view=workflows';
		$customRedirect = $this->input->get('customRedirect', '', 'default');

		if ($customRedirect) {
			$url = $customRedirect;
		}

		return $this->redirect($url);
	}
}
