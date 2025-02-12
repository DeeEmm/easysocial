<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2016 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

class SocialStylesheetAnalyzer
{

	public static function sections($css) {

		// TODO: Use preg_match
		$lines = explode("\n", $css);
		$sections = array();
		foreach ($lines as $line) {
			if (substr($line, 0, 7)==='@import') {
				$sections[] = substr($line, 9, -6);
			}
		}

		return $sections;
	}

	public static function rules($css) {

		$results = array();
		$pattern = '/.+?\{.+?\}|,/s';

		preg_match_all($pattern, $css, $matches);

		$rules = $matches[0];

		return $rules;
	}

	public static function selectors($css) {

		$rules = self::rules($css);
		$selectors = array();

		foreach ($rules as $rule) {

			$parts = self::parts($rule);
			$rule_selectors = explode(',', $parts[0]);

			foreach ($rule_selectors as $selector) {
				$selectors[] = $selector;
			}
		}

		return $selectors;
	}

	public static function parts($rule) {

		// Breaks a single css rule into 2 parts,
		// selectors and declarations.
		$pattern = '/.+?\{/s';
		preg_match($pattern, $rule, $parts);

		return $parts;
	}

	public static function split($css, $every=4096) {

		$rules = self::rules($css);

		$blocks = array();
		$i = 1;
		$count = 0;

		foreach ($rules as $rule) {

			$parts = self::parts($rule);
			$rule_selectors = explode(',', $parts[0]);

			$count += count($rule_selectors);

			if ($count > ($i * $every)) {
				$i += 1;
			}

			if (!isset($blocks[$i])) {
				$blocks[$i] = '';
			}

			$blocks[$i] .= $rule;
		}

		$blocks = array_values($blocks);

		return $blocks;
	}
}
