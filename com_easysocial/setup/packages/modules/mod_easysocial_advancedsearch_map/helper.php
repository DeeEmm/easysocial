<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2020 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

class EasySocialModAdvancedSearchMapHelper
{

	/**
	 * Get the user profile map items
	 *
	 * @since   4.0
	 * @access  public
	 */
	public static function getItems($addresskey = 'ADDRESS', $count = 50, $moduleLib = false)
	{
		$data = [];

		$session = JFactory::getSession();
		$searchConfig = $session->get('advancedsearch.configs', null, SOCIAL_SESSION_NAMESPACE);

		if ($searchConfig) {

			// debug
			// $items = self::simulateData();

			// for now we only support user profiles
			$lib = ES::advancedsearch(SOCIAL_TYPE_USER);

			// override the limits count
			$searchConfig['nextlimit'] = 0;
			$searchConfig['limit'] = $count;

			$items = $lib->search($searchConfig);

			if ($items) {
				foreach ($items as $user) {
					$item = new stdClass();
					$item->name = $moduleLib->html('html.user', $user);
					$item->permalink = $user->getPermalink();
					$item->avatar = $moduleLib->html('avatar.user', $user, 'default', true, true, '', false);

					$item->lat = '';
					$item->lng = '';
					$item->address = '';

					$address = $user->getFieldData($addresskey);

					if ($address) {
						$item->lat = $address['latitude'];
						$item->lng = $address['longitude'];
						$item->address = $address['address'];
					}

					$data[] = $item;
				}
			}
		}

		return $data;
	}


	private static function simulateData()
	{
		$db = ES::db();

		$ids = [];
		$query = 'select `id` from `#__users` where `block` = 0 LIMIT 10';

		$db->setQuery($query);

		$ids = $db->loadColumn();

		// debug
		// $ids = ['778', '786', '807', '787', '805'];

		ES::user($ids);

		$users = [];

		foreach ($ids as $id) {
			$user = ES::user($id);
			$users[] = $user;
		}

		return $users;
	}





	/**
	 * Get the title and value for the field options
	 *
	 * @since   3.0
	 * @access  public
	 */
	public static function getOptions($fieldId)
	{
		$db = ES::db();
		$sql = $db->sql();

		$sql->select('#__social_fields_options');
		$sql->where('parent_id', $fieldId);
		$sql->order('key');

		$db->setQuery($sql);

		return $db->loadObjectList();
	}

	/**
	 * Get the title and value for the field options
	 *
	 * @since   3.0
	 * @access  public
	 */
	public static function normalizeOperator($element, $filterMode)
	{
		if ($element == 'checkbox') {
			return $filterMode == 'equal' ? 'contain' : 'notcontain';
		}

		return $filterMode;
	}
}
