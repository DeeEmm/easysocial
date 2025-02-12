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

class SocialRouterFields extends SocialRouterAdapter
{
	public $name;

	public function build( &$menu, &$query )
	{
		// Field router have a few requirements
		// 1. Element must be defined
		// 2. Group defaults to SOCIAL_FIELDS_GROUP_USER
		// 3. Field id depends on the field router

		// Typical build url
		// fields/group/element/task/*

		// Alternative build url
		// fields/task/fieldid/*

		if( empty( $query['element'] ) )
		{
			return array();
		}

		$element = $query['element'];
		unset( $query['element'] );

		$group = SOCIAL_FIELDS_GROUP_USER;

		if( !empty( $query['group'] ) )
		{
			$group = $query['group'];
			unset( $query['group'] );
		}

		$view = $this->translate( $query['view'] );
		unset( $query['view'] );

		// Get the app router
		$router = $this->getFieldRouter( $group, $element );

		// If unable to get the field router, then we return empty array
		if( $router === false )
		{
			return array();
		}

		// If the build method does not exist in the field router, then we return empty array
		if( !is_callable( array( $router, 'build' ) ) )
		{
			return array();
		}

		// We let the field decides the build url type
		// Since the view 'field' is will be prepended to the segments, fields only need to handle from the 2nd segment onwards
		// Typical build url: group/element/task/*
		// Alternative build url: task/fieldid/*
		$segments = $router->build( $menu, $query );

		// Always append view into the front of segments array
		array_unshift( $segments, $view );

		return $segments;
	}

	public function parse( &$segments )
	{
		// Typical build url
		// fields/group/element/task/*

		// Alternative build url
		// fields/task/fieldid/*

		$vars = array();
		$total = count( $segments );

		// First segment must always be 'fields'
		if( $total > 0 && $segments[0] !== $this->translate( $this->name ) )
		{
			return $vars;
		}

		// Total segment must be at least 3
		if( $total < 3 )
		{
			return $vars;
		}

		// Create an empty array for segments rebuild
		// Ultimately when we pass it to the field router to process, it should always be in the form of:
		// fields/group/element/[task/*]
		// This will only pass the [] part to the parse function of field router because we want to standardize the url to parse
		// Hence rebuild will only contain additional segments in the [] part
		// task will always be the 1st segment of rebuild
		// If url is in alternative mode, then field id will be the 2nd segment of rebuild
		// The field router decides on the count of the rebuild array to determine the identify of the structure
		$rebuild = array();

		// First segment is always fields
		$vars['view'] = 'fields';

		// Layout is always display
		$vars['layout'] = 'display';

		// Determine url type from 3rd segment
		$third = intval( $segments[2] );

		// If intval of 3rd segment is 0, then we assume is a string, with the url of fields/group/element/task/*
		// If it is not 0, then we assume the url is fields/task/fieldid/*
		if( $third === 0 )
		{
			$vars['group'] = $segments[1];
			$vars['element'] = $segments[2];

			// 4th segment is defaulted to 'display' by default if 4th segment doesn't exist
			$vars['task'] = 'display';
			if( !empty( $segments[3] ) )
			{
				$vars['task'] = $segments[3];
			}

			$rebuild[] = $vars['task'];

			// If there is 5 segments or above, then we throw the remaining segments into the rebuild array
			if( $total >= 5 )
			{
				// Start from 5th segment
				for( $i = 4; $i < $total; $i++ )
				{
					$rebuild[] = $segments[$i];
				}
			}
		}
		else
		{
			// 2nd segment is the task
			$vars['task'] = $segments[1];

			// We need to get back the group and element for the field based on the field id
			$field = ES::table( 'field' );
			$state = $field->load( $segments[2] );

			// If this is not a valid field then return empty array
			if( !$state )
			{
				return array();
			}

			$app = $field->getApp();

			// If this is not a valid app then return empty array
			if( !$app )
			{
				return array();
			}

			$vars['group'] = $app->group;
			$vars['element'] = $app->element;

			// Throw task into the rebuild array
			$rebuild[] = $segments[1];

			// Throw fieldid into the rebuild array
			$rebuild[] = $segments[2];

			// If there is 4 segments and above, then we throw the remaining segments into the rebuild array
			if( $total >= 4 )
			{
				// Start from the 4th segment
				for( $i = 3; $i < $total; $i ++ )
				{
					$rebuild[] = $segments[$i];
				}
			}
		}

		// Rebuild segments should now be in the form of task/*
		// We pass the rebuild segments to individual fields router to get back additional $vars to merge
		$router = $this->getFieldRouter( $vars['group'], $vars['element'] );

		// If unable to get the field router, then we return empty array
		if( $router === false )
		{
			return array();
		}

		// If the parse method does not exist in the field router, then we return empty array
		if( !is_callable( array( $router, 'parse' ) ) )
		{
			return array();
		}

		// We pass in the rebuild segments rather than the original segments
		$extraVars = $router->parse( $rebuild );

		// There is a possibility that child router returns false
		// In that case, this means that the url segments passed in is rendered as invalid on child router
		// We return empty array since it is not a valid url
		if( $extraVars === false )
		{
			return array();
		}

		// If the router returns some vars, we merge it into the $vars
		if( !empty( $extraVars ) )
		{
			$vars = array_merge( $vars, $extraVars );
		}

		return $vars;
	}

	private function getFieldRouter( $group, $element )
	{
		static $adapters = array();

		if( empty( $adapters[$group][$element] ) )
		{
			$file = SOCIAL_FIELDS . '/' . $group . '/' . $element . '/router.php';

			if( !JFile::exists( $file ) )
			{
				return false;
			}

			$classname = 'SocialRouterFields' . ucfirst( $group ) . ucfirst( $element );

			if( !class_exists( $classname ) )
			{
				require_once( $file );
			}

			require_once( $file );

			if( !class_exists( $classname ) )
			{
				return false;
			}

			$class = new $classname( $this->name );

			// Init a few properties
			$class->group = $group;
			$class->element = $element;

			$adapters[$group][$element] = $class;
		}

		return $adapters[$group][$element];
	}


}

abstract class SocialRouterFieldsAdapter extends SocialRouterFields
{
	// This is a function for child router to use
	// Rather than constructing a the translation string from FIELDS_GROUP_ELEMENT_TASK, child router only need to pass in TASK
	public function subtranslate( $task )
	{
		$prefix = $this->name . '_' . $this->group . '_' . $this->element;

		$string = $prefix . '_' . $task;

		return $this->translate( $string );
	}
}
