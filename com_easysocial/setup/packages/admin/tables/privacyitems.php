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

ES::import('admin:/tables/table');

class SocialTablePrivacyItems extends SocialTable
{
	/**
	 * The table row id
	 * @var int
	 */
	public $id = null;

	/**
	 * FK to social_privacy.id
	 * @var string
	 */
	public $privacy_id = null;

	/**
	 * The user id
	 * @var string
	 */
	public $user_id = null;

	/**
	 * The object id
	 * @var string
	 */
	public $uid = null;

	/**
	 * The privacy type
	 * @var string max 64chars
	 */
	public $type = null;

	/**
	 * The privacy value
	 * @var int
	 */
	public $value = 0;

	/**
	 * The field access
	 * @var int
	 */
	public $field_access = 0;

	public function __construct(& $db)
	{
		parent::__construct('#__social_privacy_items', 'id', $db);
	}

	public function toJSON()
	{
		return array('id' => $this->id,
					 'privacy_id' => $this->privacy_id,
					 'user_id' => $this->user_id,
					 'uid' => $this->uid,
					 'type' => $this->type,
					 'value' => $this->value,
					 'field_access' => $this->field_access
		);
	}

}
