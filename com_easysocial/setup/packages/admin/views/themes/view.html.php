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

ES::import('admin:/views/views');

class EasySocialViewThemes extends EasySocialAdminView
{
	/**
	 * Displays a list of themes on the site.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function display($tpl = null)
	{
		// Set page heading
		$this->setHeading('COM_EASYSOCIAL_TOOLBAR_TITLE_THEMES', 'COM_EASYSOCIAL_DESCRIPTION_THEMES');

		JToolbarHelper::custom('makeDefault', 'default' , '', JText::_('COM_EASYSOCIAL_TOOLBAR_TITLE_BUTTON_MAKE_DEFAULT'), false);

		// Load themes model
		$model = ES::model('Themes');
		$themes = $model->getThemes();
		$disallowSettings = array('elegant', 'wireframe', 'frosty', 'dark', 'vortex');

		$this->set('disallowSettings', $disallowSettings);
		$this->set('themes', $themes);

		parent::display('admin/themes/default/default');
	}

	/**
	 * Renders the editor to allow user to edit the theme file
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function edit()
	{
		$this->hideSidebar();
		$this->setHeading('COM_EASYSOCIAL_TOOLBAR_TITLE_THEMES_EDITOR');

		$element = $this->input->get('element', '', 'cmd');
		$id = $this->input->get('id', '', 'default');

		// Do not allow to view this page if there is no element provided
		if (!$element) {
			$this->setMessage('COM_EASYSOCIAL_THEMES_PLEASE_SELECT_THEME_TO_BE_EDITED', ES_ERROR);
			$this->info->set($this->getMessage());

			return $this->redirect('index.php?option=com_easysocial&view=themes');
		}

		// Get a list of theme files from this template file
		$model = ES::model('Themes');
		$files = $model->getFiles($element);

		$item = null;
		$table = ES::table('ThemeOverrides');

		if ($id) {
			$item = $model->getFile($id, $element, true);

			JToolBarHelper::apply('saveFile');

			if ($item->modified) {
				JToolBarHelper::trash('revert', JText::_('COM_EASYSOCIAL_REVERT_CHANGES'), false);

				$table->load(array('file_id' => $item->override));
			}
		}

		JToolBarHelper::cancel();

		// Always use codemirror
		$editor = ES::getEditor('codemirror');

		$this->set('table', $table);
		$this->set('id', $id);
		$this->set('editor', $editor);
		$this->set('item', $item);
		$this->set('element', $element);
		$this->set('files', $files);

		parent::display('admin/themes/edit/default');
	}

	/**
	 * Post processing after saving a file
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function saveFile($file)
	{
		$this->info->set($this->getMessage());

		return $this->redirect('index.php?option=com_easysocial&view=themes&layout=edit&element=' . $file->element . '&id=' . $file->id);
	}

	/**
	 * Post processing after reverting a file
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function revert($file)
	{
		$this->info->set($this->getMessage());

		return $this->redirect('index.php?option=com_easysocial&view=themes&layout=edit&element=' . $file->element);
	}

	/**
	 * Displays the theme's form
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function form()
	{
		$element = $this->input->get('element', '', 'default');

		if (!$element) {
			$this->redirect('index.php?option=com_easysocial&view=themes');
			$this->close();
		}

		JToolbarHelper::apply('apply', JText::_('COM_EASYSOCIAL_TOOLBAR_TITLE_BUTTON_SAVE'), false, false);
		JToolbarHelper::save('save', JText::_('COM_EASYSOCIAL_TOOLBAR_TITLE_BUTTON_SAVE_AND_CLOSE'));
		JToolbarHelper::cancel();

		$model = ES::model('Themes');
		$theme = $model->getTheme($element);

		// Set the page heading
		$this->setHeading($theme->name);
		$this->setDescription($theme->desc);

		$this->set('theme', $theme);

		parent::display('admin/themes/form/default');
	}

	/**
	 * Allows site admin to insert custom css codes
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function custom()
	{
		$this->setHeading('COM_ES_TITLE_THEMES_CUSTOM_CSS');

		$editor = ES::getEditor('codemirror');

		JToolbarHelper::apply('saveCustomCss');

		$model = ES::model('Themes');
		$path = $model->getCustomCssTemplatePath();
		$contents = '';

		if (JFile::exists($path)) {
			$contents = file_get_contents($path);
		}

		$this->set('contents', $contents);
		$this->set('editor', $editor);

		parent::display('admin/themes/custom/default');
	}

	/**
	 * Displays the theme's installation page
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function install()
	{
		JToolbarHelper::custom('upload', 'upload', '', JText::_('COM_EASYSOCIAL_UPLOAD_AND_INSTALL'), false);

		// Set the page heading
		$this->setHeading('COM_EASYSOCIAL_TOOLBAR_TITLE_THEMES_INSTALL', 'COM_EASYSOCIAL_DESCRIPTION_THEMES_INSTALL');

		parent::display('admin/themes/install/default');
	}

	/**
	 * Upload view that sets the messge to redirect back to install page
	 *
	 * @since  1.1
	 * @access public
	 */
	public function upload()
	{
		return $this->redirect('index.php?option=com_easysocial&view=themes&layout=install');
	}

	/**
	 * Make a theme as a default theme
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function makeDefault()
	{
		return $this->redirect('index.php?option=com_easysocial&view=themes');
	}

	/**
	 * Post processing after a theme is stored
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function store($task, $element = null)
	{
		$url = 'index.php?option=com_easysocial&view=themes';
		$active = $this->input->get('activeTab', '', 'default');

		if ($active) {
			$active	= '&activeTab=' . $active;
		}

		if ($element && ($task == 'apply' && $task != 'save')) {
			$url = 'index.php?option=com_easysocial&view=themes&layout=form&element=' . $element . $active;
		}

		return $this->redirect($url);
	}
}
