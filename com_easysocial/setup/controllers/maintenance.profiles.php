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

require_once(__DIR__ . '/controller.php');

class EasySocialControllerMaintenanceProfiles extends EasySocialSetupController
{
	public $limit = 100;

	public function __construct()
	{
		parent::__construct();
		$this->engine();
	}

	/**
	 * Synchronize users with the default profile.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function syncProfiles()
	{
		// Fetch first $limit items to be processed.
		$db = ES::db();
		$sql = $db->sql();

		$query = array();
		$query[] = 'SELECT a.' . $db->nameQuote( 'id' ) . ', a.' . $db->nameQuote('name');
		$query[] = 'FROM ' . $db->nameQuote( '#__users' ) . ' AS a';
		$query[] = 'WHERE a.' . $db->nameQuote( 'id' ) . ' NOT IN( SELECT b.' . $db->nameQuote( 'user_id' ) . ' FROM ' . $db->nameQuote( '#__social_profiles_maps' ) . ' AS b )';
		$query[] = 'LIMIT 0,' . $this->limit;

		$db->setQuery( $query );
		$items = $db->loadObjectList();

		// Nothing to process here.
		if (!$items) {
			$result = new stdClass();
			$result->state = 1;

			$result = $this->getResultObj('Great! No orphaned users found. All users on the site is already assigned to a profile.', 1, 'success');
			$this->output( $result );
		}

		// Get the default profile id that we should use.
		$model = ES::model('Profiles');
		$profile = $model->getDefaultProfile();
		$fnField = $model->getProfileField($profile->getWorkflow()->id, 'JOOMLA_FULLNAME');

		// Get the total users that needs to be fixed.
		$totalItems = count($items);

		foreach ($items as $item) {
			$profileMap = ES::table('ProfileMap');
			$profileMap->profile_id = $profile->id;
			$profileMap->user_id = $item->id;
			$profileMap->state = SOCIAL_STATE_PUBLISHED;
			$profileMap->store();

			// lets atleast migrate the user name into profile field;
			// store the data in multirow format
			$names = explode(' ', $item->name);

			$fname = '';
			$lname = '';

			if (is_array($names)) {
				$fname = array_shift($names);
				// if there is still elements in array, lets implode it and set it as last name
				if ($names) {
					$lname = implode(' ', $names);
				}
			}

			$arrNames = array('first' => $fname,
							'middle' => '',
							'last' => $lname,
							'name' => $item->name
						);

			foreach ($arrNames as $key => $val) {

				$fData = ES::table( 'FieldData' );
				$fData->field_id = $fnField->id;
				$fData->uid = $item->id;
				$fData->type = 'user';
				$fData->data = $val;
				$fData->datakey = $key;
				$fData->raw = $val;
				$fData->store();
			}

		}

		// Detect if there are any more records.
		$query = array();
		$query[] = 'SELECT COUNT(1) FROM ' . $db->nameQuote( '#__users' ) . ' AS a';
		$query[] = 'WHERE a.' . $db->nameQuote( 'id' ) . ' NOT IN( SELECT b.' . $db->nameQuote( 'user_id' ) . ' FROM ' . $db->nameQuote( '#__social_profiles_maps' ) . ' AS b )';

		$db->setQuery($query);
		$total = $db->loadResult();

		$result = $this->getResultObj(JText::sprintf('%1s orphaned users found, synchronizing them with a default profile.', $totalItems), 2, 'success');

		return $this->output($result);
	}

	/**
	 * Retrieves the total number of users that does not have a profile type in EasySocial (orphan users)
	 * These users could be created through Joomla or any other extension
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function getTotal()
	{
		// Fetch first $limit items to be processed.
		$db = ES::db();

		$query = "select count(1) from `#__users` as a";
		$query .= " where not exists (select id from `#__social_profiles_maps` as b where b.`user_id` = a.`id`)";

		$db->setQuery($query);

		$total = $db->loadResult();

		if ($total > $this->limit) {
			$total = ceil($total / $this->limit);
		}

		return $this->output($total);

	}
}
