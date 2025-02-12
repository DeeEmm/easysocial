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
class Joomla_joindateFieldWidgetsProfile
{
	/**
	 * Displays the gender in the position profileHeaderA
	 *
	 * @since	1.0
	 * @access	public
	 * @param	string
	 * @return
	 */
	public function profileHeaderA( $key , $user , $field )
	{
		$dateString = '';

		$theme 	= ES::themes();

		$regDate = ES::date($user->registerDate);

		// linkage to advanced search page.
		if ($field->type == SOCIAL_FIELDS_GROUP_USER && $field->searchable) {
			$date = $regDate->toFormat('Y-m-d');

			$params = array('layout' => 'advanced');
			$params['criterias[]'] = $field->unique_key . '|' . $field->element;
			$params['operators[]'] = 'between';
			$params['conditions[]'] = $date . ' 00:00:00' . '|' . $date . ' 23:59:59';

			$advsearchLink = FRoute::search($params);
			$theme->set('advancedsearchlink', $advsearchLink );
		}

		$fieldParams = $field->getParams();

		$format = 'd M Y';

		switch ($fieldParams->get('date_format')) {
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

		$dateString = $regDate->toFormat($format);
		$dateString = JText::sprintf('PLG_FIELDS_JOOMLA_JOINDATE_WIDGETS_MEMBER_SINCE', $dateString);

		$theme->set('value', $dateString );

		echo $theme->output('fields/user/joomla_joindate/widgets/display');
	}
}
