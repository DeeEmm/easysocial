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

class SocialTableEventMeta extends SocialTable
{
	public $id = null;
	public $cluster_id = null;
	public $start = '0000-00-00 00:00:00';
	public $end = '0000-00-00 00:00:00';
	public $start_gmt = '0000-00-00 00:00:00';
	public $end_gmt = '0000-00-00 00:00:00';
	public $timezone = '';
	public $all_day = null;
	public $group_id = 0;
	public $page_id = 0;
	public $reminder = 0;

	public function __construct(& $db)
	{
		parent::__construct('#__social_events_meta' , 'id' , $db);
	}

	/**
	 * Returns the SocialDate object of the event start datetime.
	 *
	 * @since   1.3
	 * @access  public
	 */
	public function getStart()
	{
		$timestamp = $this->start_gmt;

		if ($timestamp === ES_EMPTY_DATE) {
			$timestamp = $this->start;
		}

		$datetime = ES::date($timestamp, true);

		if (!empty($this->timezone)) {
			try {
				$datetime->setTimezone(new DateTimeZone($this->timezone));
			} catch(Exception $e) {}
		}

		return $datetime;
	}

	/**
	 * Returns the SocialDate object of the event end datetime.
	 *
	 * @since   1.3
	 * @access  public
	 */
	public function getEnd()
	{
		// If there's no end date, we assume that the end date is the same as the start date
		if (empty($this->end) || $this->end === '0000-00-00 00:00:00') {
			$datetime = ES::date($this->start_gmt, true);
		} else {

			$timestamp = $this->end_gmt;

			if ($timestamp === ES_EMPTY_DATE) {
				$timestamp = $this->end;
			}

			$datetime = ES::date($timestamp, true);
		}

		if (!empty($this->timezone)) {
			try {
				$datetime->setTimezone(new DateTimeZone($this->timezone));
			} catch(Exception $e) {}
		}

		return $datetime;
	}

	/**
	 * Returns the SocialDate object of the event start datetime.
	 *
	 * @since   1.3
	 * @access  public
	 */
	public function getStartGMT()
	{
		$datetime = ES::date($this->start_gmt, false);
		return $datetime;
	}

	/**
	 * Returns the SocialDate object of the event end datetime.
	 *
	 * @since   1.3
	 * @access  public
	 */
	public function getEndGMT()
	{
		// If there's no end date, we assume that the end date is the same as the start date
		if (empty($this->end_gmt) || $this->end_gmt === '0000-00-00 00:00:00') {
			$datetime = ES::date($this->start_gmt, false);
		} else {
			$datetime = ES::date($this->end_gmt, false);
		}

		return $datetime;
	}

	/**
	 * Check if this event has an end date.
	 *
	 * @since  1.3
	 * @access public
	 */
	public function hasEnd()
	{
		return !empty($this->end) && $this->end !== '0000-00-00 00:00:00';
	}

	/**
	 * Checks if this event is an all day event.
	 *
	 * @since  1.3.7
	 * @access public
	 */
	public function isAllDay()
	{
		return (bool) $this->all_day;
	}

	/**
	 * Checks if this event is a group event.
	 *
	 * @since  1.3.9
	 * @access public
	 */
	public function isGroupEvent()
	{
		return !empty($this->group_id);
	}

	/**
	 * Checks if this event is a page event.
	 *
	 * @since  2.0
	 * @access public
	 */
	public function isPageEvent()
	{
		return !empty($this->page_id);
	}

	/**
	 * Returns the SocialDate object of the event timezone.
	 *
	 * @since   1.4
	 * @access  public
	 */
	public function getTimezone()
	{
		if (!empty($this->timezone)) {
			return $this->timezone;
		}

		return false;
	}

	public function getReminder()
	{
		return $this->reminder;
	}
}
