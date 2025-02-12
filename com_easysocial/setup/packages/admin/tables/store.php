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

ES::import('admin:/tables/table');

class SocialTableStore extends SocialTable
{
	public $id = null;
	public $app_id = null;
	public $category = null;
	public $version = null;
	public $title = null;
	public $info = null;
	public $price = null;
	public $logo = null;
	public $element = null;
	public $group = null;
	public $type = null;
	public $permalink = null;
	public $created = null;
	public $updated = null;
	public $download = null;
	public $download_api = null;
	public $version_checking = null;
	public $raw = null;
	public $ratings = null;
	public $votes = null;
	public $payment = null;
	public $featured = false;
	public $stackideas = false;
	
	public function __construct(&$db)
	{
		parent::__construct('#__social_apps_store', 'id', $db);
	}
}
