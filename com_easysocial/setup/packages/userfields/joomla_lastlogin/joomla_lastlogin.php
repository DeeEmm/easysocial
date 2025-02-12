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

ES::import('admin:/includes/fields/dependencies');

class SocialFieldsUserJoomla_lastlogin extends SocialFieldItem
{
	/**
	 * Responsible to output the html codes that is displayed to
	 * a user when they edit their profile.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function onEdit(&$post, &$user, $errors)
	{
		return;
	}

	/**
	 * Save trigger before user object is saved
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function onEditBeforeSave(&$post, &$user)
	{
		// Unset the lastlogin from the post data as we do not want to save this value.
		unset($post['lastlogin']);

		return true;
	}

	public function onAdminEditBeforeSave(&$post, &$user)
	{
		// Unset the lastlogin from the post data as we do not want to save this value.
		unset($post['lastlogin']);

		return true;
	}

	public function onDisplay($user)
	{
		if ($user->lastvisitDate == '' || $user->lastvisitDate == '0000-00-00 00:00:00') {
			$this->set('date', JText::_('PLG_FIELDS_JOOMLA_LASTLOGIN_WIDGETS_NEVER_LOGGED_IN'));
		}

		$llDate = ES::date($user->lastvisitDate);

		$format = 'd M Y';

		switch ($this->params->get('date_format')) {
			case 2:
			case '2':
				$format = 'M d Y';
				break;
			case 3:
			case '3':
				$format = 'Y d M';
				break;
			case 4:
			case '4':
				$format = 'Y M d';
				break;
		}

		$format .= ' H:i:s';

		// linkage to advanced search page.
		// place the code here so that the timezone wont kick in. we search the date using GMT value.
		$field = $this->field;
		if ($field->type == SOCIAL_FIELDS_GROUP_USER && $field->searchable) {
			$date = $llDate->toFormat('Y-m-d');

			$params = array('layout' => 'advanced');
			$params['criterias[]'] = $field->unique_key . '|' . $field->element;
			$params['operators[]'] = 'between';
			$params['conditions[]'] = $date . ' 00:00:00' . '|' . $date . ' 23:59:59';

			$advsearchLink = FRoute::search($params);
			$this->set('advancedsearchlink', $advsearchLink);
		}

		$this->set('date', $llDate->toFormat($format));

		return $this->display();
	}

	/**
	 * Returns formatted value for GDPR
	 *
	 * @since  2.2
	 * @access public
	 */
	public function onGDPRExport($user)
	{
		$lastVisitDate = $user->lastvisitDate;
		
		if (!$lastVisitDate || $lastVisitDate == '0000-00-00 00:00:00') {
			return JText::_('PLG_FIELDS_JOOMLA_LASTLOGIN_WIDGETS_NEVER_LOGGED_IN');
		}

		$date = ES::date($user->lastvisitDate);
		$field = $this->field;

		$formattedValue = $date->toFormat(JText::_('DATE_FORMAT_LC2'));

		$data = new stdClass;
		$data->fieldId = $field->id;
		$data->value = $formattedValue;

		return $data;
	}

	/**
	 * Trigger to get this field's value for various purposes.
	 *
	 * @since  1.2
	 * @access public
	 */
	public function onGetValue($user)
	{
		$container = $this->getValueContainer();

		$lastlogin = $user->lastvisitDate;

		$container->raw = $lastlogin;
		$container->data = $lastlogin;
		$container->value = $lastlogin;

		return $container;
	}
}
