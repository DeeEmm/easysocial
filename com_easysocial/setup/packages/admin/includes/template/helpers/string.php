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

class ThemesHelperString extends ThemesHelperAbstract
{
	public function escape($string)
	{
		return ES::string()->escape($string);
	}

	/**
	 * Generates the "with xxx, yyy and zzz" html codes
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function with($users)
	{
		$count = count($users);

		$theme = ES::themes();
		$theme->set('users', $users);
		$theme->set('count', $count);
		$output = $theme->output('site/helpers/string/with');

		return $output;
	}

	/**
	 * Formats a given date string with a given date format
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function date( $timestamp , $format = '' , $withOffset = true )
	{
		// Get the current date object based on the timestamp provided.
		$date 	= ES::date( $timestamp , $withOffset );

		// If format is not provided, we should use DATE_FORMAT_LC2 by default.
		$format	= empty( $format ) ? 'DATE_FORMAT_LC2' : $format;

		// Get the proper format.
		$format	= JText::_( $format );

		$dateString 	= $date->toFormat( $format );

		return $date->toFormat( $format );
	}

	/**
	 * Pluralize the string if necessary.
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function pluralize( $languageKey , $count )
	{
		return ES::string()->computeNoun( $languageKey , $count );
	}

	/**
	 * Alternative to @truncater to truncate contents with HTML codes
	 *
	 * @since	1.3
	 * @access	public
	 */
	public function truncate($text, $max = 250, $ending = '', $exact = false, $showMore = true, $overrideReadmore = false, $stripTags = false)
	{
		if (!$text) {
			return $text;
		}

		if (!$ending) {
			$ending = JText::_('COM_EASYSOCIAL_ELLIPSES');
		}

		// If the plain text is shorter than the maximum length, return the whole text
		if ((ESJString::strlen(preg_replace('/<.*?>/', '', $text)) <= $max) || !$max) {
			return $text;
		}

		// Truncate the string natively without retaining the original format.
		if ($stripTags) {
			$truncate = trim(strip_tags($text));
			$truncate = ESJString::substr($truncate, 0, $max) . $ending;
		} else {
			$stringLib = ES::string();
			$truncate = $stringLib->truncateWithHtml($text, $max, $ending, $exact);
		}

		$theme = ES::themes();
		$theme->set('truncated', $truncate);
		$theme->set('original', $text);
		$theme->set('showMore', $showMore);
		$theme->set('overrideReadmore', $overrideReadmore);

		$output = $theme->output('site/helpers/string/truncate');

		return $output;
	}

	/**
	 * Truncates a string at a centrain length and add a more link
	 *
	 * @deprecated	2.0
	 * @access	public
	 */
	public function truncater($text, $max)
	{
		return $this->truncate($text, $max, '');
	}
}
