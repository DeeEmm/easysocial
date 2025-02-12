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

class SocialMath
{
	static $instance = null;

	public static function getInstance()
	{
		if (is_null(self::$instance)) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Converts a float value into data with units.
	 *
	 *
	 * Example:
	 * <code>
	 * <?php
	 * $math = ES::math();
	 * echo $math->converUnits('1024');
	 * ?>
	 * </code>
	 *
	 * @since	1.0
	 * @access	public
	 * @param	float	Floating value.
	 * @return	string	Value with unit.
	 */
	public function convertUnits($size, $fromUnit, $toUnit, $round = false, $append = false, $decimal = 0)
	{
		$fromUnit = strtoupper($fromUnit);
		$units = array('B' => 0, 'KB' => 1, 'MB' => 2, 'GB' => 3);

		// Make sure all sizes are in bytes first.
		if ($units[$toUnit] > $units[$fromUnit]) {
			$division = pow(1024, $units[$toUnit] - $units[$fromUnit]);
			$base = $size / $division;
		} else {
			$base = $size * pow(1024, $units[$fromUnit] - $units[$toUnit]);
		}

		if ($round) {
			$base = round($base, $decimal);
		}

		$base .= $append ? ' ' . $toUnit : '';
		return $base;

	}

	public function convertBytes($val)
	{
		if (empty($val)) return 0;

		$val = trim($val);

		preg_match('#([0-9.]+)[\s]*([a-z]+)#i', $val, $matches);

		$last = '';

		if (isset($matches[2])) {
			$last = $matches[2];
		}

		if (isset($matches[1])) {
			$val = (float) $matches[1];
		}

		switch (strtolower($last))
		{
			case 'g':
			case 'gb':
				$val *= 1024;
			case 'm':
			case 'mb':
				$val *= 1024;
			case 'k':
			case 'kb':
				$val *= 1024;
		}

		return number_format($val, 0, '', '');
	}
}
