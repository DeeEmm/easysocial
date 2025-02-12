<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2019 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

require_once(dirname(__FILE__) . '/library/Decoda.php');

class BBCodeDecodaAdapter
{
	/**
	 * Processes a string with decoda library.
	 *
	 * @since	2.2
	 * @access	public
	 */
	public function parse($message, $options = array(), &$streamTags = false, $debug = false)
	{
		$decoda = new Decoda();
		$decoda->addFilter(new DefaultFilter());
		$decoda->addFilter(new TextFilter());
		$decoda->addFilter(new ListFilter());

		if (isset($options['videos']) && $options['videos']) {
			$decoda->addFilter(new VideoFilter());
		}

		if (isset($options['links']) && $options['links']) {
			$decoda->addFilter(new UrlFilter());
			$decoda->addHook(new ClickableHook());
		}

		// Determines if we should load up emoticons.
		if (isset($options['emoticons']) && $options['emoticons']) {
			$decoda->addFilter(new ImageFilter());
			$decoda->addHook(new EmoticonHook());
		}

		if(isset($options['escape']) && $options['escape']) {
			$decoda->setEscaping($options['escape']);
		} else {
			$decoda->setEscaping(false);
		}

		if (isset($options['code']) && $options['code']) {
			$decoda->addFilter(new CodeFilter());
		}

		if (isset($options['restFormat']) && $options['restFormat']) {
			$decoda->restFormat = $options['restFormat'];
			$decoda->streamTags = $streamTags;
		}

		$decoda->reset($message);
		$message = $decoda->parse();

		if (isset($options['restFormat']) && $options['restFormat']) {
			$streamTags = $decoda->streamTags;
		}

		return $message;
	}

	public function parseRaw($string, $filters = array())
	{
		$decoda = new Decoda();

		foreach ($filters as $filter) {
			$filterClass = ucfirst($filter) . 'Filter';

			if (class_exists($filterClass)) {
				$filterObj = new $filterClass();
				$decoda->addFilter($filterObj);
			}
		}

		$decoda->reset($string);
		$string = $decoda->parse();

		return $string;
	}
}
