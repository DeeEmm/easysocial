<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

class SocialPollsTemplate extends JObject
{
	public $element = null;
	public $uid = null;
	public $title = null;
	public $multiple = null;
	public $locked = null;
	public $created = null;
	public $created_by = null;
	public $expiry_date = null;
	public $items = null;
	public $cluster_id = null;
	public $privacy = null;

	public function __construct()
	{
		// Set the creation date to the current time.
		$date = ES::date();
		$this->created = $date->toMySQL();
		$this->title = '';
		$this->element = '';
		$this->uid = '0';
		$this->multiple = 0;
		$this->locked = 0;
		$this->created_by = '0';
		$this->expiry_date = '';
		$this->items = [];
		$this->cluster_id = null;
		$this->privacy = [];
	}

	/**
	 * Sets the title of the poll.
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function setTitle($title)
	{
		$this->title = $title;
	}

	/**
	 * Sets the cluster of the poll.
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function setCluster($clusterId, $type = null)
	{
		$this->cluster_id = $clusterId;

		if ($type) {
			$this->cluster_type = $type;
		}
	}

	/**
	 * Sets the creator of the poll.
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function setCreator($userid)
	{
		// Set actors id
		$this->created_by = $userid;
	}

	/**
	 * Sets the creator of the poll.
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function setPrivacy($privacy, $customPrivacy)
	{
		// Set actors id
		$this->privacy['privacy'] = $privacy;
		$this->privacy['customPrivacy'] = $customPrivacy;
	}

	/**
	 * Sets the context of this polls
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function setContext($uid, $element)
	{
		// Set the context id.
		$this->uid = $uid;

		// Set the context type.
		$this->element = $element;
	}


	/**
	 * Sets if this polls allow multiple options selectin or not.
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function setMultiple($multiple = 1)
	{
		// Set the context id.
		$this->multiple = $multiple;
	}


	/**
	 * Sets if this polls has the expiry date.
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function setExpiry($expiry)
	{
		if ($expiry) {
			$parts = explode(' ', $expiry);

			// to end the poll at the end of the day on the configured date.
			$expiry = $parts[0] . ' 23:59:59';
		}

		// We get the joomla timezone
		$original_TZ = new DateTimeZone(JFactory::getConfig()->get('offset'));

		// Get the date with timezone
		$expiryDate = JFactory::getDate($expiry, $original_TZ);

		$expiry = $expiryDate->toSql();

		// Set the context id.
		$this->expiry_date = $expiry;
	}


	/**
	 * add polls options
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function addOption($option)
	{
		if (is_array($option)) {
			$option = (object) $option;
		}

		$this->items[] = $option;
	}

}
