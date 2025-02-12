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

class SocialScheduler extends EasySocial
{
	/**
	 * Creating scheduled item.
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function create($uid, SocialStreamTemplate $data)
	{
		// We'll only create an entry when this is a scheduled post.
		if (!$data->isScheduled()) {
			return;
		}

		$table = ES::table('StreamScheduled');
		$table->bind($data);
		$table->stream_id = $uid;

		$state = $table->store();

		return $state;
	}

	/**
	 * Updating scheduled item.
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function update($streamId, $scheduled)
	{
		$table = ES::table('StreamScheduled');
		$table->load(array('stream_id' => $streamId));

		// Update scheduled date.
		// Make sure the stored datetime is in UTC/GMT
		// $date = ES::date($scheduled, false);
		$userTZ = JFactory::getUser()->getParam('timezone');

		// Get default timezone
		if (is_null($userTZ)) {
			$userTZ = JFactory::getConfig()->get('offset');
		}

		$tz = new DateTimeZone($userTZ);
		$dateDate = JFactory::getDate($scheduled, $tz);

		$table->scheduled = $dateDate->toSql();

		// Update modified date.
		$date = ES::date();
		$table->modified = $date->toSql();

		$table->store();
	}
}
