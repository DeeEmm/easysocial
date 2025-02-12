<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2018 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

class SocialRouterFieldsUserFile extends SocialRouterFieldsAdapter
{
	public $name;

	public $group;
	public $element;

	public function build( &$menu, &$query )
	{
		// A field can decide if the built url should be a typical url or alternative url
		// Typical url: <group>/<element>/<task>/<fieldid>/<uniqueid>
		// Alternative url: <task>/<fieldid>/<uniqueid>

		$segments = array();

		$segments[] = $this->group;
		$segments[] = $this->element;

		if( !empty( $query['task'] ) )
		{
			$segments[] = $this->subtranslate( $query['task'] );
			unset( $query['task'] );
		}

		if( !empty( $query['id'] ) )
		{
			$segments[] = $query['id'];
			unset( $query['id'] );
		}

		if( !empty( $query['uid'] ) )
		{
			$segments[] = $query['uid'];
			unset( $query['uid'] );
		}

		return $segments;
	}

	public function parse( &$segments )
	{
		// Segments that is pass into field router in in the form of typical url
		// Typical url: <group>/<element>/<task>/*
		// But <group> and <element> will not exist in the segments
		// Passed in segments always start from task
		// <task>/*
		// This is because <group> and <element> (and <task>) is automatically parsed on parent class
		// Although <task> is automatically parsed on parent class, <task> is still passed into child router
		// This is so that child router can modify or check the <task> against language string if necessary
		// Child router only need to populate remaining segments into vars

		// For file field case, we are expecting:
		// <task>/<fieldid>/<uniqueid>

		$total = count( $segments );

		// We need at least 3 segments here
		if( $total < 3 )
		{
			return false;
		}

		$vars = array();

		if( $segments[0] === $this->subtranslate( 'download' ) )
		{
			$vars['task'] = 'download';
			$vars['id'] = $segments[1];
			$vars['uid'] = $segments[2];

			return $vars;
		}

		if( $segments[0] === $this->subtranslate( 'preview' ) )
		{
			$vars['task'] = 'preview';
			$vars['id'] = $segments[1];
			$vars['uid'] = $segments[2];

			return $vars;
		}

		// If no other matches, this means the url is invalid and ultimately return false
		return false;
	}
}
