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

require_once(__DIR__ . '/analyzer.php');
require_once(__DIR__ . '/task.php');

class SocialStylesheetBuilder 
{
	public $stylesheet = null;

	protected static $defaultOptions = array(

		'compile' => array(
			'enabled' => true,
			'force' => false
		),

		'minify' => array(
			'enabled' => true
		),

		'build' => array(
			'enabled' => true,
			'target' => array(
				'mode' => 'index'
			),
			'minified_target' => array(
				'mode' => 'join'
			)
		)
	);

	protected static $presets = array(

		// Generate index & join minified stylesheets.
		// Ideal when you want to pick up CSS changes on page refresh.
		// Suitable when running under static mode.
		'fast' => array(
			'compile' => array(
				'enabled' => false
			),
			'minify' => array(
				'enabled' => false
			)
		),

		// Cache compile, minify stylesheet, generate index and join minified stylesheet.
		// Ideal when you want to pick up LESS changes on page refresh when loading minified stylesheets.
		// Suitable for testing when running under static/optimized mode.
		'cache' => array(),

		// Cache compile & generate index.
		// Ideal when you want to pick up LESS changes on page refresh.
		// Suitable for development when running under development mode.
		'development' => array(

			'minify' => array(
				'enabled' => false
			),

			'build' => array(
				'minified_target' => array(
					'mode' => 'skip'
				)
			)
		),

		// Full compile, minify stylesheet, generate index and join minified stylesheets.
		// Ideal when packaging theme stylesheets.
		// Suitable for building.
		'full' => array(
			'compile' => array(
				'force' => true
			)
		)
	);

	public function __construct($stylesheet)
	{
		$this->stylesheet = $stylesheet;
	}

	private static function array_merge_recursive_distinct(array &$array1, array &$array2)
	{
		$merged = $array1;

		foreach($array2 as $key => &$value) {
			if(is_array($value) && isset($merged[$key]) && is_array($merged[$key])) {
				$merged[$key] = self::array_merge_recursive_distinct($merged[$key], $value);
			} else {
				$merged[$key] = $value;
			}
		}

		return $merged;
	}

	public function run($preset='cache', $options=array())
	{
		$location = $this->stylesheet->location;
		$name = $this->stylesheet->name;
		$type = $this->stylesheet->type();

		// Create compile task object.
		$this->task = new SocialStylesheetTask("Building $location stylesheet '$name' using '$preset' preset.");
		$task = $this->task;

		// Create log folder
		$logFolder = $this->stylesheet->folder('log');

		if (!JFolder::exists($logFolder)) {
			JFolder::create($logFolder);
		}

		// Write to a log file when this task is completed.
		$task->output = $this->stylesheet->file('log');

		// Normalize options
		$options = self::array_merge_recursive_distinct(self::$presets[$preset], $options);
		$options = self::array_merge_recursive_distinct(self::$defaultOptions, $options);

		// Get manifest file.
		$manifest = $this->stylesheet->manifest();

		foreach ($manifest as $group => $sections) {

			// Compile stylesheet if this is ATS or LESS
			if ($type !== 'css') {

				// If we need to compile,
				$compileOptions = $options['compile'];

				if ($compileOptions['enabled']) {

					// then compile all sections for this group.
					$subtask = $this->compileGroup($group, $compileOptions);
					$task->subtasks[] = $subtask;

					// If failed, stop.
					if ($subtask->failed) {
						$task->reject();
						break;
					}
				}

			// Else verify if the CSS file exists
			} else {

				$cssFile = $this->stylesheet->file('css');

				if (!JFile::exists($cssFile)) {
					return $task->reject("Missing css file '$cssFile'.");
				}
			}

			// If we need to minify,
			$minifyOptions = $options['minify'];

			if ($minifyOptions['enabled']) {

				// then minify all sections for this group.
				$subtask = $this->minifyGroup($group, $minifyOptions);
				$task->subtasks[] = $subtask;

				// If failed, stop.
				if ($subtask->failed) {
					$task->reject();
					break;
				}
			}

			if ($type=='ats') {

				// If we need to build,
				$buildOptions = $options['build'];

				if ($buildOptions['enabled']) {

					// then build this group.
					$subtask = $this->buildGroup($group, $buildOptions);
					$task->subtasks[] = $subtask;

					// If failed, stop.
					if ($subtask->failed) {
						$task->reject();
						break;
					}
				}
			}
		}

		// If any of the task above failed, stop.
		if ($task->failed) {
			return $task;
		}

		if ($type == 'ats') {
			$sections = $this->stylesheet->sections();

			$files = array();
			$cache = array();

			// Collect modified time for every section's css file
			foreach ($sections as $section) {

				$file = $this->stylesheet->file($section, 'css');
				$filename = basename($file);
				$modifiedTime = @filemtime($file);

				// Skip unreadable file
				if ($modifiedTime === false) {
					$task->report("Unable to get modified time for '$file'.");
					continue;
				}

				$files[$filename] = $modifiedTime;
			}

			// Build cache data.
			$cache['files'] = $files;

			// Generate cache file
			$cacheFolder = $this->stylesheet->folder('cache');
			$cacheFile = $this->stylesheet->file('cache');
			$cacheContent = json_encode($cache);

			// Create cache folder if it doesn't exist.
			if (!JFolder::exists($cacheFolder)) {
				JFolder::create($cacheFolder);
			}

			// Write cache file.
			if (!JFile::write($cacheFile, $cacheContent)) {
				$task->report("Unable to write cache file '$cacheFile'.");
			}
		}

		return $task->resolve();
	}

	public function compileGroup($group, $options=array())
	{
		$task = new SocialStylesheetTask("Compiling all sections for group '$group'.");

		// Get manifest
		$manifest = $this->stylesheet->manifest();

		// Stop if group does not exist in stylesheet manifest.
		if (!isset($manifest[$group])) {
			return $task->reject("Group '$group' does not exist in stylesheet manifest.");
		}

		// Get sections
		$sections = $manifest[$group];

		// Stop if there are no sections.
		if (count($sections) < 1) {
			return $task->reject("No available sections to compile.");
		}

		foreach ($sections as $section) {

			// Compile section
			$subtask = $this->stylesheet->compile($section, $options);
			$task->subtasks[] = $subtask;

			// Stop if section could not be compiled.
			if ($subtask->failed) {
				return $task->reject("An error occured while compiling section '$section'.");
			}
		}

		return $task->resolve();
	}

	public function minifyGroup($group, $options=array())
	{
		$task = new SocialStylesheetTask("Minifying all sections for group '$group'.");

		// Get manifest
		$manifest = $this->stylesheet->manifest();

		// Stop if group does not exist in stylesheet manifest.
		if (!isset($manifest[$group])) {
			return $task->reject("Group '$group' does not exist in stylesheet manifest.");
		}

		// Get sections
		$sections = $manifest[$group];

		// Stop if there are no sections.
		if (count($sections) < 1) {
			return $task->reject("No available sections to compile.");
		}

		foreach ($sections as $section) {

			// Compile section
			$subtask = $this->stylesheet->minify($section, $options);
			$task->subtasks[] = $subtask;

			// Stop if section could not be minified.
			if ($subtask->failed) {
				return $task->reject("An error occured while compiling section '$section'.");
			}
		}

		return $task->resolve();
	}

	public function buildGroup($group, $options=array())
	{
		$task = new SocialStylesheetTask("Building group '$group'.");

		// Get manifest
		$manifest = $this->stylesheet->manifest();

		// Stop if group does not exist in stylesheet manifest.
		if (!isset($manifest[$group])) {
			return $task->reject("Group '$group' does not exist in stylesheet manifest.");
		}

		// Get sections
		$sections = $manifest[$group];

		// Stop if there are no sections.
		if (count($sections) < 1) {
			return $task->reject("No available sections to minify.");
		}

		// Write target.
		$type = 'css';
		$mode = $options['target']['mode'];

		$subtask = $this->writeTarget($group, $type, $mode);
		$task->subtasks[] = $subtask;

		// Stop if writing target failed.
		if ($subtask->failed) {
			return $task->reject();
		}

		// Write minified target.
		$type = 'minified';
		$mode = $options['minified_target']['mode'];

		$subtask = $this->writeTarget($group, $type, $mode);
		$task->subtasks[] = $subtask;

		// Stop if writing minified target failed.
		if ($subtask->failed) {
			return $task->reject();
		}

		return $task->resolve();
	}

	public function writeTarget($group, $type, $mode)
	{

		$task = new SocialStylesheetTask("Writing $type target for '$group'.");

		$file = $this->stylesheet->file($group, $type);
		$content = '';

		// Get manifest
		$manifest = $this->stylesheet->manifest();

		// Stop if group does not exist in stylesheet manifest.
		if (!isset($manifest[$group])) {
			return $task->reject("Group '$group' does not exist in stylesheet manifest.");
		}

		// Get sections
		$sections = $manifest[$group];

		// Stop if there are no sections.
		if (count($sections) < 1) {
			return $task->reject("No available sections to write target.");
		}

		switch ($mode) {

			case 'index':
				$subtask = $this->generateIndex($sections, $type);
				$task->subtasks[] = $subtask;

				if ($subtask->failed) {
					return $task->reject();
				}

				$content = $subtask->result;
				break;

			case 'join':
				$subtask = $this->joinFiles($sections, $type);
				$task->subtasks[] = $subtask;

				if ($subtask->failed) {
					return $task->reject();
				}

				$content = $subtask->result;
				break;

			case 'skip':
			default:
				$task->report('Nothing to do.', 'info');
				return $task;
		}

		if (!JFile::write($file, $content)) {
			return $task->reject("Unable to write to file '$file'");
		}

		return $task->resolve();
	}

	public function generateIndex($sections=array(), $type='css')
	{

		$task = new SocialStylesheetTask("Generating index for $type sections.");

		$index = '';
		foreach ($sections as $section) {
			$filename = basename($this->stylesheet->file($section, $type));
			$index .= "@import '$filename';\n";
		}

		$task->result = $index;

		return $task->resolve();
	}

	public function joinFiles($sections=array(), $type='css')
	{
		$task = new SocialStylesheetTask("Joining $type sections.");

		$content = '';

		foreach ($sections as $section) {

			$sectionFile = $this->stylesheet->file($section, $type);

			if (!JFile::exists($sectionFile)) {
				return $task->reject("Missing minified section file '$sectionFile'.");
			}

			$sectionContent = file_get_contents($sectionFile);

			if ($sectionContent===false) {
				return $task->reject("Unable to read minified section file '$sectionFile'.");
			}

			$content .= $sectionContent;
		}

		$task->result = $content;

		return $task->resolve();
	}
}
