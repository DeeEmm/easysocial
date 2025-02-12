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

class SocialTablePrivacy extends SocialTable
{
	public $id = null;
	public $core = null;
	public $state = null;
	public $type = null;
	public $rule = null;
	public $value = 0;
	public $description = null;
	public $options = 0;

	public function __construct(&$db)
	{
		parent::__construct('#__social_privacy', 'id', $db);
	}

	/**
	 * Retrieves the options for this privacy
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function getOptions()
	{
		$data = json_decode($this->options);
		$options = array();

		foreach ($data->options as $key) {
			$option = new stdClass();
			$option->value = ES::privacy()->toValue($key);
			$option->label = JText::_('COM_EASYSOCIAL_PRIVACY_OPTION_' . strtoupper($key));

			$options[] = $option;
		}

		return $options;
	}

	/**
	 * Converts the privacy options into a standard object
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function toJSON()
	{
		$options = json_decode($this->options);

		return array('id' => $this->id ,
					 'type' => $this->type,
					 'rule' => $this->rule,
					 'value' => $this->value,
					 'description' => $this->description,
					 'options' => $options,
					 'state' => $this->state,
					 'core' => $this->core
		);
	}
}
