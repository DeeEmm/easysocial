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

ES::import('fields:/user/datetime/datetime');

class BirthdayFieldWidgetsProfile extends EasySocial
{
	/**
	 * Renders the custom field in profileIntro position
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function profileIntro($key, SocialUser $user, $field)
	{
		// Get the value of the field
		$value = $field->data;

		// If user didn't set their gender, don't need to do anything
		if (!$value) {
			return;
		}

		// Get the params of the custom fields
		$params = $field->getParams();

		$onlyAllowYear = false;
		$allowYear = true;

		if ($params->get('year_privacy')) {
			$allowYear = $this->allowedPrivacy($user, 'birthday.year', $field);
		}

		if (!$this->allowedPrivacy($user, SOCIAL_TYPE_FIELD, $field)) {

			if ($allowYear && $params->get('year_privacy')) {
				$onlyAllowYear = true;
			} else {
				return;
			}
		}

		// Privacy for birthday field is different
		$privacy = $this->my->getPrivacy();


		$allowViewYear = $privacy->validate('field.birthday.year', $field->id, 'birthday.year', $user->id);

		// Empty value. just return empty string.
		if (is_array($value) && isset($value['date']) && !$value['date']) {
			return;
		}

		// We do not want to set a timezone on birthday field
		if (is_array($value) && isset($value['timezone'])) {
			unset($value['timezone']);
		}

		$data = new SocialFieldsUserDateTimeObject($value);
		$date = null;

		if (!empty($data->year) && !empty($data->month) && !empty($data->day)) {
			$date = $data->year . '-' . $data->month . '-' . $data->day;
		}

		if (!$date) {
			return;
		}

		// Display year by default
		$displayYear = true;

		$format = $allowViewYear ? 'd M Y' : 'd M';

		$age = $allowYear && $params->get('show_age') ? $this->getAge($date) : '';

		switch($params->get('date_format')) {
			case 2:
			case '2':
				$format = $allowViewYear ? 'M d Y' : 'M d';
				break;
			case 3:
			case '3':
				$format = $allowViewYear ? 'Y d M' : 'd M';
				break;
			case 4:
			case '4':
				$format = $allowViewYear ? 'Y M d' : 'M d';
				break;
		}

		if ($onlyAllowYear) {
			$format = 'Y';
		}

		$birthDate = ES::date($date, false)->format($format);

		$search = false;

		if ($field->isSearchable() && $allowYear && !$onlyAllowYear) {
			$date = $data->format('Y-m-d');

			$options = array();
			$options['layout'] = 'advanced';
			$options['criterias[]'] = $field->unique_key . '|' . $field->element;
			$options['operators[]'] = 'between';
			$options['conditions[]'] = $date . ' 00:00:00' . '|' . $date . ' 23:59:59';

			$search = ESR::search($options);
		}

		$theme = ES::themes();
		$theme->set('age', $age);
		$theme->set('params', $params);
		$theme->set('search', $search);
		$theme->set('birthDate', $birthDate);

		echo $theme->output('fields/user/birthday/widgets/display');
	}

	/**
	 * Renders the age of the user.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getAge($value)
	{
		$birthDate = new DateTime($value);

		$now = new DateTime();
		$years = date_diff($birthDate, $now)->y;

		return $years;
	}

	/**
	 * Shorthand function to check privacy of the viewing user against the privacy set in the field
	 *
	 * @since	3.1
	 * @access	public
	 */
	public function allowedPrivacy($user, $type = SOCIAL_TYPE_FIELD, $field = '')
	{
		$result = true;

		// For now we only validate privacy if the object is a SocialUser object
		// This is because group fields sometimes rides on user field, then when it comes to this part, it fails because we don't have privacy for group fields for now
		if ($user instanceof SocialUser) {
			$my = ES::user();
			$lib = ES::privacy($my->id);

			$element = 'field.' . $field->element;
			$elementType = $type;

			if ($type == 'birthday.year') {
				$element = 'field.' . $type;
				$elementType = $type;
			}

			$result = $lib->validate($element, $field->id, $elementType, $user->id);
		}

		return $result;
	}

}
