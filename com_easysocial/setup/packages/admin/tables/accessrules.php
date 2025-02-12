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

ES::import('admin:/tables/table');

class SocialTableAccessRules extends SocialTable
{
	public $id = null;
	public $name = null;
	public $title = null;
	public $description = null;
	public $extension = null;
	public $element = null;
	public $group = null;
	public $state = null;
	public $created = null;
	public $params = null;

	public function __construct(& $db)
	{
		parent::__construct('#__social_access_rules', 'id', $db);
	}

	public function load($keys = null, $reset = true)
	{
		$state = parent::load($keys, $reset);

		if (!$state) {
			return false;
		}

		$this->extractParams();

		return true;
	}

	public function bind($src, $ignore = array())
	{
		$state = parent::bind($src, $ignore);

		if (!$state) {
			return false;
		}

		$this->extractParams();

		return true;
	}

	public function extractParams()
	{
		$params = ES::makeObject($this->params);

		if (empty($params)) {
			return;
		}

		foreach ($params as $key => $value) {
			$this->$key = $value;
		}
	}
}
