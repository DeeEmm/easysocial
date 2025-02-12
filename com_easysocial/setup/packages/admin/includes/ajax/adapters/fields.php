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

class SocialAjaxAdapterFields extends SocialAjaxAdapterAbstract
{
	public function execute($namespace, $parts, $args, $method)
	{
		// This is the group
		$group = $parts[0];

		// We know the second segment is always the element.
		$element = $parts[1];

		// Construct parameters
		$options = array('group' => $group, 'element' => $element, 'field' => null, 'inputName' => SOCIAL_FIELDS_PREFIX . '0');

		// Detect if there is an id passed in.
		$id = $this->input->get('id', 0, 'int');

		// If there is an id, it should also create a copy of the field.
		if ($id) {

			$field = ES::table('Field');
			$field->load($id);

			$step = ES::table('FieldStep');
			$step->load($field->step_id);

			$options['params'] = ES::fields()->getFieldConfigValues($field);
			$options['uid'] = $step->uid;
			$options['workflow_id'] = $step->workflow_id;
			$options['field'] = $field;
			$options['inputName'] = SOCIAL_FIELDS_PREFIX . $field->id;
		}

		// Determine the class name
		$className = 'SocialFields' . ucfirst($group) . ucfirst($element);

		// Let's instantiate the new object now.
		$obj = new $className($options);

		// Call the ajax method
		return $obj->$method();
	}
}
