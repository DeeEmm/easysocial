<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2015 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

// Dependencies
ES::import('admin:/tables/table');

class SocialTableFieldOptions extends SocialTable
{
	public $id = null;
	public $parent_id = null;
	public $key = null;
	public $ordering = null;
	public $title = null;
	public $value = null;
	public $default = null;

	public function __construct(& $db )
	{
		parent::__construct( '#__social_fields_options' , 'id' , $db );
	}

	public function store($updateNulls = false)
	{
		if (empty($this->value) && !empty($this->title)) {
			$this->value = ESJString::strtolower(ESJString::str_ireplace(' ', '', $this->title));
		}

		if (empty($this->title) && !empty($this->value)) {
			$this->title = ESJString::ucfirst($this->value);
		}

		return parent::store($updateNulls);
	}
}
