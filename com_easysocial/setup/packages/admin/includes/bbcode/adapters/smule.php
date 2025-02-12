<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) 2010 - 2017 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

class SocialBBCodeSmule
{
	/**
	 * Retrieves the embed widget for smule.com videos
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function getEmbedHTML($url)
	{
		$html = '<div class="es-video es-video--16by9"><iframe src="' . $url . '/frame" frameborder="0"></iframe></div>';

		return $html;
	}
}