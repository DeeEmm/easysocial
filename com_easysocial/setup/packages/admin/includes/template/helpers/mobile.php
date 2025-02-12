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

class ThemesHelperMobile extends ThemesHelperAbstract
{
	/**
	 * Renders a filter action button
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function filterActions($actions, $attributes = array(), $icon = 'fa fa-plus')
	{
		if ($attributes) {
			$attributes = implode(' ', $attributes);
		}

		$theme = ES::themes();
		$theme->set('actions', $actions);
		$theme->set('attributes', $attributes);
		$theme->set('icon', $icon);

		$output = $theme->output('site/helpers/mobile/filter.actions');

		return $output;
	}

	/**
	 * Renders a filter action link
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function filterAction($title, $link, $attributes = array())
	{
		if ($attributes) {
			$attributes = implode(' ', $attributes);
		} else {
			// convert it into empty string.
			$attributes = '';
		}

		$theme = ES::themes();
		$theme->set('title', $title);
		$theme->set('link', $link);
		$theme->set('attributes', $attributes);

		$output = $theme->output('site/helpers/mobile/filter.action');

		return $output;
	}

	/**
	 * Renders a filter group for mobile devices
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function filterGroup($title, $target, $active = false, $icon = '', $dialog = false, $attributes = array(), $link = 'javascript:void(0);', $class = 'btn es-mobile-filter-slider__btn')
	{
		if ($attributes) {
			$attributes = implode(' ', $attributes);
		}

		$theme = ES::themes();
		$theme->set('icon', $icon);
		$theme->set('active', $active);
		$theme->set('title', $title);
		$theme->set('target', $target);
		$theme->set('dialog', $dialog);
		$theme->set('attributes', $attributes);
		$theme->set('link', $link);
		$theme->set('class', $class);

		$output = $theme->output('site/helpers/mobile/filter.group');

		return $output;
	}

	/**
	 * Renders a filter tab for mobile devices
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function filterTab($title, $link, $active = false, $attributes = array(), $linkAttributes = array())
	{
		if ($attributes) {
			$attributes = implode(' ', $attributes);
		}

		if ($linkAttributes) {
			$linkAttributes = implode(' ', $linkAttributes);
		}

		if (!$attributes) {
			$attributes = '';
		}

		if (!$linkAttributes) {
			$linkAttributes = '';
		}

		$theme = ES::themes();
		$theme->set('linkAttributes', $linkAttributes);
		$theme->set('active', $active);
		$theme->set('attributes', $attributes);
		$theme->set('title', $title);
		$theme->set('link', $link);

		$output = $theme->output('site/helpers/mobile/filter.tab');

		return $output;
	}
}
