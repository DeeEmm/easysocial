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

jimport('joomla.installer.installer');
jimport('joomla.installer.helper');
jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.file');

class SocialInstallerJoomla extends JObject
{
	const RECURSIVE_SEARCH = true;
	const RETRIEVE_FULL_PATH = true;
	const FORCE_COPY = true;

	// Error messages
	const XML_NOT_FOUND = 100;
	const XML_NOT_VALID = 200;
	const XML_NAMESPACE = 'social';

	public $allowed = ['fields', 'apps'];
	public $source = null;
	public $type = null;
	public $parser = null;
	public $path = null;
	public $element = null;
	public $group = null;

	/**
	 * Copies the source to the destination folder.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function upload($source, $destination)
	{
		$state = JFile::copy($source, $destination);

		return $state;
	}

	public function extract($archive)
	{
		$destination = JPath::clean(dirname($archive ) . '/' . uniqid('social_install_'));
		$archive = JPath::clean($archive);

		jimport('joomla.filesystem.archive');

		if (ESArchive::extract($archive, $destination)) {
			return $destination;
		}

		return false;
	}

	public function cleanup($path)
	{
		jimport('joomla.filesystem.folder');

		return JFolder::delete($path);
	}

	public function cleanupSource()
	{
		return $this->cleanup($this->source);
	}

	/**
	 * Performs the installation of applications.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function install()
	{
		// Ensure that the type is valid.
		$type = strtolower($this->type);

		// Include the adapters for this.
		require_once(__DIR__ . '/adapters/' . $type . '.php');

		$className	= 'SocialInstaller' . ucfirst($type);
		$obj = new $className($this);

		return $obj->install();
	}

	/**
	 * Performs the installation of applications.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function discover()
	{
		$type = strtolower($this->type);

		$adapter = dirname( __FILE__ ) . '/adapters/' . $type . '.php';

		if (!JFile::exists($adapter)) {
			return false;
		}

		require_once(__DIR__ . '/adapters/' . $type . '.php' );

		$className = 'SocialInstaller' . ucfirst($type);
		$obj = new $className($this);

		return $obj->discover();
	}

	/**
	 * Copies the manifest file over to the application folder.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function copyManifest( $destination )
	{
		if( JFile::exists( $this->path ) )
		{
			return true;
		}

		return JFile::copy( $this->path , $destination );
	}

	/**
	 * Copies all the contents specified from the manifest file into the app.
	 *
	 * @since	2.0.11
	 * @param	string
	 */
	public function copyContents($destination, $name)
	{
		// Get the folders and files based on the source path of the manifest file.
		$sourceFolders = JFolder::folders($this->source);
		$sourceFiles = JFolder::files($this->source);

		// If the extracted archive contains the same name as the app, most likely the zip was incorrect.
		if (empty($sourceFiles) && count($sourceFolders) == 1 && $sourceFolders[0] == $name) {
			$this->source .= '/' . $name;
		}

		$source = rtrim($this->source, '/');
		$dest = rtrim($destination, '/');

		// We allow language copying since it should reside in the language folder.
		// Copy language file
		$this->copyLanguages($destination);

		// We skip this because the folder is already in the appropriate path
		if ($source == $dest) {
			return false;
		}

		// Copy files
		$this->copyFiles($destination);

		// Copy folders
		$this->copyFolders($destination);

		// Copy SQL files
		$this->copySQL($destination);

		return true;
	}

	/**
	 * Determines if this app is installable by user.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function isInstallable()
	{
		$installable = $this->parser->xpath('installable');

		if (empty($installable)) {
			return false;
		}

		$isInstallable	= (string) $installable[ 0 ];
		$isInstallable	= $isInstallable == 'false' ? false : true;

		return $isInstallable;
	}

	/**
	 * Determines if this app has a widget layout
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function isWidget()
	{
		$items 	= $this->parser->children();

		if( !isset( $items->widget ) )
		{
			return true;
		}

		$widget = (string) $items->widget;

		if( $widget == 'true' )
		{
			return true;
		}

		return false;
	}

	/**
	 * Copies the files over.
	 *
	 * @since	1.0
	 * @access	private
	 */
	private function copyFiles( $destination )
	{
		// Get the files list from the manifest file.
		$files		= $this->parser->xpath( 'files/file' );

		// Only process when there are files.
		if( $files )
		{
			foreach( $files as $file )
			{
				$fileName	= (string) $file;
				$source		= rtrim( $this->source , '/' ) . '/' . $fileName;
				$dest 		= $destination . '/' . $fileName;

				// @TODO: What if the source and destination is the same?
				if( JFile::exists( $source ) )
				{
					$state 		= JFile::copy( $source , $dest );
				}
				else
				{
					// Add error log when the file doesn't exist.
					$this->setError( JText::sprintf( 'The file defined in the manifest file, <strong>%1s</strong> does not exist' , $dest ) );
				}
			}
		}
		return true;
	}

	/**
	 * Copies the folders over.
	 *
	 * @since	1.0
	 * @access	private
	 * @param	string	The destination path.
	 * @return	bool	True on success false otherwise.
	 */
	private function copyFolders( $destination )
	{
		// Get the list of folders defined in the manifest file.
		$folders	= $this->parser->xpath( 'files/folder' );

		if( $folders )
		{
			foreach( $folders as $folder )
			{
				$folderName		= (string) $folder;
				$source 		= rtrim( $this->source , '/' ) . '/' . $folderName;
				$dest 			= $destination . '/' . $folderName;

				// Only copies the folder if the folder exists.
				if( JFolder::exists( $source ) )
				{
					JFolder::copy( $source , $dest , '' , self::FORCE_COPY );
				}
			}
		}

		return true;
	}

	/**
	 * Get the version of the app.
	 *
	 * @since	1.0
	 * @access	public
	 * @param	null
	 * @return	SocialRegistry	The registry object.
	 *
	 * @author	Mark Lee <mark@stackideas.com>
	 */
	public function getVersion()
	{
		$version 	= $this->parser->xpath( 'version' );

		if( !empty( $version ) )
		{
			return (string) $version[ 0 ];
		}

		return false;
	}

	/**
	 * Determines if the app is a core app
	 *
	 * @since	1.0
	 * @access	public
	 * @param	string
	 * @return
	 */
	public function isCore()
	{
		$val 	= $this->parser->xpath( 'core' );

		if( $val )
		{
			$core 	= (string) $val[0];

			$core 	= $core == 'true' ? true : false;

			return $core;
		}

		return false;
	}

	/**
	 * Determines if the app is a system processing app
	 *
	 * @since	1.0
	 * @access	public
	 * @param	string
	 * @return
	 */
	public function isSystem()
	{
		$val 	= $this->parser->xpath( 'system' );

		if( $val )
		{
			$core 	= (string) $val[0];

			$core 	= $core == 'true' ? true : false;

			return $core;
		}

		return false;
	}


	/**
	 * Determines if the app is a unique app
	 *
	 * @since	1.0
	 * @access	public
	 * @param	string
	 * @return
	 */
	public function isUnique()
	{
		$val = $this->parser->xpath( 'unique' );

		if( $val )
		{
			$unique = (string) $val[0];

			$unique = $unique == 'true' ? true : false;

			return $unique;
		}

		return false;
	}

	/**
	 * Get the title of the app.
	 *
	 * @since	1.0
	 * @access	public
	 * @param	null
	 * @return	SocialRegistry	The registry object.
	 *
	 * @author	Mark Lee <mark@stackideas.com>
	 */
	public function getTitle()
	{

		$parts 	= $this->parser->xpath( 'name' );

		if( !empty( $parts ) )
		{
			$title 	= (string) $parts[ 0 ];
		}
		else
		{
			// If title is not provided, we should give it an error.
			$title 	= JText::_( 'COM_EASYSOCIAL_APPS_UNTITLE_APPLICATION' );
		}

		return $title;
	}

	/**
	 * Get the title of the app.
	 *
	 * @since	1.0
	 * @access	public
	 * @param	null
	 * @return	SocialRegistry	The registry object.
	 *
	 * @author	Mark Lee <mark@stackideas.com>
	 */
	public function getAlias()
	{
		$alias 	= $this->parser->xpath( 'alias' );

		if( !$alias )
		{
			// If alias is not provided, use the title.
			$alias 	= JFilterOutput::stringURLSafe( $this->getTitle() );
		}
		else
		{
			$alias 	= (string) $alias[ 0 ];
		}

		return $alias;
	}

	/**
	 * Get the description of the app.
	 *
	 * @since	1.0
	 * @access	public
	 * @param	null
	 * @return	string	A description data.
	 *
	 * @author	Mark Lee <mark@stackideas.com>
	 */
	public function getDescription()
	{
		$desc 	= $this->parser->xpath( 'description' );

		if( !empty( $desc ) )
		{
			return (string) $desc[ 0 ];
		}

		return false;
	}

	/**
	 * Get's a list of servers from the manifest file.
	 *
	 * @since	1.0
	 * @access	public
	 * @param	null
	 * @return	SocialRegistry	The registry object.
	 *
	 * @author	Mark Lee <mark@stackideas.com>
	 */
	public function getParams()
	{
		$items		= $this->parser->children();
		$param 		= ES::get( 'Registry' );

		if( isset( $items->servers ) )
		{
			$servers 	= $items->servers->children();

			if( $servers )
			{
				foreach( $servers as $key => $value )
				{
					$param->set( $key , (string) $value );
				}
			}
		}

		// Check for icon class
		if( isset( $items->icon ) )
		{
			$param->set( 'icon' , (string) $items->icon );
		}

		return $param;
	}

	/**
	 * Processes language files for the app
	 *
	 * @since	2.0.1
	 * @access	public
	 */
	private function copyLanguages($destination)
	{
		// Locate language elements
		$languages = $this->parser->xpath('languages/language');

		if (!$languages) {
			return true;
		}

		foreach ($languages as $language) {
			$file = (string) $language;
			$code = (string) $language['type'];

			$source = rtrim($this->source, '/') . '/' . $language;
			$destination = JPATH_ROOT . '/administrator/language/' . $code . '/' . $language;

			$exists = JFile::exists($source);

			// Check if the source file exists.
			if ($exists) {
				$state = JFile::copy($source, $destination);
			}
		}

		return true;
	}

	/**
	 * Copies and executes SQL queries.
	 *
	 * @since	1.0
	 * @access	private
	 * @param	string	The destination path.
	 * @return	bool	True on success false otherwise.
	 */
	public function copySQL( $destination )
	{
		// Process SQL files here.
		$sql 		= $this->parser->xpath( 'files/sql' );

		if( $sql )
		{
			$db		= JFactory::getDBO();

			foreach( $sql as $sqlFile )
			{
				$sqlFileName 	= (string) $sqlFile;
				$source 		= rtrim( $this->source , '/' ) . '/' . $sqlFileName;
				$dest 			= $destination . '/' . $sqlFileName;

				// Try to read the contents of the sql.
				$contents 		= file_get_contents( $source );
				$queries		= JInstallerHelper::splitSql( $contents );

				// If the sql file is not empty, try to execute the queries in it.
				if( $queries )
				{
					foreach( $queries as $query )
					{
						$query 	= trim( $query );

						if( !empty( $query ) )
						{
							$db->setQuery( $query );
							$db->Query();
						}
					}
				}

				$state 		= JFile::copy( $source , $dest );
			}
		}

		return true;
	}

	/**
	 * Retrieves the apps configuration and store the layouts.
	 *
	 * @since	1.0
	 * @access	public
	 * @param	string
	 * @return
	 */
	public function installViews( &$app )
	{
		$views 	= $this->parser->xpath( 'views' );

		if( !$views )
		{
			return false;
		}

		$views 	= $views[0]->children();

		if( !$views )
		{
			return false;
		}

		// Delete any existing views for this app.
		$app->deleteExistingViews();

		foreach( $views as $view )
		{
			// Get the title of the view.
			$name 		= (string) $view;

			// If type is not specified, we use 'embed' by default.
			$type 		= (string) (isset( $view['type'] ) ? $view['type'] : 'embed' );

			// Get the title and desc of the view.
			$title 			= (string) $view->title;
			$description 	= (string) $view->description;

			$table 			= ES::table( 'AppView' );
			$table->view	= $name;
			$table->type 	= $type;
			$table->app_id 	= $app->id;
			$table->title 	= $title;
			$table->description	= $description;

			$table->store();
		}

		return true;
	}

	/**
	 * Creates a folder if it doesn't exist yet.
	 *
	 * @since	1.0
	 * @access	public
	 * @param	string	The folder's path.
	 * @return	bool	True if success false otherwise.
	 */
	public function createFolder($destination)
	{
		$exists = JFolder::exists($destination);

		if ($exists) {
			$this->setError(JText::sprintf('COM_EASYSOCIAL_INSTALLER_FOLDER_ALREADY_EXISTS_NOT_CREATING_FOLDER', $destination));
			return false;
		}

		$state = JFolder::create($destination);

		return $state;
	}

	/**
	 * Executes required sql queries by the application.
	 *
	 * @since	1.0
	 * @access	public
	 * @param	null
	 * @return	bool	True on success false otherwise.
	 *
	 * @author	Mark Lee <mark@stackideas.com>
	 */
	public function runQueries()
	{
		$sqlFile 	= $this->source . '/install.sql';

		if( JFile::exists( $sqlFile ) )
		{
			$contents 	= file_get_contents( $sqlFile );

			$this->executeQueries( $contents );

			return true;
		}

		return false;
	}

	/**
	 * Main method to execute sql queries
	 *
	 * @since	1.0
	 * @access	public
	 */
	private function executeQueries($sql)
	{
		$db = ES::db();

		$queries = $db->splitSql($sql);

		foreach ($queries as $query) {
			$query = trim($query);

			if (!empty($query)) {
				$db->setQuery($query);
				$db->execute();
			}
		}
	}

	/**
	 * Performs a callback function to specific methods of the app.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function callback($method = 'install', $callbackFile = '')
	{
		if (!$callbackFile) {
			return;
		}

		$exists = JFile::exists($callbackFile);

		if (!$exists) {
			return;
		}

		// Include the installation file.
		require_once( $callbackFile );

		$className 	= 'Social' . ucfirst( $this->group ) . ucfirst( $this->type ) . ucfirst( $this->element ) . 'Installer';

		if( !class_exists( $className ) )
		{
			return;
		}

		$obj 	= new $className();

		if( !method_exists( $obj , $method ) )
		{
			return;
		}

		$output	= $obj->$method();

		return $output;
	}
}
