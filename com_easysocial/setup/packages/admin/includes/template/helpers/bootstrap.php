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

class ThemesHelperBootstrap
{
	/**
	 * Renders publish / unpublish icon.
	 *
	 * @since	1.0
	 * @access	public
	 * @param	object	The object to check against.
	 * @param	string	The controller to be called.
	 * @param	string	The key for the object.
	 *
	 * @author	Mark Lee <mark@stackideas.com>
	 */
	public static function popover($title = '', $content = '', $placement = '' , $placeholder = '' , $html = false )
	{
		$theme = ES::themes();

		if (!$content) {
			$content = $title . '_TOOLTIP';
		}

		if (!$placeholder) {
			$placeholder = $title .'_PLACEHOLDER';
		}

		$title = JText::_($title);
		$content = JText::_($content);
		$placeholder = JText::_($placeholder);
		
		$theme->set('title', $title);
		$theme->set('content', $content);
		$theme->set('placement', $placement);
		$theme->set('placeholder', $placeholder);
		$theme->set('html', $html);

		return $theme->output( 'admin/html/bootstrap.popover' );
	}

	public static function state($type=null, $keyword=null)
	{
		switch ($keyword) {

			case 'primary':
			case 'active':
				$state = 'primary';
				break;

			case 'success':
				$state = 'success';
				break;

			case 'warning':
			case 'warn':
				$state = 'warning';
				break;

			case 'info':
				$state = 'info';
				break;

			case 'danger':
			case 'error':
			case 'failed':
			case 'fail':
				$state = 'danger';
				break;

			case 'pending':
			case 'default':
				$state = 'default';
				break;

			default:
				$state = '';
				break;
		}

		switch ($type) {

			case 'label':
				$classname = 'label-' . $state;
				break;

			case 'alert':
				$classname = 'alert-' . $state;
				if ($state=='primary' || $state=='default') $classname = '';
				break;

			case 'table':
				$classname = $state;
				if ($state=='default') $classname = '';
				if ($state=='primary') $classname = 'active';
				break;

			default:
				$classname = $state;
				break;
		}

		return $classname;
	}
}
