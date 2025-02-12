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

class EasySocialViewHashtags extends EasySocialSiteView
{
	/**
	 * Returns a result of suggested hash tag
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function suggest($result = array())
	{
		$output = array();

		// Load through the result list.
		foreach ($result as $tag) {
			$output[] = $tag->title;
		}

		return $this->ajax->resolve($output);
	}
}
