<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2014 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

ES::import('admin:/includes/maintenance/dependencies');

class SocialMaintenanceScriptFixMigratorDatetime extends SocialMaintenanceScript
{
	public static $title = 'Fix raw value on datetime field data from migrator';

	// public static $title = 'COM_EASYSOCIAL_MAINTENANCE_SCRIPT_FIXDATETIMEFIELDDATA_TITLE';

	public static $description = 'Fix raw value on datetime field data from migrator';

	// public static $description = 'COM_EASYSOCIAL_MAINTENANCE_SCRIPT_FIXDATETIMEFIELDDATA_DESCRIPTION';

	public function main()
	{
		// Only birthday field and datetime field is affected

		$birthdayTable = ES::table('app');
		$birthdayTable->load(array('type' => SOCIAL_APPS_TYPE_FIELDS, 'group' => SOCIAL_FIELDS_GROUP_USER, 'element' => 'birthday'));

		$datetimeTable = ES::table('app');
		$datetimeTable->load(array('type' => SOCIAL_APPS_TYPE_FIELDS, 'group' => SOCIAL_FIELDS_GROUP_USER, 'element' => 'datetime'));

		// $appid = array($birthdayTable->id, $datetimeTable->id);

		$db = ES::db();
		$sql = $db->sql();

		// $sql->select('#__social_fields_data')
		// 	->where('field_id', $appid, 'in')
		// 	->where('data', '', '!=');
		$query ="select a.* from `#__social_fields_data` as a";
		$query .= "	inner join `#__social_fields` as b on a.`field_id` = b.`id`";
		$query .= " where b.`app_id` IN ($birthdayTable->id, $datetimeTable->id)";
		$query .= " and a.`data` != ''";

		// echo $query;exit;
		$sql->raw($query);

		$db->setQuery($sql);

		$result = $db->loadObjectList();

		$json = ES::json();

		foreach ($result as $row)
		{
			if (empty($row->data))
			{
				continue;
			}

			$table = ES::table('fielddata');
			$table->bind($row);

			if ($json->isJsonString($table->data))
			{
				$val		= $json->decode($table->data);

				if ($val->year && $val->month && $val->day) {
					$dateVal 	= $val->year . '-' . $val->month . '-' . $val->day;

					$table->raw = ES::date($val->year . '-' . $val->month . '-' . $val->day)->toSql();
				}
			}
			else
			{
				try
				{
					$val = ES::date($table->data);
				}
				catch(Exception $e)
				{
					$table->data = '';
					$table->raw = '';

					$table->store();

					continue;
				}

				$table->data = $json->encode(array(
					'year' => $val->toFormat('Y'),
					'month' => $val->toFormat('n'),
					'day' => $val->toFormat('j')
				));

				$table->raw = $val->toSql();
			}

			$table->store();
		}

		return true;
	}
}
