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

require_once(__DIR__ . '/libraries/compiler.php');
require_once(__DIR__ . '/libraries/minifier.php');
require_once(__DIR__ . '/libraries/builder.php');
require_once(__DIR__ . '/libraries/analyzer.php');

jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.file');
jimport('joomla.filesystem.path');

class SocialStylesheet extends EasySocial
{
	public $ns = 'EASYSOCIAL';
	public $baseurl = null;
	public $environment = 'development';
	public $themeName = '';
	private $class = __CLASS__;

	public $workspace = array(
		'site' => null,
		'site_base'  => null,
		'admin'      => null,
		'admin_base' => null,
		'module'     => null,
		'override'   => null
	);

	public $location;
	public $name;
	public $isOverride = false;
	public $overrideStylesheet;
	static $attached = array();
	static $filetypes = array(
		'ats'     => array('css', 'minified'),
		'less'    => array('less', 'css', 'minified'),
		'css'     => array('css', 'minified'),
		'section' => array('less', 'css', 'minified')
	);

	const FILE_STATUS_NEW       = -1;
	const FILE_STATUS_UNCHANGED = 0;
	const FILE_STATUS_MODIFIED  = 1;
	const FILE_STATUS_MISSING   = 2;
	const FILE_STATUS_REMOVED   = 3;
	const FILE_STATUS_UNKNOWN   = 4;

	public $themename = '';

	public function __construct($location, $theme, $useOverride)
	{
		parent::__construct();

		if (!defined('SOCIAL_COMPONENT_CLI')) {
			$this->environment = $this->config->get('general.environment');
		}

		// Determines if this is an override request based on the location path
		$isOverride = preg_match('/(.*)(-override)$/', $location, $parts);

		// Internally, override is a location.
		if ($useOverride) {
			$location = 'override';
		}

		$this->baseurl = JURI::root(true);

		// If cdn is enabled, we need to update the base url
		$cdn = ES::getCdnUrl();

		if ($cdn) {
			$this->baseurl = $cdn;
		}

		// Enforce theme to be wireframe if the current user is using a mobile device and esmobile is activated
		if (!defined('SOCIAL_COMPONENT_CLI') && $location == 'site' && ES::responsive()->isMobile() && $this->app->getTemplate() == 'esmobile') {
			$theme = 'wireframe';
		}

		$this->themeName = $theme;
		$this->workspace = $this->getDefaultWorkspace();
		$this->workspace = array_merge($this->workspace, array($location => $theme));
		$this->location = $isOverride ? $parts[1] : $location;
		$this->name = $this->workspace[$isOverride ? 'override' : $this->location];
		$this->isOverride = $isOverride;
	}

	/**
	 * Custom method to add stylesheet so that in the event we need to manipulate it, we could
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function addStylesheet($url)
	{
		$this->doc->addStylesheet($url);
	}

	public function getDefaultWorkspace()
	{
		static $defaultWorkspace;

		if (!isset($defaultWorkspace)) {
			$defaultWorkspace = array(
				'site'=> strtolower($this->config->get('theme.site')),
				'site_base' => strtolower($this->config->get('theme.site_base')),
				'admin' => strtolower($this->config->get('theme.admin')),
				'admin_base' => strtolower($this->config->get('theme.admin_base'))
			);
		}

		return $defaultWorkspace;
	}

	/**
	 * Retrieves the path of a given folder type
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function folder($name = 'current')
	{
		static $cache = array();

		if (!isset($cache[$name]) || defined('SOCIAL_COMPONENT_CLI')) {

			$base = $this->location == 'site' ? JPATH_ROOT : JPATH_ADMINISTRATOR;
			$theme = $this->themeName;

			if ($name == 'site_base') {
				$theme = 'wireframe';
			}

			if ($name == 'admin_base') {
				$theme = 'default';
			}

			if ($name == 'site' || $name == 'site_base' || $name == 'admin' || $name == 'admin_base') {
				$folder = $base . '/components/com_easysocial/themes/' . $theme . '/styles';
			}

			if ($name == 'cache') {
				$folder = $base . '/components/com_easysocial/themes/' . $theme . '/styles/_cache';
			}

			if ($name == 'log') {
				$folder = $base . '/components/com_easysocial/themes/' . $theme . '/styles/_log';
			}

			if ($name == 'media') {
				$folder = SOCIAL_MEDIA;
			}

			if ($name == 'component') {
				$folder = SOCIAL_MEDIA . '/styles';
			}

			if ($name == 'foundry' || $name == 'global') {
				$folder = SOCIAL_MEDIA . '/styles/foundry';
			}

			if ($name == 'root') {
				$folder = JPATH_ROOT;
			}

			if ($name == 'current') {
				$folder = $this->folder($this->location);
			}

			$folder = JPATH::clean($folder, '/');
			$cache[$name] = $folder;
		}

		return $cache[$name];
	}

	/**
	 * What the f ?
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function file($filename, $type = null)
	{

		// Default file options
		$defaultOptions = array(
								'location' => $this->location,
								'filename' => 'style',
								'type' => $type,
								'seek' => false
							);

		// Current options
		$options = array();

		// When passing in an object.
		// $this->file(array('location'=>'override', 'type'=>'css'));
		if (is_array($filename)) {
			$options = $filename;

		// When passing in type or filename + type pair.
		// $this->file('css') returns 'path_to_location/style.css'
		// $this->file('photos', 'css') returns 'path_to_location/photos.css'
		} else {
			$numargs = func_num_args();
			if ($numargs===1) $options['type'] = $filename;
			if ($numargs===2) $options['filename'] = $filename;
		}

		// Extract options as variables
		$options = array_merge($defaultOptions, $options);
		extract($options);

		// If we should seek for the file according
		// to the list of import ordering locations.
		if ($seek) {

			// Get list of import ordering locations
			$locations = SocialStylesheetCompiler::importOrdering($this->location . ($this->isOverride ? '-override' : ''));

			// Go through each of the location
			foreach ($locations as $location) {

				$file = $this->file(array(
					'location' => $location,
					'filename' => $filename,
					'type' => $type
				));

				// and return if the file exists
				if (JFile::exists($file)) return $file;
			}

			// If file could not be found, return file from current location.
			$file = $this->file(array(
				'location' => $this->location,
				'filename' => $filename,
				'type' => $type
			));

			return $file;
		}

		// Construct filename without extension
		$folder = $this->folder($location);

		switch ($type) {

			case 'worksheet':
			case 'less':
				$file = "$folder/$filename.less";
				break;

			case 'stylesheet':
			case 'css':
				$file = "$folder/$filename.css";
				break;

			case 'minified':
				$file = "$folder/$filename.min.css";
				break;

			case 'manifest':
			case 'json':
				$file = "$folder/$filename.json";
				break;

			case 'fallback':
				$file = "$folder/$filename.default.css";
				break;

			case 'config':
			case 'xml':
				$file = "$folder/$filename.default.xml";
				break;

			case 'log';
				$folder = $this->folder('log');
				$file = "$folder/$filename.json";
				break;

			case 'cache':
				$folder = $this->folder('cache');
				$file = "$folder/$filename.json";
				break;

			case 'variables':
				$file = "$folder/variables.less";
				break;
		}

		return $file;
	}

	/**
	 * Generates the absolute uri to the file
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function uri($filename, $type=null)
	{
		$path = is_array($filename) ? $this->path($filename) : $this->path($filename, $type);

		$uri = $this->baseurl . '/' . $path;

		return $uri;
	}

	public function cdn($filename, $type=null)
	{
		$path = is_array($filename) ? $this->path($filename) : $this->path($filename, $type);
		$uri = $this->baseurl . '/' . $path;

		return $uri;
	}

	public function path($filename, $type=null)
	{
		$path = is_array($filename) ? $this->file($filename) : $this->file($filename, $type);

		$path = $this->strip_root($path);

		return $path;
	}

	public function strip_root($path='')
	{
		$root = JPATH_ROOT;
		$root_win = str_replace('\\', '/', $root);

		if (strpos($path, $root)===0) {
			$path = substr_replace($path, '', 0, strlen($root));
		} else if (strpos($path, $root_win)===0) {
			$path = substr_replace($path, '', 0, strlen($root_win));
		}

		// For some site's the root path is just / and it's probably caused by chroot
		if ($root === '/') {
			return $path;
		}

		// Strip trailing slash
		return substr($path, 1);
	}

	public function relative($dest, $root='', $dir_sep='/')
	{
		$root = explode($dir_sep, $root);
		$dest = explode($dir_sep, $dest);
		$path = '.';
		$fix = '';
		$diff = 0;

		for ($i = -1; ++$i < max(($rC = count($root)), ($dC = count($dest)));) {

			if (isset($root[$i]) and isset($dest[$i])) {

				if ($diff) {
					$path .= $dir_sep. '..';
					$fix .= $dir_sep. $dest[$i];
					continue;
				}

				if ($root[$i] != $dest[$i]) {
					$diff = 1;
					$path .= $dir_sep. '..';
					$fix .= $dir_sep. $dest[$i];
					continue;
				}

			} elseif (!isset($root[$i]) and isset($dest[$i])) {

				for($j = $i-1; ++$j < $dC;) {
					$fix .= $dir_sep. $dest[$j];
				}
				break;

			} elseif (isset($root[$i]) and !isset($dest[$i])) {

				for($j = $i-1; ++$j < $rC;) {
					$fix = $dir_sep. '..'. $fix;
				}
				break;
			}
		}

		$rel = $path . $fix;
		$rel = (substr($rel, 0, 2)=='./') ? substr($rel, 2) : '';

		return $rel;
	}

	/**
	 * Creates a new compiler instance
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function compiler()
	{
		$compiler = new SocialStylesheetCompiler($this);

		return $compiler;
	}

	public function minifier()
	{
		if (!isset($minifier)) {
			$minifier = new SocialStylesheetMinifier($this);
		}

		return $minifier;
	}

	public function builder()
	{
		if (!isset($builder)) {
			$builder = new SocialStylesheetBuilder($this);
		}

		return $builder;
	}

	/**
	 * Allows caller to compile the less files and combine them into a single style.css file
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function compile($section, $options=array())
	{
		$compiler = $this->compiler();
		$task = $compiler->run($section, $options);
		return $task;
	}

	/**
	 * Minifies the css file
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function minify($section, $options=array())
	{
		$minifier = $this->minifier();
		$task = $minifier->run($section, $options);
		return $task;
	}

	public function build($preset = 'cache', $options=array())
	{
		$builder = $this->builder();
		$task = $builder->run($preset, $options);

		return $task;
	}

	public function type()
	{

		// static $type;

		if (isset($type)) return $type;

		// ATS
		$manifestFile = $this->file('manifest');
		if (JFile::exists($manifestFile)) {
			$type = 'ats';
			return $type;
		}

		// LESS
		$lessFile = $this->file('less');
		if (JFile::exists($lessFile)) {
			$type = 'less';
			return $type;
		}

		// CSS
		$cssFile = $this->file('css');
		if (JFile::exists($cssFile)) {
			$type = 'css';
			return $type;
		}

		// Fallback is always css
		return 'css';
	}

	public function manifest() {

		// static $manifestContent;

		// Manifest content loaded before, just return it.
		if (isset($manifestContent)) return $manifestContent;

		$manifestFile = $this->file('manifest');

		// If manifest file exists,
		if (JFile::exists($manifestFile)) {

			// read manifest file,
			$manifestData = file_get_contents($manifestFile);

			// and parse manifest data.
			$manifestContent = json_decode($manifestData, true);
		}

		// If no manifest file found or manifest could not be parsed, assume simple stylesheet.
		// Simple stylesheet does not contain sections, the bare minimum is a single "style.css" file.
		// If it has a "style.less" file, then this less file is considered the source stylesheet where "style.css" is compiled from, else "style.css" is considered the source stylesheet.
		if (empty($manifestContent) || !is_array($manifestContent)) {
			$manifestContent = array('style' => array('style'));
		}

		return $manifestContent;
	}

	public function sections() {

		// static $sections;

		if (isset($sections)) return $sections;

		// Get manifest
		$manifest = $this->manifest();

		// Merge all sections in a single array
		$sections = array();
		foreach ($manifest as $group => $_sections) {
			$sections = array_merge($sections, $_sections);
		}

		// Remove duplicates
		$sections = array_unique($sections);

		return $sections;
	}

	public function log($section='style')
	{

		// If log file does not exist, stop.
		$logFile = $this->file($section, 'log');
		if (!JFile::exists($logFile)) return false;

		// If log file could not be read, stop.
		$logContent = file_get_contents($logFile);
		if (!$logContent) return false;

		$log = json_decode($logContent, true);

		return $log;
	}

	public function sectionId($section)
	{
		return $this->location . '-' . $this->workspace[$this->location] . '-' . $section;
	}

	public function strip_folder($path, $folders=array())
	{
		// Generate a list of folders to strip
		if (empty($folders)) {

			$locations = SocialStylesheetCompiler::importOrdering($this->location . ($this->isOverride ? '-override' : ''));
			$folders = array();

			foreach ($locations as $location) {
				$folder     = $this->folder($location);
				$folder_win = str_replace('\\', '/', $folder);
				$folders[] = $folder;
				$folders[] = $folder_win;
			}
		}

		$found = false;
		foreach($folders as $folder) {
			if (strpos($path, $folder)===0) {
				$path = substr_replace($path, '', 0, strlen($folder) + 1);
				$found = true;
				break;
			}
		}

		// Fallback if folder was not stripped
		if (!$found) $path = basename($path);

		// Strip extension
		$path = preg_replace("/\\.[^.\\s]{3,4}$/", "", $path);

		return $path;
	}

	public function imports($section)
	{

		// static $imports;
		if (!isset($imports)) $imports = array();
		if (isset($imports[$section])) return $imports[$section];

		// If cache file does not exist, stop.
		$cacheFile = $this->file($section, 'cache');
		if (!JFile::exists($cacheFile)) return false;

		// If unable to read file, stop.
		$cacheContent = file_get_contents($cacheFile);
		if (!$cacheFile) return false;

		// If cache structure is invalid, stop.
		$cache = json_decode($cacheContent, true);
		if (!is_array($cache)) return false;

		$NS = $this->ns . '_';

		$status = array();
		$files  = $cache['files'];

		foreach ($files as $file => $modified) {

			// Properties
			$name   = $this->strip_folder($file);
			$path   = $this->strip_root($file);
			$uri    = constant($NS . 'JOOMLA_URI') . '/' . $path;
			$state  = 'ready';
			$exists = JFile::exists($file);

			// Missing file
			if (!$exists) $state = 'missing';

			// Modified file
			$current = @filemtime($file);
			if ($current > $modified) {
				$state = 'modified';
			}

			$status[] = (object) array(
				'name'     => $name,
				'path'     => $path,
				'uri'      => $uri,
				'current ' => $current,
				'modified' => $modified,
				'state'    => $state
			);
		}

		$imports[$section] = $status;

		return $status;
	}

	public function override()
	{
		if (empty($this->overrideStylesheet)) {
			$this->overrideStylesheet = new $this->class($this->ns, $this->workspace, $this->location . '-override');
		}

		return $this->overrideStylesheet;
	}

	public function overrides()
	{
		// static $overrides;

		if (isset($overrides)) return $overrides;

		// Prepare keywords for path building.
		$NS = $this->ns . '_';
		$administrator = ($this->location=='admin') ? '/administrator/' : '';
		$component = ($this->location=='module') ? $this->workspace['module'] : constant($NS . 'COMPONENT_NAME');

		// Determine path for Joomla template folder because frontend and backend is different.
		$templateFolder = constant($NS . 'JOOMLA') . "$administrator/templates";

		// Get a list of template folders.
		$templates = JFolder::folders($templateFolder);

		// Go through each template folder to see if there is a stylesheet override.
		$overrides = array();
		foreach ($templates as $template) {

			$overrideFolder = "$templateFolder/$template/html/$component/styles";

			// If override folder exists, add to override list.
			if (JFolder::exists($overrideFolder)) {
				$overrides[] = $template;
			}
		}

		return $overrides;
	}

	public function hasOverride()
	{

		if ($this->isOverride) return false;

		$overrideFile = $this->file(array('location' => 'override', 'type' => 'css'));
		$hasOverride = JFile::exists($overrideFile);

		return $hasOverride;
	}

	public function modules()
	{
		// static $modules;

		if (isset($modules)) return $modules;

		// Prepare keywords for path building.
		$NS = $this->ns . '_';
		$modulePath = constant($NS . 'JOOMLA_MODULES');
		$modulePrefix = 'mod_' . constant($NS . 'IDENTIFIER');

		// Get a list of modules that starts with the component identifier,
		// e.g. mod_easysocial, mod_easyblog, mod_easydiscuss.
		$_modules = JFolder::folders($modulePath, $modulePrefix);

		// Go through each module folder and see if there is a styles folder
		$modules = array();
		foreach ($_modules as $module) {

			// If styles folder exists, add to module list.
			if (JFolder::exists("$modulePath/$module/styles")) {
				$modules[] = $module;
			}
		}

		return $modules;
	}

	/**
	 * Attaches stylesheet on the site.
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function attach($minified = true, $allowOverride = true)
	{
		static $loaded = null;

		if ($loaded === true) {
			return true;
		}

		$loaded = true;
		$build = false;

		// If we're in a development environment, always cache compile stylesheet and
		// attached uncompressed stylesheets.
		if ($this->environment == 'development') {
			$build = true;
			$minified = false;
		}

		// We should only attach scrips on html documents otherwise JDocument would hit an error
		if ($this->doc->getType() != 'html') {
			return;
		}

		// Rebuild stylesheet on page load if necessary
		if ($build) {
			$task = $this->build($this->environment);

			// This generates build log in the browser console.
			if ($this->environment == 'development') {

				$errors = array();

				if ($task->state == 'error') {
					foreach ($task->subtasks as $subtask) {
						foreach ($subtask->subtasks as $innerSubtask) {
							if ($innerSubtask->state == 'error') {
								$errors[] = $innerSubtask;
							}
						}
					}
				}

				$script = ES::script();
				$script->set('errors', $errors);
				$script->attach('themes:/admin/stylesheet/log');
			}
		}

		// Determines if the viewer is viewing the admin section.
		$isAdmin = ES::isFromAdmin();

		if ($isAdmin) {
			$allowOverride = false;

			$this->attachFontawesome();

			$this->process($minified, $allowOverride);

			return;
		}

		$direction = $this->doc->getDirection();

		// Attach font awesome
		if ($this->config->get('styles.fontawesome')) {
			$this->attachFontawesome();
		}

		// Attach mmenu css
		if (ES::responsive()->isMobile() || ES::responsive()->isTablet()) {
			$this->attachMobileMenu();
		}

		// For rtl, we need to handle this manually
		if ($direction == 'rtl') {
			$rtlStylesheet = $this->baseurl . '/components/com_easysocial/themes/' . $this->themeName . '/styles/style-rtl';

			if ($this->environment != 'development') {
				$rtlStylesheet .= '.min';
			}

			$rtlStylesheet .= '.css';

			$this->addStylesheet($rtlStylesheet);
		} else {
			$this->process($minified, $allowOverride);
		}


		// Site location
		if ($this->location == 'site') {

			// Check if custom.css exists on the site as template overrides
			$file = JPATH_ROOT . '/templates/' . $this->app->getTemplate() . '/html/com_easysocial/css/custom.css';
			$exists = JFile::exists($file);

			if ($exists) {
				$customCssFile = $this->baseurl . '/templates/' . $this->app->getTemplate() . '/html/com_easysocial/css/custom.css';

				$this->addStylesheet($customCssFile);
			}

			// Add custom css codes
			$theme = ES::themes();
			$output = $theme->output('site/structure/custom.css');

			$this->doc->addCustomTag($output);
		}
	}

	/**
	 * Renders font awesome stylesheets
	 *
	 * @since	3.2.19
	 * @access	public
	 */
	private function attachFontawesome()
	{
		static $loaded = null;

		if (is_null($loaded)) {
			$fontawesome = $this->baseurl . '/media/com_easysocial/fonts/font-awesome/css/all.min.css';

			$this->addStylesheet($fontawesome);

			$loaded = true;
		}

		return $loaded;
	}

	/**
	 * Renders stylesheets for mmenu
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	private function attachMobileMenu()
	{
		if (!defined('SI_MMENU')) {
			$css = $this->baseurl . '/media/com_easysocial/css/mmenu.css';

			$this->addStylesheet($css);

			define('SI_MMENU', true);

			return false;
		}

		return true;
	}

	public function process($minified=true, $allowOverride=true, $attachStyle = true)
	{
		$document = JFactory::getDocument();
		$isAdmin = ES::isFromAdmin();

		// Load manifest file.
		$manifest = $this->manifest();

		$uris = array();

		foreach ($manifest as $group => $sections) {

			// Determine the type of stylesheet to attach
			$type = $minified ? 'minified' : 'css';

			// Build path options
			$target = array(
				'location' => $this->isOverride ? 'override' : $this->location,
				'filename' => $group,
				'type' => $type
			);

			// Fallback to css if minified not exists,
			// only for template overrides because
			// we don't want too much disk i/o.
			if ($this->isOverride && $minified) {

				$minifiedFile = $this->file($target);

				if (!JFile::exists($minifiedFile)) {
					$target['type'] = 'css';
				}
			}

			// Do not attach CDN uri for backend
			if ($isAdmin) {
				$uri = $this->uri($target);
			} else {
				$uri = $this->cdn($target);
			}

			$uris[] = $uri;

			// Ensure that it hasn't been attached before
			if (isset(self::$attached[$uri])) {
				return;
			}

			// Attach version to the css
			$uri .= '?' . md5(ES::getLocalVersion()) . '=1';

			if ($attachStyle) {
				$this->addStylesheet($uri);
			}

			self::$attached[$uri] = true;
		}

		return $uris;
	}

	public function status($section='style')
	{
		// static $status;
		if (!isset($status)) $status = array();
		if (isset($status[$section])) return $status[$section];

		// Stylesheet
		if ($section=='style') {
			$manifest  = $this->manifest();
			$filenames = array_keys($manifest);
			$type      = $this->type();

		// Section
		} else {
			$filenames = array($section);
			$type      = 'section';
		}

		$files = array();
		$filetypes = self::$filetypes[$type];

		foreach ($filenames as $filename) {

			foreach ($filetypes as $filetype) {

				// Options to get file path
				$options = array(
					'filename' => $filename,
					'type'     => $filetype,
					'seek'     => $type=='section' && $filetype=='less'
				);

				// Get properties of this file
				$file     = $this->file($options);
				$uri      = $this->uri($options);
				$path     = $this->path($options);
				$exists   = JFile::exists($file);
				$size     = null;
				$modified = null;
				$rules    = 0;
				$state    = 'unknown';

				if ($exists) {
					$size     = @filesize($file);
					$modified = @filemtime($file);
					$state    = 'ready';

					if ($filetype=='css' || $filetype=='minified') {
						$content  = file_get_contents($file);
						$rules = SocialStylesheetAnalyzer::rules($content);
						$rules = count($rules);
					}
				} else {
					$state   = 'missing';
				}

				// Determine file status
				// TODO: Merge "changes()" method within this method so we can track new/missing/modified files.

				$files[] = (object) array(
					'name'     => basename($file),
					'path'     => $path,
					'uri'      => $uri,
					'exists'   => $exists,
					'size'     => $size,
					'modified' => $modified,
					'state'    => $state,
					'rules'    => $rules
				);
			}
		}

		$status[$section] = $files;

		return $files;
	}

	public function changes($fast=false)
	{
		// static $result;

		$key = (string) $fast;

		if (!isset($result)) $result = array();

		// Return cached result if possible
		if (isset($result[$key])) return $result[$key];

		$task = new SocialStylesheetTask('Detect changes in stylesheets');

		$cacheFile = $this->stylesheet->file('cache');
		$cache = null;

		if (!JFile::exists($cacheFile)) {

			$cacheData = file_get_contents($cacheFile);

			if (!$cacheData) {
				$task->report("No cache file found at '$cacheFile'.", SocialStylesheetTask::MESSAGE_INFO);
				return $task->reject();
			}

			$cache = json_decode($cacheData);
		}

		if (!is_array($cache)) {
			$task->report("Incompatible style cache structure or invalid cache file was provided at '$cacheFile'.");
			return $task->reject();
		}

		// Result dataset
		$changes  = array();
		$status   = array();
		$modified = false;

		// Get cache to detect missing or modified file.
		$files = $cache->files;

		// Get sections to detect new or deleted files.
		$sections = $this->sections();

		foreach ($sections as $section) {

			$filename = $section . '.css';

			if (!isset($files[$filename])) {
				$files[$filename] = null;
			}
		}

		// Go through each file to look for changes
		foreach ($files as $filename => $timestamp) {

			// For fast change detection. Used by hasChanges().
			if ($fast && $modified) break;

			// Get file path
			$file = $this->file($this->filename, 'css');
			$state = self::FILE_STATUS_UNCHANGED;

			// If the file does not exist anymore
			if (!JFile::exists($file)) {

				// If the file still exist in the manifest,
				// then this file is missing.
				if (isset($sections[$filename])) {
					$task->report("Missing file '$file'.");
					$state = self::FILE_STATUS_MISSING;

				// Else this file has been removed.
				} else {
					$task->report("Removed file '$file'.");
					$$state = self::FILE_STATUS_REMOVED;
				}

			} else {

				// Retrieve file's modified time
				$modifiedTime = @filemtime($file);

				// Skip and generate a warning if unable to retrieve timestamp
				if ($modifiedTime===false) {
					$task->report("Unknown modified time for '$file'.");
					$$state = self::FILE_STATUS_UNKNOWN;
				}

				// File is new
				if (is_null($timestamp)) {
					$task->report("New file found '$file'.");
					$state = self::FILE_STATUS_NEW;

				// File is modified
				} elseif ($timestamp < $modifiedTime) {
					$task->report("Modified file found '$file'.");
					$state = self::FILE_STATUS_MODIFIED;
				}
			}

			// Add to change list
			$changes[$file] = $state;

			// Increase state count
			if (isset($status[$state])) {
				$status[$state] = 0;
			}
			$status[$state]++;

			// Flag to indicate this stylesheet is modified
			if (!$modified && $state!==0) {
				$modified = true;
			}
		}

		// If there are no changes in this stylesheet, report it.
		if (!$modified) {
			$task->report('There are no changes in this stylesheet.');
		}

		$task->result = (object) array(
			'status'   => $status,
			'changes'  => $changes,
			'modified' => $modified
		);

		$result[$key] = $result;

		return $task->resolve();
	}

	public function hasChanges() {

		$task = $this->changes(true);

		// Unable to detect changes
		if ($task->failed) return null;

		return $task->result->modified;
	}

	public function purge() {

		// Create compile task object.
		$task = new SocialStylesheetTask("Purging stylesheet cache & log files.");

		$cacheFolder = $this->folder('cache');

		if (JFolder::exists($cacheFolder)) {

			if (JFolder::delete($cacheFolder)) {
				$task->report("Deleted cache folder '$cacheFolder'.");
			} else {
				$task->report("Unable to delete cache folder '$cacheFolder'.");
			}
		}

		$logFolder = $this->folder('log');

		if (JFolder::exists($logFolder)) {

			if (JFolder::delete($logFolder)) {
				$task->report("Deleted log folder '$logFolder'.");
			} else {
				$task->report("Unable to delete log folder '$logFolder'.");
			}
		}

		return $task->resolve();
	}
}
