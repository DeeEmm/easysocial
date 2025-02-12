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

use Joomla\Registry\Registry;

class JFormRuleValidatetype extends JFormRule
{
	public function test(SimpleXMLElement $element, $value, $group = null, Registry $input = null, JForm $form = null)
	{
		$searchtype = $input['params']->searchtype;
		$field_name = (string) $element['name'];

		if ($field_name == 'profile_id' && $searchtype == 'user' && $value == '') {
			return false;
		}

		if ($field_name == 'group_category' && $searchtype == 'group' && $value == '') {
			return false;
		}

		if ($field_name == 'page_category' && $searchtype == 'page' && $value == '') {
			return false;
		}

		if ($field_name == 'event_category' && $searchtype == 'event' && $value == '') {
			return false;
		}

		return true;
	}
}
