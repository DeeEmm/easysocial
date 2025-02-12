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

class SocialStylesheetTask
{

	const MESSAGE_SUCCESS = 'success';
	const MESSAGE_ERROR   = 'error';
	const MESSAGE_INFO    = 'info';
	const MESSAGE_WARN    = 'warn';

	const STATE_SUCCESS = 'success';
	const STATE_ERROR   = 'error';
	const STATE_PENDING = 'pending';

	// Task summary
	public $state;
	public $message = '';
	public $details = array();
	public $failed = false;
	public $subtasks = array();
	public $result;

	// Task profiling
	public $time_start;
	public $time_end;
	public $time_total;
	public $mem_start;
	public $mem_end;
	public $mem_peak;

	// Task reporting
	static $key = 0;
	public $output = null;

	public function __construct($message='', $type=self::MESSAGE_INFO) {

		$this->state = self::STATE_PENDING;

		if (!empty($message)) {
			$this->message = $this->strip_root($message);
			$this->report($message, $type);
		}

		$this->start();
	}

	public function start() {

		$this->time_start = microtime(true);
		$this->mem_start  = memory_get_usage();

		return $this;
	}

	public function stop() {

		$this->time_end   = microtime(true);
		$this->time_total = $this->time_end - $this->time_start;
		$this->mem_end    = memory_get_usage();
		$this->mem_peak   = memory_get_peak_usage();

		return $this;
	}

	public function resolve($message='', $type=self::MESSAGE_SUCCESS) {

		$this->state   = self::STATE_SUCCESS;
		$this->failed  = false;

		if (!empty($message)) {
			$this->message = $this->strip_root($message);
			$this->report($message, $type);
		}

		$this->stop();
		$this->save();

		return $this;
	}

	public function reject($message='', $type=self::MESSAGE_ERROR) {

		$this->state   = self::STATE_ERROR;
		$this->failed  = true;

		if (!empty($message)) {
			$this->message = $this->strip_root($message);
			$this->report($message, $type);
		}

		$this->stop();
		$this->save();

		return $this;
	}

	public function report($message='', $type=self::MESSAGE_WARN)
	{
		// Strip site root path
		$message = str_ireplace(JPATH_ROOT . '/', '', $message);

		$timestamp = microtime(true);

		$detail = (object) array(
			'timestamp' => $timestamp,
			'message'   => $message,
			'type'      => $type
		);

		$key = self::$key++;
		$this->details[$key] = $detail;

		return $detail;
	}

	private function strip_root($path)
	{
		$root = JPATH_ROOT . '/';
		$root_win = str_replace('\\', '/', $root);

		if (strpos($path, $root)===0) {
			$path = substr_replace($path, '', 0, strlen($root));
		} else if (strpos($path, $root_win)===0) {
			$path = substr_replace($path, '', 0, strlen($root_win));
		}

		return $path;
	}

	public function toArray() {

		$task = array();
		$details = array();

		$props = array(
			'state',
			'message',
			'failed',
			'time_start',
			'time_end',
			'time_total',
			'mem_start',
			'mem_end',
			'mem_peak'
		);

		foreach($props as $prop) {
			$task[$prop] = $this->$prop;
		}

		foreach ($this->details as $timestamp => $detail) {
			$details[$timestamp] = $detail;
		}

		foreach($this->subtasks as $subtask) {

			$subtask = $subtask->toArray();
			$task['subtasks'][] = $subtask;

			foreach ($subtask['details'] as $timestamp => $detail) {
				$details[$timestamp] = $detail;
			}
		}

		// Sort log by key
		ksort($details);

		$task['details'] = $details;

		return $task;
	}

	public function toJSON() {

		return json_encode($this->toArray());
	}

	public function save($output=null) {

		// If no output path given
		if (is_null($output)) {

			// Get output path from instance property
			if (is_null($this->output)) return false;
			$output = $this->output;
		}

		// Export log content
		$content = $this->toJSON();

		return JFile::write($output, $content);
	}
}
