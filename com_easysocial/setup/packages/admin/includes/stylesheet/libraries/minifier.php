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

require_once(dirname(__DIR__) . '/compilers/cssmin.php');
require_once(__DIR__ . '/task.php');

class SocialStylesheetMinifier extends FD40_CSSMin
{
	private $stylesheet;

	public function __construct($stylesheet)
	{
		$this->stylesheet = $stylesheet;
	}

	public function run($section, $options=array())
	{
		// Create new task
		$this->task = new SocialStylesheetTask("Minifying section '$section'.");
		$task = $this->task;

		// Get paths
		$in   = $this->stylesheet->file($section, 'css');
		$out  = $this->stylesheet->file($section, 'minified');
		$root = dirname($out);

		// Check if css file exists.
		if (!JFile::exists($in)) {
			return $task->reject("Missing css file '$in'.");
		}

		// Check if folder is writable.
		if (!is_writable($root)) {
			return $task->reject("Unable to write files inside the folder '$root'.");
		}

		// Check if css file is writable.
		if (JFile::exists($out) && !is_writable($out)) {
			return $task->reject("Unable to write css file '$out'.");
		}

		$content = file_get_contents($in);

		if ($content===false) {
			return $task->reject("Unable to read css file '$in'.");
		}

		$minifiedContent = null;

		try {
			$minifiedContent = $this->compress($content);
		} catch (Exception $exception) {
			$task->reject("An error occured while minifying section '$section'.");
			$task->report($exception->getMessage(), 'error');
			return $task;
		}

		if (!JFile::write($out, $minifiedContent)) {
			return $task->reject("An error occured while writing minified file '$out'.");
		}

		return $task->resolve();
	}
}
