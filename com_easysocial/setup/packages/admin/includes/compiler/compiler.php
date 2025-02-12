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

class SocialCompiler
{
	static $instance = null;
	public $version;
	public $cli = false;

	// These script files should be rendered externally and not compiled together
	// Because they are either too large or only used in very minimal locations.
	public $excludePatterns = [
		"moment",

		// Sharer.js used for sharing
		"sharer",

		// Site
		"site/vendors/videojs",
		"site/vendors/videojs.js",
		"site/vendors/prism.js",
		"site/vendors/masonry.js",

		// Admin
		"admin/vendors/flot.js",

		"vendors/swiper.js",
		"vendors/pressure.js", // Used for reactions
		"vendors/mobile-events.js", // Used for events when reacting
		"vendors/chosen.js",
		"vendors/imgareaselect.js",
		"vendors/jquery.js",
		"vendors/easing.js",
		"vendors/wavesurfer.js", // Used for audio waves only
		"vendors/eventsource.js", // SSE compatibility layer
		"vendors/leaflet.js", // Alternative maps
		"vendors/leaflet-providers.js", // Alternative maps
		"vendors/mmenu.js", // Mobile menu for the front-end only
		"vendors/moment.js", // Date
		"vendors/select2.js", // Dropdown
		"vendors/sly.js", // Mobile navigation
		"vendors/mentions.js", // Mentions used in forms
		"vendors/plupload.js", // Uploader should only be rendered when needed to
		"vendors/markitup.js", // Only used in discussions
		"vendors/datetimepicker.js",

		"vendors/toast.js",
		"vendors/gmaps.js",

		//jquery.ui
		"vendors/ui/core.js",
		"vendors/ui/mouse.js",
		"vendors/ui/datepicker.js",
		"vendors/ui/timepicker.js",
		"vendors/ui/resizable.js",
		"vendors/ui/sortable.js",
		"vendors/ui/slider.js",
		"vendors/ui/widget.js",
		"vendors/ui/draggable.js",
		"vendors/ui/droppable.js"
	];

	public $excluded = []; // Excluded files
	public $excludedMinify = []; // Excluded files that should be minified

	/**
	 * Allows caller to compile a script file on the site, given the section
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function compile($section = 'admin', $minify = true, $jquery = true)
	{
		// Get the file name that should be used after compiling the scripts
		$fileName = ES::scripts()->getFileName($section, $jquery);

		$files = $this->getFiles($section, $jquery);

		$contents = '';

		$contents .= $this->compileBootloader();

		// 1. Core file contents needs to be placed at the top
		$contents .= $this->compileCoreFiles($files->core);

		// 2. Libraries should be appended next
		$contents .= $this->compileLibraries($files->libraries);

		// 3. Compile the normal scripts
		$contents .= $this->compileScripts($files->scripts);

		$result = new stdClass();
		$result->section = $section;
		$result->minify = $minify;

		// Store the uncompressed version
		$standardPath = SOCIAL_SCRIPTS . '/' . $fileName . '.js';
		$this->write($standardPath, $contents);

		$result->standard = $standardPath;
		$result->minified = false;

		// Compress the script and minify it
		if ($minify) {
			$closure = $this->getClosure();

			// 1. Minify the main library
			$contents = $closure->minify($contents);

			// Store the minified version
			$minifiedPath = SOCIAL_SCRIPTS . '/' . $fileName . '.min.js';
			$this->write($minifiedPath, $contents);

			// 2. Since excluded files are running on their own, we would need to minify them so that it
			// runs on the compressed version rather than the uncompressed version
			$excludedFiles = $this->getExcludedFilesToMinify();

			foreach ($excludedFiles as $excludedFile) {
				$targetPath = str_ireplace('.js', '.min.js', $excludedFile);
				$excludedContents = file_get_contents($excludedFile);

				$excludedContents = $closure->minify($excludedContents);

				$this->write($targetPath, $excludedContents);
			}

			$result->minified = $minifiedPath;
		}

		return $result;
	}

	/**
	 * Retrieves a list of excluded script files that needs to be minified
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getExcludedFilesToMinify()
	{
		$files = [];

		foreach ($this->excludedMinify as $excluded) {

			// As there are too many momentjs language files, it's not ideal to recompile this on the fly.
			// Since these files hardly change, we just store these minified versions in the repository.
			if ($this->isMomentLanguageFile($excluded)) {
				continue;
			}

			// Dependency files should not need to be minified as they are already included into the core
			if ($this->isDependencyFile($excluded)) {
				continue;
			}

			$files[] = $excluded;
		}

		return $files;
	}

	/**
	 * Determines if the file pattern is a moment language file
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function isMomentLanguageFile($file)
	{
		if (stristr($file, SOCIAL_SCRIPTS . '/vendors/moment/') !== false) {
			return true;
		}

		return false;
	}

	/**
	 * Determines if the file pattern is a dependency file
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function isDependencyFile($file)
	{
		$dependencies = ES::scripts()->getDependencies();

		$pattern = implode('|', array_map('preg_quote', $dependencies));
		$pattern = str_ireplace('/', '\/', $pattern);
		$pattern = '/' . $pattern . '/i';

		if (preg_match($pattern, $file)) {
			return true;
		}

		return false;
	}

	/**
	 * Compiles core files
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function compileCoreFiles($files)
	{
		$contents = '';

		foreach ($files as $file) {
			$contents .= file_get_contents($file);
		}

		return $contents;
	}

	/**
	 * Retrieves contents from the bootloader file
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function compileBootloader()
	{
		$file = JPATH_ROOT . '/media/com_easysocial/scripts/bootloader.js';

		$contents = file_get_contents($file);

		return $contents;
	}

	/**
	 * Compiles all libraries
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function compileLibraries($files)
	{
		$modules = array();

		// Get the prefix so that we can get the proper namespace
		$prefix = SOCIAL_SCRIPTS . '/vendors';

		foreach ($files as $file) {
			$fileName = ltrim(str_ireplace($prefix, '', $file), '/');
			$modules[] = str_ireplace('.js', '', $fileName);
		}

		$modules = json_encode($modules);

ob_start();
?>
FD40.plugin("static", function($) {
	$.module(<?php echo $modules;?>);

	// Now we need to retrieve the contents of each files
	<?php foreach ($files as $file) { ?>
		<?php echo $this->getContents($file); ?>
	<?php } ?>
});
<?php
$contents = ob_get_contents();
ob_end_clean();

		return $contents;
	}

	/**
	 * Compiles script files
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function compileScripts($files)
	{
		$modules = array();

		foreach ($files as $file) {
			$namespace = str_ireplace(SOCIAL_SCRIPTS, 'easysocial', $file);

			$modules[] = str_ireplace('.js', '', $namespace);
		}

		$modules = json_encode($modules);
ob_start();
?>
// Prepare the script definitions
FD40.installer('EasySocial', 'definitions', function($) {
	$.module(<?php echo $modules;?>);
});

// Prepare the contents of all the scripts
FD40.installer('EasySocial', 'scripts', function($) {
	<?php foreach ($files as $file) { ?>
		<?php echo $this->getContents($file); ?>
	<?php } ?>
});
<?php
$contents = ob_get_contents();
ob_end_clean();

		return $contents;
	}


	/**
	 * Only creates this instance once
	 *
	 * @since	2.0
	 * @access	public
	 */
	public static function getInstance()
	{
		if (is_null(self::$instance)) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Retrieves the contents of a particular file
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getContents($file)
	{
		$contents = file_get_contents($file);

		return $contents;
	}

	/**
	 * Retrieves the closure compiler
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getClosure()
	{
		require_once(__DIR__ . '/closure.php');
		$closure = new SocialCompilerClosure();

		return $closure;
	}

	/**
	 * Retrieves a list of files for specific sections
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getFiles($section, $jquery = true)
	{
		$files = new stdClass();

		// Get a list of core files
		$coreFiles = ES::scripts()->getDependencies(true, $jquery);
		$files->core = $coreFiles;

		// Get a list of libraries
		$files->libraries = $this->getLibraryFiles();

		// Get a list of shared scripts that is used across sections
		$scriptFiles = array();
		$scriptFiles = array_merge($scriptFiles, $this->getSharedFiles());

		// Get script files from the particular section
		$scriptFiles = array_merge($scriptFiles, $this->getScriptFiles($section));

		$files->scripts = $scriptFiles;

		return $files;
	}

	/**
	 * Retrieves a list of library files used on the site
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getLibraryFiles()
	{
		$path = SOCIAL_SCRIPTS . '/vendors';
		$files = JFolder::files($path, '.js$', true, true);
		$files = $this->reduceFiles($files, []);

		// $this->debug($files);

		return $files;
	}

	/**
	 * Retrieves list of shared files that is used across all sections
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getSharedFiles()
	{
		// Retrieve core dependencies
		$dependencies = ES::scripts()->getDependencies();

		$folderExclusion = array('.git', '.svn', 'CVS', '.DS_Store', '__MACOSX', 'admin', 'site', 'unused', 'vendors');
		$folders = JFolder::folders(SOCIAL_SCRIPTS, '.', false, true, $folderExclusion);

		$files = [];

		foreach ($folders as $folder) {
			$files = array_merge($files, JFolder::files($folder, '.js$', true, true));
		}

		$exclusions = [
			'apps/fields/user',
			'apps/fields/event',
			'apps/fields/group',
			'apps/fields/page',
			'apps/fields/marketplace',

			// Custom fields related library only get rendered on registration / creation page
			'shared/fields/conditional.js',
			'shared/fields/validate.js',
			'shared/fields/base.js',

			'shared/sidebarmenu.js', // Seems to be used for category navigation only

			'uploader'
		];

		$files = $this->reduceFiles($files, $exclusions);

		// $this->debug($files);

		return $files;
	}

	/**
	 * Retrieves list of scripts that is only used in the particular section
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getScriptFiles($section)
	{
		$path = SOCIAL_SCRIPTS . '/' . $section;
		$files = JFolder::files($path, '.js$', true, true);

		$exclusions = [
			// Admin
			'admin/vendors/fontawesome-iconpicker.js',
			'admin/workflows/form.js',
			'admin/vendors/raty.js', // Used on the app store only
			'admin/migrators/migrator.js', // Migrator section

			// Workflows
			'admin/workflows/choices.js',
			'admin/users/privacy.js',

			// Flot.js
			'admin/vendors/drag.js',
			'admin/vendors/resize.js',
			'admin/vendors/mousewheel.js',

			// Site

			// Only needed when story form is rendered
			'site/story',
			'site/conversations/conversations.js', // Used in conversations page only
			'site/explorer/explorer.js', // Used in file explorers
			'site/vendors/jquery.raty.js', // Used in review apps only
			'site/manage/clusters.js'
		];

		$files = $this->reduceFiles($files, $exclusions, true, true);

		// $this->debug($files);

		return $files;
	}

	/**
	 * Given a list of files, remove exclusions
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function reduceFiles($files, $exclusions, $minifyExcludedFiles = true, $debug = false)
	{
		// Exclude dependencies
		$dependencies = ES::scripts()->getDependencies();
		$exclusions = array_merge($this->excludePatterns, $exclusions, $dependencies);

		// Add exclusion files
		foreach ($exclusions as $exclusion) {
			// Excluded files may also contain a .min.js
			$exclusions[] = str_ireplace('.js', '.min.js', $exclusion);
		}

		$files = array_filter($files, function($file) use (&$exclusions, &$minifyExcludedFiles) {
			$pattern = implode('|', array_map('preg_quote', $exclusions));
			$pattern = str_ireplace('/', '\/', $pattern);
			$pattern = '/' . $pattern . '/i';

			if (preg_match($pattern, $file)) {
				// We don't want to generate duplicate excluded files since excluded files will need to be minified as well
				if (stristr($file, '.min.js') === false) {
					$this->excluded[] = $file;

					if ($minifyExcludedFiles) {
						$this->excludedMinify[] = $file;
					}
				}

				return false;
			}

			return true;
		});

		return $files;
	}

	/**
	 * Saves the contents into a file
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function write($path, $contents)
	{
		if (JFile::exists($path)) {
			JFile::delete($path);
		}

		return JFile::write($path, $contents);
	}

	/**
	 * For debugging purposes only. @dump does not display everything
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function debug($items)
	{
		$debug = [];
		$totalSize = 0;

		foreach ($items as &$item) {
			$file = new stdClass();
			$file->file = $item;

			$size = round(filesize($item) / 1024);

			if ($size > 50) {
				$file->size = "<span style='background:red;color: #fff;'>" . $size . "kb</span>";
			} else {
				$file->size = $size . 'kb';
			}

			$debug[] = $file;

			$totalSize += $size;
		}

		echo "Total Size: " . $totalSize . 'kb';

		usort($debug, function($a, $b) {
			$sizeA = str_ireplace('kb', '', $a->size);
			$sizeB = str_ireplace('kb', '', $b->size);
			return $sizeA < $sizeB;
		});

		echo '<pre>';
		print_r($debug);
		echo '</pre>';
		exit;
	}
}
