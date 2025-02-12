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

class SocialTableCalendar extends SocialTable
{
	public $id = null;
	public $uid = 0;
	public $type = '';
	public $title = null;
	public $description = null;
	public $reminder = 0;
	public $date_start = null;
	public $date_end = null;
	public $user_id	= null;
	public $all_day	= 0;

	/**
	 * Class Constructor.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function __construct(& $db)
	{
		parent::__construct('#__social_apps_calendar', 'id', $db);
	}

	public function getStartDate()
	{
		static $dates = array();

		if (!isset($dates[$this->id])) {
			$dates[$this->id] = ES::date($this->date_start, false);
		}

		return $dates[$this->id];
	}

	public function getEndDate()
	{
		static $dates = array();

		if (!isset($dates[$this->id])) {
			$dates[$this->id] = ES::date($this->date_end, false);
		}

		return $dates[$this->id];
	}

	/**
	 * Publishes into the stream
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function createStream($verb = 'create')
	{
		// Add activity logging when a new schedule is created
		// Activity logging.
		$stream	= ES::stream();
		$streamTemplate	= $stream->getTemplate();

		// Set the actor.
		$streamTemplate->setActor($this->user_id, SOCIAL_TYPE_USER);

		// Set the context.
		$streamTemplate->setContext($this->id, 'calendar');

		// Set the verb.
		$streamTemplate->setVerb($verb);

		$streamTemplate->setPublicStream('core.view');

		// Create the stream data.
		$stream->add($streamTemplate);
	}

	public function getApp()
	{
		static $app;

		if (empty($app)) {
			$app = ES::table('app');
			$options = array('type' => SOCIAL_TYPE_APPS, 'group' => SOCIAL_APPS_GROUP_USER, 'element' => 'calendar');

			$app->load($options);
		}

		return $app;
	}

	public function getPermalink($external = false, $xhtml = true, $sef = true, $adminSef = false)
	{
		$userAlias = ES::user($this->user_id)->getAlias();
		$options = array('uid' => $userAlias, 'type' => SOCIAL_TYPE_USER, 'customView' => 'item', 'schedule_id' => $this->id, 'external' => $external, 'sef' => $sef, 'adminSef' => $adminSef);

		$permalink = $this->getApp()->getCanvasUrl($options, $xhtml);

		return $permalink;
	}

	/**
	 * Deletes an item
	 *
	 * @since	3.0
	 * @access	public
	 */
	public function delete($pk = null)
	{
		$db = ES::db();

		// delete comments and reactions.
		$query = "delete b, c";
		$query .= " from `#__social_apps_calendar` as a";
		// remove all associated comments;
		$query .= " left join `#__social_comments` as b on a.`id` = b.`uid` and b.`element` LIKE " . $db->Quote('calendar.%');
		// remove all associated reaction;
		$query .= " left join `#__social_likes` as c on a.`id` = c.`uid` and c.`type` LIKE " . $db->Quote('calendar.%');
		$query .= " where a.`id` = " . $db->Quote($this->id);

		$db->setQuery($query);
		$db->query();

		// now we delete any 'left overs' reactions on those deleted comments
		$query = "delete a";
		$query .= " from `#__social_likes` as a";
		$query .= "	left join `#__social_comments` as b on a.`uid` = b.`id`";
		$query .= " where a.`type` like " . $db->Quote('comments.%');
		$query .= " and b.`id` is null";

		$db->setQuery($query);
		$db->query();

		$state = parent::delete();
		return $state;
	}
}
