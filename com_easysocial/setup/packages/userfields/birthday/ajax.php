<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2017 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

// Include dependencies from our libraries
ES::import('admin:/includes/fields/dependencies');
ES::import('fields:/user/datetime/datetime');

class SocialFieldsUserBirthday extends SocialFieldsUserDateTime
{
	/**
	 * Birthday date validation
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function isValid()
	{
		// Render the ajax lib.
		$ajax = ES::ajax();

		$value = $this->input->get('value', '', 'string');
		$date = $this->getDatetimeValue($value);

		$yearFrom = $this->input->get('year_from', '', 'string');
		$yearTo = $this->input->get('year_to', '', 'string');

		$range = $this->getYearRange(false, $yearFrom, $yearTo);

		// Check for year range
		if ($range !== false && !empty($date->year) && ($date->year < $range->min || $date->year > $range->max)) {
			return $ajax->reject(JText::_('PLG_FIELDS_DATETIME_VALIDATION_YEAR_OUT_OF_RANGE'));
		}

		$ageLimit = $this->input->get('age_limit', 0, 'int');

		if ($ageLimit < 1) {
			return true;
		}

		// We don't throw validity error here, leave it up to the parent function to do it
		if (!$date->isValid()) {
			return $ajax->resolve();
		}

		$now = ES::date('now', false)->toUnix();
		$birthDate = $date->toDate()->toUnix();

		$age = floor(($now - $birthDate) / (31556926));

		if ($age < $ageLimit) {
			return $ajax->reject(JText::sprintf('PLG_FIELDS_BIRTHDAY_VALIDATION_AGE_LIMIT', $ageLimit));
		}

		return $ajax->resolve();
	}
}
