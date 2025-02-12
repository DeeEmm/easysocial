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

class SocialFieldsUserJoomla_joindate extends SocialFieldItem
{
	/**
	 * Responsible to output the html codes that is displayed to
	 * a user when they edit their profile.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function onEdit( &$post, &$user, $errors )
	{
		return;
	}

	/**
	 * Save trigger before user object is saved
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function onEditBeforeSave( &$post, &$user )
	{
		// Unset the lastlogin from the post data as we do not want to save this value.
		unset( $post['joindate'] );

		return true;
	}

	public function onAdminEditBeforeSave(&$post, &$user)
	{
		// Unset the lastlogin from the post data as we do not want to save this value.
		unset( $post['joindate'] );

		return true;
	}

	public function onDisplay($user)
	{
		$regDate = ES::date($user->registerDate);

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

		// linkage to advanced search page.
		// place the code here so that the timezone wont kick in. we search the date using GMT value.
		$field = $this->field;
		if ($field->type == SOCIAL_FIELDS_GROUP_USER && $field->searchable) {
			$date = $regDate->toFormat('Y-m-d');

			$params = array( 'layout' => 'advanced' );
			$params['criterias[]'] = $field->unique_key . '|' . $field->element;
			$params['operators[]'] = 'between';
			$params['conditions[]'] = $date . ' 00:00:00' . '|' . $date . ' 23:59:59';

			$advsearchLink = FRoute::search($params);
			$this->set('advancedsearchlink', $advsearchLink );
		}

		$this->set('date', $regDate->toFormat($format));

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
		$registerDate = $user->registerDate;
		
		if (!$registerDate) {
			return '';
		}

		$joinDate = ES::date($user->registerDate);
		$field = $this->field;

		$formattedValue = $joinDate->toFormat(JText::_('DATE_FORMAT_LC2'));

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

		$lastlogin = $user->registerDate;

		$container->raw = $lastlogin;
		$container->data = $lastlogin;
		$container->value = $lastlogin;

		return $container;
	}
}
