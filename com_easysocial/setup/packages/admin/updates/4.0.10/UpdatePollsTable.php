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

ES::import('admin:/includes/maintenance/dependencies');

class SocialMaintenanceScriptUpdatePollsTable extends SocialMaintenanceScript
{
	public static $title = "Update Polls Table For Expiration Issue.";
	public static $description = 'Updating the future expiry date to end date for the existing polls';

	public function main()
	{
		$db = ES::db();

		$query = 'SELECT * FROM `#__social_polls` WHERE `expiry_date` > NOW()';

		$db->setQuery($query);
		$polls = $db->loadObjectList();

		if (empty($polls)) {
			return true;
		}

		foreach ($polls as $poll) {
			$table = ES::table('Polls');
			$table->bind($poll);

			// Convert back to the site's current time zone
			$expiry = ES::date($table->expiry_date)->toSql(true);

			$parts = explode(' ', $expiry);

			// To end the poll at the end of the day on the configured date.
			$expiry = $parts[0] . ' 23:59:59';

			$original_TZ = new DateTimeZone(ES::jconfig()->get('offset'));
			$expiryDate = JFactory::getDate($expiry, $original_TZ);

			// Store back its UTC updated expiry date
			$table->expiry_date = $expiryDate->toSql();
			$table->store();
		}

		return true;
	}
}