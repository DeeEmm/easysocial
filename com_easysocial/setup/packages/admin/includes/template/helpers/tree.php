<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2014 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined( '_JEXEC' ) or die( 'Unauthorized Access' );

class ThemesHelperTree
{
	/**
	 * Renders the user group tree listing.
	 *
	 * @since	1.0
	 * @access	public
	 * @param	object	The object to check against.
	 * @param	string	The controller to be called.
	 * @param	string	The key for the object.
	 *
	 * @author	Mark Lee <mark@stackideas.com>
	 */
	public static function groups( $name = 'gid' , $selected = '' , $exclude = array() , $checkSuperAdmin = false )
	{
		static $count;

		$count++;

		// If selected value is a string, we assume that it's a json object.
		if( is_string( $selected ) )
		{
			$json 		= ES::json();
			$selected 	= $json->decode( $selected );
		}

		$version 	= ES::getInstance( 'Version' )->getVersion();

		if( $version >= '1.6' )
		{
			$groups 	= self::getGroups();

			$theme 		= ES::themes();

			$selected 		= ES::makeArray( $selected );
			$isSuperAdmin	= JFactory::getUser()->authorise('core.admin');

			$theme->set( 'name'				, $name );
			$theme->set( 'checkSuperAdmin' , $checkSuperAdmin );
			$theme->set( 'isSuperAdmin' , $isSuperAdmin );
			$theme->set( 'selected'	, $selected );
			$theme->set( 'count'	, $count );
			$theme->set( 'groups'	, $groups );

			return $theme->output( 'admin/html/tree.groups' );
		}

		return JHTML::_('select.genericlist', JFactory::getAcl()->get_group_children_tree( null, 'USERS', false ), 'gid', 'size="10"', 'value', 'text', $selected );
	}

	private static function getGroups()
	{
		$db 		= ES::db();
		$sql 		= $db->sql();

		$sql->select( '#__usergroups', 'a' );
		$sql->column( 'a.*' );
		$sql->column( 'b.id', 'level', 'count distinct' );
		$sql->join( '#__usergroups' , 'b' );
		$sql->on( 'a.lft', 'b.lft', '>' );
		$sql->on( 'a.rgt', 'b.rgt', '<' );
		$sql->group( 'a.id' , 'a.title' , 'a.lft' , 'a.rgt' , 'a.parent_id' );
		$sql->order( 'a.lft' , 'ASC' );

		$db->setQuery( $sql );
		$groups 	= $db->loadObjectList();

		return $groups;
	}

	/**
	 * Returns a UL list of user groups with check boxes
	 *
	 * @param   string   $name             The name of the checkbox controls array
	 * @param   array    $selected         An array of the checked boxes
	 * @param   boolean  $checkSuperAdmin  If false only super admins can add to super admin groups
	 *
	 * @return  string
	 *
	 * @since   11.1
	 */
	public static function groups16($name, $selected, $checkSuperAdmin = false , $exclude = array() )
	{
		static $count;

		$count++;

		$isSuperAdmin = JFactory::getUser()->authorise('core.admin');

		$db 	= ES::db();
		$sql	= $db->sql();

		$sql->select( '#__usergroups', 'a' );
		$sql->column( 'a.*' );
		$sql->column( 'b.id', 'level', 'count distinct' );
		$sql->leftjoin( '#__usergroups', 'b' );
		$sql->on( 'a.lft', 'b.lft', '>' );
		$sql->on( 'a.rgt', 'b.rgt', '<' );

		if( !empty( $exclude ) )
		{
			$sql->where( 'a.id', $exclude, 'not in' );
		}

		$sql->group( 'a.id', 'a.title', 'a.lft', 'a.rgt', 'a.parent_id' );
		$sql->order( 'a.lft', 'asc' );

		$db->setQuery( $sql );
		$groups 	= $db->loadObjectList();

		$html = array();

		for ($i = 0, $n = count($groups); $i < $n; $i++)
		{
			$item = &$groups[$i];

			// If checkSuperAdmin is true, only add item if the user is superadmin or the group is not super admin
			if ((!$checkSuperAdmin) || $isSuperAdmin || (!JAccess::checkGroup($item->id, 'core.admin')))
			{
				// Setup  the variable attributes.
				$eid = $count . 'group_' . $item->id;

				// Don't call in_array unless something is selected
				$checked = '';
				if ($selected)
				{
					$checked = in_array($item->id, $selected) ? ' checked="checked"' : '';
				}
				$rel = ($item->parent_id > 0) ? ' rel="' . $count . 'group_' . $item->parent_id . '"' : '';

				// Build the HTML for the item.
				$html[] = '	<div class="control-group">';
				$html[] = '		<div class="controls">';
				$html[] = '			<label class="checkbox" for="' . $eid . '">';
				$html[] = '			<input type="checkbox" name="' . $name . '[]" value="' . $item->id . '" id="' . $eid . '"';
				$html[] = '					' . $checked . $rel . ' />';
				$html[] = '			' . str_repeat('<span class="gi">|&mdash;</span>', $item->level) . $item->title;
				$html[] = '			</label>';
				$html[] = '		</div>';
				$html[] = '	</div>';
			}
		}

		return implode("\n", $html);
	}
}
