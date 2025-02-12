<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2015 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

// Dependencies
ES::import('admin:/includes/fields/dependencies');

class SocialFieldsUserAutocomplete extends SocialFieldItem
{
	/**
	 * Given a keyword, try to suggest the caller with respective value
	 *
	 * @since	1.4
	 * @access	public
	 * @param	string
	 * @return	
	 */
	public function suggest()
	{
		$search = $this->input->get('search', '', 'default');

		// Get available options for this field
		$result = $this->match($search);

		// Format the result
		$result = $this->format($result);
		
		return $this->ajax->resolve($result);
	}

	/**
	 * Formats the result and return data that is necessary only
	 *
	 * @since	1.4
	 * @access	public
	 * @param	string
	 * @return	
	 */
	public function format($result)
	{
		$items = array();

		if (!$result) {
			return $items;
		}

		foreach ($result as $row) {
			$item = new stdClass();
			$item->id = $row->id;
			$item->title = $row->title;
			$item->value = $row->value;

			$items[] = $item;
		}

		return $items;
	}

	/**
	 * Try to match for options given a keyword
	 *
	 * @since	1.4
	 * @access	public
	 * @param	string
	 * @return	
	 */
	private function match($keyword)
	{
		$db = ES::db();
		$query = $db->sql();

		$query->select('#__social_fields_options');
		$query->where('title', '%' . $keyword . '%', 'LIKE');
		$query->where('parent_id', $this->field->id);

		$db->setQuery($query);
		$result = $db->loadObjectList();

		return $result;
	}
}
