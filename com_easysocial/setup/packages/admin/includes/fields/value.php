<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) 2010 - 2014 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

/**
 * Field Value base class object for field to return as a general purpose value object.
 */
class SocialFieldValue
{
	/**
	 * The unique key of the field.
	 * @var string
	 */
	public $unique_key;

	/**
	 * The element of the field.
	 * @var string
	 */
	public $element;

	/**
	 * The field id.
	 * @var integer
	 */
	public $field_id;

	/**
	 * The owner id of this value.
	 * @var integer
	 */
	public $uid;

	/**
	 * The type of the owner of this value.
	 * @var string
	 */
	public $type;

	/**
	 * The formatted value of the field.
	 * @var mixed
	 */
	public $value;

	/**
	 * The data column retrieved from database.
	 * @var string
	 */
	public $raw;

	/**
	 * The data column retrieved from database that is processed by fields.
	 * @var mixed
	 */
	public $data;

	public function __construct($field = null)
	{
		if (!empty($field)) {
			$this->unique_key = $field->unique_key;
			$this->element = $field->element;
			$this->field_id = $field->id;

			$this->value = $field->data;
			$this->data = $field->data;
			$this->raw = $field->data;

			$this->uid = $field->uid;
			$this->type = $field->type;
		}
	}

	/**
	 * Returns the user object of this field value's owner.
	 *
	 * @since  1.2
	 * @access public
	 */
	final public function user()
	{
		return ES::user($this->userid);
	}

	/**
	 * Returns the field table of this field value.
	 *
	 * @since  1.2
	 * @access public
	 */
	final public function field()
	{
		$table = ES::table('field');
		$table->load($fieldid);

		return $table;
	}

	/**
	 * Magic method to use the class as value in string format properly. This method is only a proxy to call the toString function that expects field to override the behaviour.
	 *
	 * @since  1.2
	 * @access public
	 */
	final public function __toString()
	{
		return $this->toString();
	}

	/**
	 * Fields is expected to override this function behaviour to have a default class to string conversion.
	 *
	 * @since  1.2
	 * @access public
	 */
	public function toString()
	{
		return (string) $this->value;
	}

	/**
	 * Fields is expected to override this function behaviour to have a default class to string conversion.
	 *
	 * @since  1.2
	 * @access public
	 */
	public function toDisplay()
	{
		return (string) $this->value;
	}
}
