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

class EasySocialViewPrivacy extends EasySocialAdminView
{
	/**
	 * Display dialog for confirming deletion
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function confirmDelete()
	{
		$theme = ES::themes();
		$contents = $theme->output('admin/privacy/dialogs/delete');

		return $this->ajax->resolve($contents);
	}

	/**
	 * Sends back the list of files to the caller.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function discoverFiles($files = array())
	{
		return $this->ajax->resolve($files);
	}

	/**
	 * Processes ajax calls to scan rules.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function scan($obj)
	{
		$message = JText::sprintf('COM_EASYSOCIAL_DISCOVER_CHECKED_OUT', $obj->file, count($obj->rules));

		return $this->ajax->resolve($message);
	}

	/**
	 * Display custom field dialog
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function fields()
	{
		$selected = $this->input->get('selected', '', 'default');

		$isDefault = $this->input->get('isDefault', false, 'bool');


		// get fields
		$model = ES::model('Privacy');
		$fields = $model->getConfigFields();

		$current = array();
		if ($selected) {
			$current = explode(',', $selected);
		}

		$title = JText::_('COM_ES_PRIVACY_EDIT_FIELD_TITLE');
		if ($isDefault) {
			$title = JText::_('COM_ES_PRIVACY_EDIT_DEFAULT_FIELD_TITLE');
		}

		$theme = ES::themes();
		$theme->set('fields', $fields);
		$theme->set('title', $title);
		$theme->set('current', $current);
		$contents = $theme->output('admin/privacy/dialogs/fields');

		return $this->ajax->resolve($contents);
	}
}
