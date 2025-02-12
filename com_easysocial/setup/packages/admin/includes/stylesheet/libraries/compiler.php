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

require_once(dirname(__DIR__) . '/compilers/lessc.php');
require_once(__DIR__ . '/task.php');

class SocialStylesheetCompiler extends SocialStylesheetLessc
{

	private $stylesheet;
	private $task;

	protected static $defaultOptions = array(
												'force' => false
											);

	protected static $locations = array(
										// 'user',
										// 'override',
										'site',
										'site_base',
										'admin',
										'admin_base',
										// 'module',
										'media',
										'component',
										'foundry',
										'global'
									);

	protected static $importOrdering = array(
												'site' => array(
													// 'user',
													'site',
													'site_base',
													'component',
													'global'
												),

												'admin' => array(
													// 'user',
													'admin',
													'admin_base',
													'component',
													'global'
												),

												'module' => array(
													// 'user',
													'module',
													'component',
													'global'
												),

												// 'site-override' => array(
												// 	'user',
												// 	'override',
												// 	'site',
												// 	'site_base',
												// 	'component',
												// 	'global'
												// ),

												// 'admin-override' => array(
												// 	// 'user',
												// 	'override',
												// 	'admin',
												// 	'admin_base',
												// 	'component',
												// 	'global'
												// ),

												// 'module-override' => array(
												// 	// 'user',
												// 	'override',
												// 	'module',
												// 	'component',
												// 	'global'
												// ),

												'foundry' => array(
													'global'
												)
											);

	// TODO: Restrict importing of less files within the allowed directories.
	public $allowedDir = array();

	public function __construct($stylesheet)
	{
		$this->stylesheet = $stylesheet;
	}

	public static function importOrdering($location)
	{

		if (isset(self::$importOrdering[$location])) {
			$locations = self::$importOrdering[$location];
		}

		return (empty($locations)) ? array() : $locations;
	}

	public function run($section, $options=array())
	{
		// Create new task
		$this->task = new SocialStylesheetTask("Compiling section '$section'.");

		$task = $this->task;

		// Create log folder
		$logFolder = $this->stylesheet->folder('log');

		if (!JFolder::exists($logFolder)) {
			JFolder::create($logFolder);
		}

		// Write to a log file when this task is completed.
		$task->output = $this->stylesheet->file($section, 'log');

		// Normalize options
		$options = array_merge(self::$defaultOptions, $options);

		// Get current stylesheet location
		$currentLocation = $this->stylesheet->location;

		if ($this->stylesheet->isOverride) {
			$currentLocation .= '-override';
		}

		// Get paths
		$in    = $this->stylesheet->file(array('filename' => $section, 'type' => 'less', 'seek' => true));
		$out   = $this->stylesheet->file($section, 'css');
		$cache = $this->stylesheet->file($section, 'cache');
		$root  = dirname($out);

		// Check if less file exists.
		if (!JFile::exists($in)) {
			return $task->reject("Missing less file '$in'.");
		}

		// Check if folder is writable.
		if (!is_writable($root)) {
			return $task->reject("Unable to write files inside the folder '$root'.");
		}

		// Check if css file is writable.
		if (JFile::exists($out) && !is_writable($out)) {
			return $task->reject("Unable to write css file '$out'.");
		}

		// Prepare cache.
		$cacheBefore = null;

		// Check if cache file is writable.
		if (JFile::exists($cache) && !is_writable($cache)) {
			return $task->reject("Unable to write cache file '$cache'.");
		}

		// If there is an existing cache file,
		if (JFile::exists($cache)) {

			// get contents of cache file.
			$content = file_get_contents($cache);

			if ($content===false) {
				$task->report("Unable to read existing cache file '$cache'.", 'info');
			} else {
				$cacheBefore = json_decode($content, true);
			}
		}

		// Generate location variables
		$variables = array();

// dump(self::$locations);
		foreach (self::$locations as $location) {
			$path = $this->stylesheet->folder($location);
			$variables[$location] = "'" . 'file://' . $path . "'";
			$variables[$location . '_uri'] = "'" . $this->stylesheet->relative($path, $root) . "'";
		}

		// Set variables
		$this->setVariables($variables);

		// Generate import directories
		if (isset(self::$importOrdering[$currentLocation])) {

			$importDir = array();

			foreach (self::$importOrdering[$currentLocation] as $location) {
				$importDir[] = $this->stylesheet->folder($location);
			}

			// Set import directories
			$this->setImportDir($importDir);
		}

		// Compile less stylesheet.
		try {
			$cacheAfter = $this->cachedCompile((empty($cacheBefore) ? $in : $cacheBefore), $options['force']);
		} catch (Exception $exception) {

			$task->report($exception->getMessage(), 'error');
			$task->reject("An error occured while compiling less file.");
			return $task;
		}

		// Stop if compiler did not return an array object.
		if (!is_array($cacheAfter)) {
			return $task->reject("Incompatible less cache structure or invalid input file was provided.");
		}

		// Determine if there are changes in this stylesheet.
		if (empty($cacheBefore) || $cacheAfter['updated'] > $cacheBefore['updated']) {

			// Write stylesheet file.
			$content = $cacheAfter['compiled'];
			if (!JFile::write($out, $content)) {
				return $task->reject("An error occured while writing css file '$out'.");
			}

			// Create cache folder
			$cacheFolder = $this->stylesheet->folder('cache');
			if (!JFolder::exists($cacheFolder)) {
				JFolder::create($cacheFolder);
			}

			// Write cache file.
			$cacheContent = json_encode($cacheAfter);
			if (!JFile::write($cache, $cacheContent)) {
				return $task->reject("An error occured while writing cache file '$cache'.");
			}

			// Delete minified file.
			$minified = $this->stylesheet->file($section, 'minified');
			if (JFile::exists($minified) && !JFile::delete($minified)) {
				$task->report("Unable to remove minified file '$minified'.", 'warn');
			}

		// If there are no changes, skip writing stylesheet & cache file.
		} else {

			$task->report("There are no changes in this section.", 'info');
			return $task->stop();
		}

		return $task->resolve();
	}

	public function makeParser($name) {

		// Thia makes tracing broken less files a lot easier.
		// $this->task->report("Parsing '$name'.", 'info');

		return parent::makeParser($name);
	}

	public function findImport($name) {

		// Adds support for absolute paths
		if (substr($name, 0, 7)=="file://") {
			$full = substr($name, 7);
			// TODO: Restrict importing of less files within the allowed directories.
			if ($this->fileExists($file = $full.'.less') || $this->fileExists($file = $full)) {
				return $file;
			}
		}

		return parent::findImport($name);
	}
}
