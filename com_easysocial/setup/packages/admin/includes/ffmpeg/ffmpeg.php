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

require_once(__DIR__ . '/adapter.php');

class SocialFFmpeg extends EasySocial
{
	static $instance = null;
	private $adapter = null;

	public function __construct($type = SOCIAL_TYPE_VIDEO)
	{
		parent::__construct();

		$path = $this->config->get('video.ffmpeg');

		if ($type == SOCIAL_TYPE_AUDIO) {
			$path = $this->config->get('audio.encoder');
		}

		$this->adapter = new FFMpeg($path);
	}

	public function __get($property)
	{
		return $this->adapter->$property;
	}

	public function __call($method, $args)
	{
		return call_user_func_array(array($this->adapter, $method), $args);
	}

	public function convert()
	{
	}

	public function getThumbnail()
	{
	}

	public function run()
	{
	}
}
