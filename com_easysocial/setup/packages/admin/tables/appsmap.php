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

ES::import('admin:/tables/table');

class SocialTableAppsmap extends SocialTable
{
	public $id = null;
	public $uid = null;
	public $type = null;
	public $app_id = null;
	public $position = null;
	public $created = null;
	public $params = null;

	public function __construct(&$db)
	{
		parent::__construct('#__social_apps_map', 'id', $db);
	}
}
