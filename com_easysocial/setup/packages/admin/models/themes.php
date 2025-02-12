<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2016 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

ES::import('admin:/includes/model');

jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.file');

class EasySocialModelThemes extends EasySocialModel
{
	private $data			= null;

	function __construct()
	{
		parent::__construct( 'themes' );
	}

	/**
	 * Stores the theme settings in the database
	 *
	 * @since	1.0
	 * @access	public
	 * @param	string
	 * @return	bool		True if success, false otherwise.
	 */
	public function update( $element , $data )
	{
		$db 	= ES::db();
		$sql 	= $db->sql();

		// Check if the records exists first or not.
		$sql->select( '#__social_themes' );
		$sql->column( 'COUNT(1)' );
		$sql->where( 'element' , $element );

		$db->setQuery( $sql );
		$exists 	= $db->loadResult();

		// Clear previous results
		$sql->clear();

		// Convert the array into a standard json string.
		$params	= ES::makeJSON( $data );

		$obj 	= new stdClass();
		$obj->element 	= $element;
		$obj->params 	= $params;

		if( !$exists )
		{
			// Insert
			$state	= $db->insertObject( '#__social_themes' , $obj );
		}
		else
		{
			// Update
			$state 	= $db->updateObject( '#__social_themes' , $obj , 'element' );
		}

		if( !$state )
		{
			$this->setError( JText::_( 'There was an error when saving the theme parameters.' ) );
			return false;
		}

		return true;
	}

	/**
	 * Installs a new theme
	 *
	 * @since	1.0
	 * @access	public
	 * @param	string
	 * @return
	 */
	public function install($file)
	{
		$source = $file['tmp_name'];
		$config = ES::config();
		$fileName = md5($file['name'] . ES::date()->toMySQL());
		$fileExtension = '_themes_install.zip';
		$destination = SOCIAL_TMP . '/' . $fileName . $fileExtension;
		$state = JFile::upload($source , $destination, false, true);

		if (!$state) {
			$this->setError(JText::_('COM_EASYSOCIAL_THEMES_INSTALLER_ERROR_COPY_FROM_PHP'));
			return false;
		}

		// Extract to this folder
		$extracted = dirname($destination) . '/' . $fileName . '_themes_install';
		$state = ESArchive::extract($destination, $extracted);

		// Get the configuration file.
		$manifest = $extracted . '/config/template.json';

		// Get the theme object
		$theme = ES::makeObject($manifest);

		// Move it to the appropriate folder
		$finalDest = SOCIAL_SITE_THEMES . '/' . strtolower($theme->element);

		// @TODO: If folder exists, overwrite it. For now, just throw an error.
		if (JFolder::exists($finalDest))
		{
			// Cleanup folder
			JFile::delete($destination);
			JFolder::delete($extracted);

			$this->setError(JText::sprintf('COM_EASYSOCIAL_THEMES_INSTALLER_ERROR_SAME_THEME_FOLDER_EXISTS', $theme->element));
			return false;
		}

		// Move the extracted folder over to the final destination
		$state = JFolder::move($extracted , $finalDest);

		if (!$state) {
			// Cleanup folder
			JFile::delete($destination);
			JFolder::delete($extracted);

			$this->setError(JText::_('COM_EASYSOCIAL_THEMES_INSTALLER_ERROR_MOVING_FOLDER_TO_THEMES_FOLDER'));
			return false;
		}

		// Cleanup the zip file.
		JFile::delete($destination);

		return $theme->element;
	}

	/**
	 * Get's a list of themes that is installed on the site.
	 *
	 * @since	1.0
	 * @access	public
	 * @param	string	The location of the theme. (site/admin)
	 * @return	Array	An array of theme files.
	 */
	public function getTheme( $element , $location = 'site' )
	{
		// Get our own config object
		$config = ES::config();

		$folder = $location == 'admin' ? SOCIAL_ADMIN_THEMES : SOCIAL_SITE_THEMES;

		// Get a list of theme folders.
		$manifest = $folder . '/' . $element . '/config/template.json';

		if (!JFile::exists($manifest)) {
			$this->setError(JText::_('Theme manifest file not available'));
			return false;
		}

		$obj = ES::makeObject($manifest);

		// Set new states on the object.
		$obj->id = $obj->element;
		$obj->default = $config->get('theme.' . $location) == $obj->element ? true : false;

		// Render the form for this theme.
		$obj->form = $this->renderForm($element);

		return $obj;
	}

	/**
	 * Returns the location to the form's file.
	 *
	 * @since	1.0
	 * @access	public
	 * @param	string
	 * @return
	 */
	public function getFormLocation( $element, $location = 'site' )
	{
		$folder 	= $location == 'admin' ? SOCIAL_ADMIN_THEMES : SOCIAL_SITE_THEMES;

		// Get a list of theme folders.
		$manifest	= $folder . '/' . $element . '/config/form.json';

		jimport( 'joomla.filesystem.file' );

		if( !JFile::exists( $manifest ) )
		{
			return false;
		}

		return $manifest;
	}

	/**
	 * Returns the default params of the theme
	 *
	 * @since	1.0
	 * @access	public
	 * @param	string
	 * @return
	 */
	public function getDefaultParams( $element , $location = 'site' )
	{
		$manifest	= $location == 'admin' ? SOCIAL_ADMIN_THEMES : SOCIAL_SITE_THEMES;
		$manifest 	= $manifest . '/' . $element . '/config/defaults.json';

		jimport( 'joomla.filesystem.file' );

		if( !JFile::exists( $manifest ) )
		{
			$manifest 	= SOCIAL_SITE_THEMES . '/wireframe/config/defaults.json';
		}

		$registry 	= ES::registry( $manifest );

		return $registry;
	}


	/**
	 * Returns the params of the theme
	 *
	 * @since	1.0
	 * @access	public
	 * @param	string
	 * @return
	 */
	public function getParams( $element , $location = 'site' )
	{
		// Get the stored params.
		$db 	= ES::db();
		$sql 	= $db->sql();

		$sql->select( '#__social_themes' );
		$sql->where( 'element' , $element );

		$db->setQuery( $sql );
		$result	= $db->loadObject();

		if( !$result )
		{
			return false;
		}

		$registry 	= ES::registry( $result->params );

		return $registry;
	}

	/**
	 * Renders the theme's form.
	 *
	 * @since	1.0
	 * @access	public
	 * @param	string
	 * @return
	 */
	public function renderForm($element, $location = 'site')
	{
		$file = $this->getFormLocation($element, $location);

		if ($file === false) {
			return false;
		}

		$activeTab = $this->input->get('activeTab', '', 'default');

		// Create a new object from the json string./
		$registry = ES::makeObject($file);

		// Get the parameter object.
		$form = ES::get('Form');
		$form->load($registry);

		$params = $this->getParams($element, $location);

		// Bind the params to the form.
		$form->bind($params);

		// Get the HTML output.
		return $form->render(true, true, $activeTab);
	}

	/**
	 * Retrieves information about a single file
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getFile($filePath, $element, $contents = false)
	{
		$path = $this->getThemePath($element);
		$filePath = base64_decode($filePath);
		$filePath = $path . $filePath;

		$file = new stdClass();
		$file->element = $element;
		$file->title = str_ireplace($path, '', $filePath);
		$file->absolute = $filePath;
		$file->relative = str_ireplace($path, '', $filePath);
		$file->id = base64_encode($file->relative);

		$file->override = $this->getOverridePath($file->relative);
		$file->modified = JFile::exists($file->override);
		$file->contents = '';

		if ($contents) {
			$location = $file->modified ? $file->override : $file->absolute;
			$file->contents = file_get_contents($location);
		}

		return $file;
	}

	/**
	 * Retrieves a list of files
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getFiles($element)
	{
		$path = $this->getThemePath($element);

		// We should exclude emails since we already have a email template editor
		$exclude = array('.svn', 'CVS', '.DS_Store', '__MACOSX', 'emails', 'images', 'styleguide', 'config');

		// Get a list of folers first
		$folders = JFolder::folders($path, '.', false, true, $exclude);
		$files = array();
		$filter = "^.*\.(php|js)$";

		foreach ($folders as $folder) {

			$group = basename($folder);

			$items = JFolder::files($folder, $filter, true, true, array('', '.svn', 'CVS', '.DS_Store', '__MACOSX', '.less', '.json', '_cache', '_log', 'index.html'));

			if (empty($items)) {
				continue;
			}

			if (!isset($files[$group])) {
				$files[$group] = array();
			}

			foreach ($items as $item) {
				$item = ES::normalizeSeparator($item);

				$item = str_ireplace($path, '', $item);
				$item = base64_encode($item);

				$file = $this->getFile($item, $element);

				$files[$group][] = $file;
			}
		}

		return $files;
	}

	/**
	 * Generates the path to the theme
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getThemePath($element)
	{
		$path = SOCIAL_SITE_THEMES . '/' . $element;
		$path = ES::normalizeSeparator($path);

		return $path;
	}

	/**
	 * Generates the override path for a theme file
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getOverridePath($relativePath)
	{
		$template = $this->getCurrentTemplate();

		$path = JPATH_ROOT . '/templates/' . $template . '/html/com_easysocial/' . ltrim($relativePath, '/');

		return $path;
	}

	/**
	 * Retrieves the current site template
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getCurrentTemplate()
	{
		$db = ES::db();

		$query = 'SELECT ' . $db->nameQuote('template') . ' FROM ' . $db->nameQuote('#__template_styles');
		$query .= ' WHERE ' . $db->nameQuote('home') . '!=' . $db->Quote(0);
		$query .= ' AND ' . $db->qn('client_id') . '=' . $db->Quote(0);

		$db->setQuery($query);

		$template = $db->loadResult();
		return $template;
	}

	/**
	 * Get's a list of themes that is installed on the site.
	 *
	 * @since	1.0
	 * @access	public
	 * @param	string	The location of the theme. (site/admin)
	 * @return	Array	An array of theme files.
	 */
	public function getThemes( $location = 'site' )
	{
		// Get our own config object
		$config 	= ES::config();

		$folder 	= $location == 'admin' ? SOCIAL_ADMIN_THEMES : SOCIAL_SITE_THEMES;

		// Test if the folder really exists first.
		jimport( 'joomla.filesystem.folder' );

		if( !JFolder::exists( $folder ) )
		{
			return false;
		}

		// Get a list of theme folders.
		$result 	= JFolder::folders($folder, '.', false, false, array('.svn', 'CVS', '.DS_Store', '__MACOSX'), array('^\..*', '\.bak'));
		$themes     = array();

		foreach( $result as $theme )
		{
			// Get manifest information of the themes
			$manifest 	= $folder . '/' . $theme . '/config/template.json';

			if( !JFile::exists( $manifest ) )
			{
				continue;
			}

			$obj 	= ES::makeObject( $manifest );

			// Set new states on the object.
			$obj->id 		= $obj->element;
			$obj->default 	= $config->get( 'theme.' . $location ) == strtolower( $obj->element ) ? true : false;
			$themes[]		= $obj;
		}

		return $themes;
	}

	/**
	 * Allows caller to revert an overriden theme file
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function revert($file)
	{
		$exists = JFile::exists($file->override);
		if (!$exists) {
			return false;
		}

		$state = JFile::delete($file->override);

		return $state;
	}

	/**
	 * Allows caller to write contents
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function write($file, $contents)
	{
		$state = JFile::write($file->override, $contents);

		return $state;
	}

	/**
	 * Retrieve the current site css template path
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function getCustomCssTemplatePath()
	{
		$template = $this->getCurrentTemplate();

		$path = JPATH_ROOT . '/templates/' . $template . '/html/com_easysocial/css/custom.css';

		return $path;
	}
}
