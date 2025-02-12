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

/**
 * Profile view for Notes app.
 *
 * @since	1.0
 * @access	public
 */
class Joomla_lastloginFieldWidgetsProfile
{
	/**
	 * Displays the gender in the position profileHeaderA
	 *
	 * @since	1.0
	 * @access	public
	 * @param	string
	 * @return
	 */
	public function profileHeaderA($key , $user , $field)
	{
		$dateString = '';

		$theme 	= ES::themes();


		if ($user->lastvisitDate == '' || $user->lastvisitDate == '0000-00-00 00:00:00') {
			$dateString = JText::_('PLG_FIELDS_JOOMLA_LASTLOGIN_WIDGETS_NEVER_LOGGED_IN');

		} else {

			$llDate = ES::date($user->lastvisitDate);

			// linkage to advanced search page.
			if ($field->type == SOCIAL_FIELDS_GROUP_USER && $field->searchable) {
				$date = $llDate->toFormat('Y-m-d');

				$params = array('layout' => 'advanced');
				$params['criterias[]'] = $field->unique_key . '|' . $field->element;
				$params['operators[]'] = 'between';
				$params['conditions[]'] = $date . ' 00:00:00' . '|' . $date . ' 23:59:59';

				$advsearchLink = FRoute::search($params);
				$theme->set('advancedsearchlink', $advsearchLink);
			}

			$dateString = $llDate->toLapsed();
			$dateString = JText::sprintf('PLG_FIELDS_JOOMLA_LASTLOGIN_WIDGETS_LAST_LOGGED_IN', $dateString);
		}

		$theme->set('value'	, $dateString);
		echo $theme->output('fields/user/joomla_lastlogin/widgets/display');
	}
}
