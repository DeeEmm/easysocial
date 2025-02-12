<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2019 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

class SocialFieldsUserJoomlaFullnameHelper
{
	/**
	 * Determines if the name is allowed
	 *
	 * @since	3.2.24
	 * @access	public
	 */
	public static function allowed($name, &$params, $current = '')
	{
		// Exception for current
		if (!empty($current) && $name === $current) {
			return true;
		}

		$disallowed = trim($params->get('disallowed', ''));

		// If nothing is defined as allowed
		if (empty($disallowed)) {
			return true;
		}

		$disallowed = ESJString::strtoupper($disallowed);
		$disallowed = ES::makeArray($disallowed, ',');

		if (empty($disallowed)) {
			return true;
		}

		$disallowedType = $params->get('disallowed_type');

		// Standardize case sensitivity
		$name = ESJString::strtoupper($name);

		if ($disallowedType == 'equal') {
			if (!in_array($name, $disallowed)) {
				return true;
			}
		} else {
			$match = false;

			foreach ($disallowed as $string) {
				if (strpos($name, $string) !== false) {
					$match = true;
					break;
				}
			}

			if (!$match) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Get the specific name of the field based on the key
	 *
	 * @since	3.2.24
	 * @access	public
	 */
	public static function getKeyNameValue($fieldId ,$uid ,$key)
	{
		$model = ES::model('Fields');
		$result = $model->getCustomFieldsValue($fieldId ,$uid, SOCIAL_APPS_GROUP_USER, $key);

		return $result[$key];
	}
}
